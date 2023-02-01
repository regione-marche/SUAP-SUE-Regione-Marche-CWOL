<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    proConservazioneManagerDigiP
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    16.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManager.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';

class proConservazioneManagerDigiP extends proConservazioneManager {

    const CLASSE_PARAMETRI = 'DIGIP_MARCHE';
    const Versione = '1.4';
    const VersioneDatiSpecifici = '1.0';

    /*
     * Keyes Base Dati/ Dizionario DigiP Elaborata 
     */
    const K_VERSIONE = 'VERSIONE';
    const K_LOGINNAME = 'LOGINNAME';
    const K_PASSWORD = 'PASSWORD';
    const K_URLSERVIZIO = 'URLSERVIZIO';
    const K_URLMODIFICA = 'URLMODIFICA';
    const K_AMBIENTE = 'AMBIENTE';
    const K_ENTE = 'ENTE';
    const K_STRUTTURA = 'STRUTTURA';
    const K_USERID = 'USERID';
    const K_TIPOCONSERVAZIONE = 'TIPOCONSERVAZIONE';
    const K_TIPOLOGIAUNITADOCUMENTARIA = 'TIPOLOGIAUNITADOCUMENTARIA'; //** Non usata oltre al test?
    const K_FORZAACCETTAZIONE = 'FORZAACCETTAZIONE'; //** Non usata oltre al test?
    const K_FORZACONSERVAZIONE = 'FORZACONSERVAZIONE'; //** Non usata oltre al test?

    /* Keys Documento Principale */
    const K_DOCPRINCIPALE = 'DOCPRINCIPALE';
    const K_IDDOCUMENTO = 'IDDOCUMENTO';
    const K_TIPODOCUMENTO = 'TIPODOCUMENTO';
    const K_VERSIONEDATISPECIFICI = 'VERSIONEDATISPECIFICI';
    const K_DATISPECIFICITIPODOCUMENTO = 'DATISPECIFICITIPODOCUMENTO';
    const K_IDUNIVOCO = 'IDUNIVOCO';
    const K_ORIGINE = 'ORIGINE';
    /* Struttura Generale Componenti */
    const K_TIPOSTRUTTURA = 'TIPOSTRUTTURA';

    /* Keys componente: Generiche */
    const K_IDCOMPONENTE = 'TIPOSTRUTTURA';
    const K_ORDINEPRESENTAZIONE = 'ORDINEPRESENTAZIONE';
    const K_TIPOCOMPONENTE = 'TIPOCOMPONENTE';
    const K_TIPOSUPPORTOCOMPONENTE = 'TIPOSUPPORTOCOMPonente';
    const K_NOMECOMPONENTE = 'NOMECOMPONENTE';
    const K_FORMATOFILEVERSATO = 'FORMATOFILEVERSATO';
    const K_FILEPATH = 'FILEPATH';

    /* Keys componente: Allegati */
    const K_ALLEGATI = 'ALLEGATI';
    const K_ID = 'ID';
    const K_HASHVERSATO = 'HASHVERSATO';
    const K_IDCOMPONENTEVERSATO = 'IDCOMPONENTEVERSATO';
    const K_UTILIZZODATAFIRMAPERRIFTEMP = 'UTILIZZODATAFIRMAPERRIFTEMP';
    /*
     * Key Documenti SUAP Dati Specifici
     */
    const K_DSPE = 'KEY_DSPESUAP';
    const K_DSPE_SEGNATURA = 'SEGNATURA';
    const K_DSPE_DATAREGISTRAZIONE = 'DATAREGISTRAZIONE';
    const K_DSPE_OGGETTO = 'OGGETTO';
    const K_DSPE_MITTENTE_NOME = 'MITTENTE_NOME';
    const K_DSPE_MITTENTE_COGNOME = 'MITTENTE_COGNOME';
    const K_DSPE_MITTENTE_DENOMINAZIONE = 'MITTENTE_DENOMINAZIONE';
    const K_DSPE_MITTENTE_CODICEFISCALE = 'MITTENTE_CODICEFISCALE';
    const K_DSPE_MITTENTE_PARTITAIVA = 'MITTENTE_PARTITAIVA';
    const K_DSPE_MITTENTE_STATO = 'MITTENTE_STATO';
    const K_DSPE_MITTENTE_REGIONE = 'MITTENTE_REGIONE';
    const K_DSPE_MITTENTE_COMUNE = 'MITTENTE_COMUNE';
    const K_DSPE_MITTENTE_CAP = 'MITTENTE_CAP';
    const K_DSPE_MITTENTE_INDIRIZZO = 'MITTENTE_INDIRIZZO';
    const K_DSPE_MITTENTE_EMAIL = 'MITTENTE_EMAIL';
    const K_DSPE_MITTENTE_NUMCIVICO = 'MITTENTE_NUMCIVICO';
    const K_DSPE_MITTENTE_DATARICEZIONE = 'MITTENTE_DATARICEZIONE';
    const K_DSPE_MITTENTE_MEZZORICEZIONE = 'MITTENTE_MEZZORICEZIONE';
    const K_DSPE_MITTENTE_SEGNATURAPROTOCOLLO = 'MITTENTE_SEGNATURAPROTOCOLLO';
    const K_DSPE_SOGGPROD_CODICEIPA = 'SOGGPROD_CODICEIPA';
    const K_DSPE_SOGGPROD_DENOMINAZIONE = 'SOGGPROD_DENOMINAZIONE';
    const K_DSPE_SOGGPROD_TIPOSOGGETTO = 'SOGGPROD_TIPOSOGGETTO';
    const K_DSPE_SOGGPROD_CONDIZIONEGIURIDICA = 'SOGGPROD_CONDIZIONEGIURIDICA';
    const K_DSPE_SOGGPROD_STATO = 'SOGGPROD_STATO';
    const K_DSPE_SOGGPROD_REGIONE = 'SOGGPROD_REGIONE';
    const K_DSPE_SOGGPROD_COMUNE = 'SOGGPROD_COMUNE';
    const K_DSPE_SOGGPROD_CAP = 'SOGGPROD_CAP';
    const K_DSPE_SOGGPROD_INDIRIZZO = 'SOGGPROD_INDIRIZZO';
    const K_DSPE_SOGGPROD_NUMCIV = 'SOGGPROD_NUMCIV';
    const K_DSPE_DESTINATARIO_NOME = 'DESTINATARIO_NOME';
    const K_DSPE_DESTINATARIO_COGNOME = 'DESTINATARIO_COGNOME';
    const K_DSPE_DESTINATARIO_DENOMINAZIONE = 'DESTINATARIO_DENOMINAZIONE';
    const K_DSPE_DESTINATARIO_CODICEFISCALE = 'DESTINATARIO_CODICEFISCALE';
    const K_DSPE_DESTINATARIO_PARTITAIVA = 'DESTINATARIO_PARTITAIVA';
    const K_DSPE_DESTINATARIO_STATO = 'DESTINATARIO_STATO';
    const K_DSPE_DESTINATARIO_REGIONE = 'DESTINATARIO_REGIONE';
    const K_DSPE_DESTINATARIO_COMUNE = 'DESTINATARIO_COMUNE';
    const K_DSPE_DESTINATARIO_CAP = 'DESTINATARIO_CAP';
    const K_DSPE_DESTINATARIO_INDIRIZZO = 'DESTINATARIO_INDIRIZZO';
    const K_DSPE_DESTINATARIO_EMAIL = 'DESTINATARIO_EMAIL';
    const K_DSPE_DESTINATARIO_NUMCIV = 'DESTINATARIO_NUMCIV';
    const K_DSPE_DESTINATARIO_RICEVUTEPEC_ACC = 'DESTINATARIO_RICEVUTEPEC_ACC';
    const K_DSPE_DESTINATARIO_RICEVUTEPEC_CONS = 'DESTINATARIO_RICEVUTEPEC_CONS';
    const K_DSPE_LUOGODOCUMENTO = 'LUOGODOCUMENTO';
    const K_DSPE_TIPODOCUMENTO_CODIDENTIFICATIVO = 'TIPODOCUMENTO_CODIDENTIFICATIVO';
    const K_DSPE_TIPODOCUMENTO_DENOMINAZIONE = 'TIPODOCUMENTO_DENOMINAZIONE';
    const K_DSPE_TIPODOCUMENTO_NATURA = 'TIPODOCUMENTO_NATURA';
    const K_DSPE_TIPODOCUMENTO_ACCESSIBILITA = 'TIPODOCUMENTO_ACCESSIBILITA';
    const K_DSPE_DATADOCUMENTO = 'DATADOCUMENTO';
    const K_DSPE_IDENTIFICATIVOPROCEDIMENTO = 'IDENTIFICATIVOPROCEDIMENTO';
    const K_DSPE_TEMPOMINIMOCONSERVAZIONE = 'TEMPOMINIMOCONSERVAZIONE';
    const K_DSPE_ANNOAPERTURA = 'ANNOAPERTURA';
    const K_DSPE_ANNOCHIUSURA = 'ANNOCHIUSURA';
    const K_DSPE_PROCAMM_CODICEPROCEDIMENTO = 'PROCAMM_CODICEPROCEDIMENTO';
    const K_DSPE_PROCAMM_DESCRIZIONE = 'PROCAMM_DESCRIZIONE';
    const K_DSPE_PROCAMM_RIFERIMENTINORMATIVI = 'PROCAMM_RIFERIMENTINORMATIVI';
    const K_DSPE_PROCAMM_DESTINATARI = 'PROCAMM_DESTINATARI';
    const K_DSPE_PROCAMM_REGIMIABILITATIVI = 'PROCAMM_REGIMIABILITATIVI';
    const K_DSPE_PROCAMM_TIPOLOGIA = 'PROCAMM_TIPOLOGIA';
    const K_DSPE_PROCAMM_SETTOREATTIVITA = 'PROCAMM_SETTOREATTIVITA';
    const K_DSPE_CODICEIPA = 'CODICEIPA';
    const K_DSPE_AMMINISTRAZIONIPARTECIPANTI = 'AMMINISTRAZIONIPARTECIPANTI';
    const K_DSPE_ACCESSIBILITA = 'ACCESSIBILITA';
    //
    const K_DSPE_PROTOCOLLO = 'PROTOCOLLO';
    const K_DSPE_TIPOPROTOCOLLO = 'TIPOPROTOCOLLO';
    const K_DSPE_DATAPROTOCOLLO = 'DATAPROTOCOLLO';
    const K_DSPE_ALTRIMITTDEST = 'ALTRIMITTDEST';
    //
    const K_DSPE_CHIAVE_PASSO = 'CHIAVE_PASSO';
    const K_DSPE_NUMEROPASSO = 'NUMEROPASSO';
    const K_DSPE_SERIEARCCODICE = 'SERIEARCCODICE';
    const K_DSPE_SERIEARCSIGLA = 'SERIEARCSIGLA';
    const K_DSPE_SERIEARCPROGRESSIVO = 'SERIEARCPROGRESSIVO';
    const K_DSPE_SERIEANNO = 'SERIEANNO';
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
         * Crea base dati generali e per DIGIP
         */
        if (!$this->getBaseDatiUnitaDocumentarie()) {
            return false;
        }


        //Out::msginfo('base', print_r($this->baseDati[self::K_DSPE], true));
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
                $this->creaXmlUnitaDocumentariaInterno();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                $this->creaXmlUnitaDocumentariaAggiornamento();
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                $this->creaXmlUnitaDocumentariaAnnullamento();
                break;

            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO:
                $this->creaXmlUnitaDocumentaria();
                break;
        }
        $this->logger->info('Chiamo la funzione di versamento. Unita Doc: ' . $this->unitaDocumentaria);

        //file_put_contents('C:/Works/xmlrich.xml', $this->xmlRichiesta);
//        return; //Di test per verifica xml
        /*
         * Versamento in conservazione
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO:
                if (!$this->versaDGIPSincrono()) {
                    return false;
                }
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                if (!$this->aggiornaDGIPSincrono()) {
                    return false;
                }
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                if (!$this->aggiornaDGIPSincrono()) {
                    return false;
                }
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
        /*
         * Fascicolo Pratica Amministrativa
         */
        if ($this->anapro_rec['PROPAR'] == 'I') {
            $proLibConservazione = new proLibConservazione();
            if (!$proLibConservazione->SalvaEsitiConservazioneFascicolo($this->anapro_rec['PROFASKEY'])) {
                // log salvataggio esito.
                $this->logger->info('Salvataggio dati Fascicolo: ' . $proLibConservazione->getErrMessage());
            }
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
        if (!$this->parametriManager['DIGIP_URLSERVIZIO']) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Non è possibile procedere. Parametri DIGIP MARCHE Conservazione non definiti.";
            return false;
        }
        if (!$this->parametriManager['DIGIP_URLMODIFICA']) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Non è possibile procedere. Parametri DIGIP MARCHE di Aggiornamento Conservazione non definiti di.";
            return false;
        }
        return true;
    }

    protected function getBaseDatiUnitaDocumentarie() {
        if (!parent::getBaseDatiUnitaDocumentarie()) {
            return false;
        }


        $this->logger->info('Carico il dizionario DIGIP per la Conservazione');
        $this->baseDati[self::K_VERSIONE] = self::Versione;
        $this->baseDati[self::K_AMBIENTE] = $this->parametriManager['DIGIP_AMBIENTE'];
        $this->baseDati[self::K_ENTE] = $this->parametriManager['DIGP_ENTE'];
        $this->baseDati[self::K_STRUTTURA] = $this->parametriManager['DIGIP_STRUTTURA'];
        $this->baseDati[self::K_USERID] = $this->parametriManager['DIGIP_USERID'];
        $this->baseDati[self::K_PASSWORD] = $this->parametriManager['DIGIP_PASSWORD'];
        $this->baseDati[self::K_URLSERVIZIO] = $this->parametriManager['DIGIP_URLSERVIZIO'];
        $this->baseDati[self::K_URLMODIFICA] = $this->parametriManager['DIGIP_URLMODIFICA'];
        $this->baseDati[self::K_VERSIONEDATISPECIFICI] = self::VersioneDatiSpecifici;




        $this->getBaseDatiSpecifici();
        $this->getBaseDatiComponentiFile();
        return true;
    }

    private function LoadBaseDatiFascicoli() {
        $ElencoFascicoli = $this->proLibFascicolo->CaricaFascicoliProtocollo($this->anapro_rec['PRONUM'], $this->anapro_rec['PROPAR']);
        if ($this->anapro_rec['PROCCF']) {
            $Classificazione = $this->DecodClassifica($this->anapro_rec['PROCCF']);
            $this->baseDati[proConservazioneManagerHelper::K_FASCICOLOPRINCIPALE][proConservazioneManagerHelper::K_CLASSIFICAZIONE] = $Classificazione;
        }
        foreach ($ElencoFascicoli as $Fascicolo) {
            $BaseFas = array();
            // Classificazione ORGCCF
            $Classificazione = $this->DecodClassifica($Fascicolo['ORGCCF']);
            $BaseFas[proConservazioneManagerHelper::K_CLASSIFICAZIONE] = $Classificazione;
            $BaseFas[proConservazioneManagerHelper::K_FASCICOLO_CODICE] = $Fascicolo['ORGKEY'];
            $BaseFas[proConservazioneManagerHelper::K_FASCICOLO_OGGETTO] = $Fascicolo['ORGDES'];
            if ($Fascicolo['CODICE_SOTTOFAS']) {
                $BaseFas[proConservazioneManagerHelper::K_SOTTFAS_CODICE] = $Fascicolo['CODICE_SOTTOFAS'];
                $BaseFas[proConservazioneManagerHelper::_SOTTFAS_OGGETTO] = $Fascicolo['OGGETTO_SOTTOFAS'];
            }
            /* Fascicolo principale */
            if ($this->anapro_rec['PROFASKEY'] && $Fascicolo['ORGKEY'] == $this->anapro_rec['PROFASKEY']) {
                $this->baseDati[proConservazioneManagerHelper::K_FASCICOLOPRINCIPALE] = $BaseFas;
            } else {
                $this->baseDati[proConservazioneManagerHelper::K_FASCICOLI][] = $BaseFas;
            }
        }

        return $this->baseDati;
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
            $AnadocChiave = 'ANADOC-' . $Anadoc_rec['ROWID'];
            $Allegato = array();
            $Allegato[self::K_IDDOCUMENTO] = $AnadocChiave;
            /* Dati Specifici Documento */
            if (!$this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC]) {
                $this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC] = "Generico";
            }
            $Allegato[self::K_DATISPECIFICITIPODOCUMENTO] = $this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC];
            $Allegato[self::K_IDUNIVOCO] = $AnadocChiave;

            /* Specifico */
            switch ($this->baseDati[proConservazioneManagerHelper::K_TIPOPROT]) {
                case 'A':
                    $Allegato[self::K_ORIGINE] = 'Ingresso';
                    break;

                default:
                    $Allegato[self::K_ORIGINE] = 'Uscita';
                    break;
            }

            /* Componenti */
//            $Allegato[self::K_ID] = $Anadoc_rec['ROWID'];
            $Allegato[self::K_ID] = $AnadocChiave;
            $Allegato[self::K_ORDINEPRESENTAZIONE] = $Ordine;
            $Allegato[self::K_TIPOCOMPONENTE] = 'CONTENUTO';
            $Allegato[self::K_TIPOSUPPORTOCOMPONENTE] = 'FILE';
            $Allegato[self::K_NOMECOMPONENTE] = $Anadoc_rec['DOCNAME'];
            $Allegato[self::K_FORMATOFILEVERSATO] = $Anadoc_rec['ESTENSIONE'];
            $Allegato[self::K_HASHVERSATO] = $Anadoc_rec['HASHFILE'];
            $Allegato[self::K_IDCOMPONENTEVERSATO] = $AnadocChiave;
            $Allegato[self::K_UTILIZZODATAFIRMAPERRIFTEMP] = 'false'; // Stringa?

            $Allegato[self::K_FILEPATH] = $Anadoc_rec['FILEPATH'];

            /* Controlli se Principale o Allegato */
            if ($Anadoc_rec['DOCTIPO'] == 'PRINCIPALE') {
                /* Principale */
                $Allegato[self::K_ORDINEPRESENTAZIONE] = 1;
                $this->baseDati[self::K_DOCPRINCIPALE] = $Allegato;
            } else {
                /* Allegato */
                $this->baseDati[self::K_ALLEGATI][] = $Allegato;
                // Incremento conteggio allegati.
                $Ordine++;
            }
        }
        /*
         * Elaborazione Base Dati Mail
         */
        foreach ($this->baseDati[proConservazioneManagerHelper::K_MAIL_ARCHIVIO_TAB] as $key => $Mail_rec) {
            /*
             * Lettura Dati.
             */
            $AllegatoChiave = 'MAIL-' . $Mail_rec['ROWID'];
            $Allegato = array();
            $Allegato[self::K_IDDOCUMENTO] = $AllegatoChiave;
            /* Dati Specifici Documento */
            if (!$this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC]) {
                $this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC] = "Generico";
            }
            $Allegato[self::K_DATISPECIFICITIPODOCUMENTO] = $this->baseDati[proConservazioneManagerHelper::K_PROTIPODOC];
            $Allegato[self::K_IDUNIVOCO] = $AllegatoChiave;

            /* Specifico */
            switch ($this->baseDati[proConservazioneManagerHelper::K_TIPOPROT]) {
                case 'A':
                    $Allegato[self::K_ORIGINE] = 'Ingresso';
                    break;

                default:
                    $Allegato[self::K_ORIGINE] = 'Uscita';
                    break;
            }

            /* Componenti */
            $Allegato[self::K_ID] = $AllegatoChiave;
            $Allegato[self::K_ORDINEPRESENTAZIONE] = $Ordine;
            $Allegato[self::K_TIPOCOMPONENTE] = 'CONTENUTO';
            $Allegato[self::K_TIPOSUPPORTOCOMPONENTE] = 'FILE';
            $Allegato[self::K_NOMECOMPONENTE] = $Mail_rec['NOMEFILE'];
            $Allegato[self::K_FORMATOFILEVERSATO] = $Mail_rec['ESTENSIONE'];
            $Allegato[self::K_HASHVERSATO] = $Mail_rec['HASHFILE'];
            $Allegato[self::K_IDCOMPONENTEVERSATO] = $AllegatoChiave;
            $Allegato[self::K_UTILIZZODATAFIRMAPERRIFTEMP] = 'false'; // Stringa?

            $Allegato[self::K_FILEPATH] = $Mail_rec['FILEPATH'];

            /* Allegato */
            $this->baseDati[self::K_ALLEGATI][] = $Allegato;
            // Incremento conteggio allegati.
            $Ordine++;
        }
    }

    private function getBaseDatiSpecifici() {
        /*
         * Dati Unità Documentaria DIGIP
         * Lettura in base della unità documentaria:
         */
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                $TipologiaUnitaDocumentaria = 'Registro di Protocollo'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Registro di Protocollo'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                // Se è registro servono dati base del registro.
                if (!proConservazioneManagerHelper::getBaseDatiRegistroProtocollo($this->baseDati)) {
                    return false;
                }
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT:
                $TipologiaUnitaDocumentaria = 'Documento protocollato'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento protocollato'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                $TipologiaUnitaDocumentaria = 'Documento Formale'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento interno'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
                $TipologiaUnitaDocumentaria = 'Documento protocollato'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento protocollato aggiornato'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
                $TipologiaUnitaDocumentaria = 'Documento Formale'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento interno aggiornato'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;

            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
                $TipologiaUnitaDocumentaria = 'Documento protocollato'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento protocollato annullato'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                $TipologiaUnitaDocumentaria = 'Documento Formale'; // Potrebbero essere parametrici.
                $TipoDocumento = 'Documento interno annullato'; // Potrebbero essere parametrici.
                $TipoStruttura = 'DocumentoGenerico'; // Potrebbero essere parametrici.
                $TipoConservazione = 'SOSTITUTIVA'; // Potrebbero essere parametrici.
                break;

            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO:
                $TipologiaUnitaDocumentaria = 'SUAP Documento';
                $TipoDocumento = 'Generico'; // Verificare se è possibile inserire "SUAP Documento"
                $TipoStruttura = 'DocumentoGenerico';
                $TipoConservazione = 'SOSTITUTIVA';
                // Se è registro servono dati base del registro.
                if (!self::getBaseDatiSecificiSUAPDocumento($this->baseDati)) {
                    return false;
                }
                break;

            default :
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = 'Unità documentaria non prevista nella conservazione.';
                return false;
                break;
        }

        $this->baseDati[self::K_TIPOLOGIAUNITADOCUMENTARIA] = $TipologiaUnitaDocumentaria;
        $this->baseDati[self::K_TIPODOCUMENTO] = $TipoDocumento; //TipoDocumento
        $this->baseDati[self::K_TIPOSTRUTTURA] = $TipoStruttura;
        $this->baseDati[self::K_TIPOCONSERVAZIONE] = $TipoConservazione;
        return true;
    }

    private function creaXmlUnitaDocumentaria() {
        /*
         * Scrivo l'xml
         * 
         */
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $ProfiloArchivistico = $this->getXmlProfiloArchivistico();
        $DatiSpecifici = $this->getXmlDatiSpecifici();
        $DatiSpecificiDocumento = $this->getXmlDatiSpecificiDocumento();
        $xmlAllegati = $this->getXmlAllegati();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<UnitaDocumentaria>
    <Intestazione>
        <Versione>{$this->baseDati[self::K_VERSIONE]}</Versione>
        <Versatore>
            <Ambiente>{$this->baseDati[self::K_AMBIENTE]}</Ambiente>
            <Ente>{$this->baseDati[self::K_ENTE]}</Ente>
            <Struttura>{$this->baseDati[self::K_STRUTTURA]}</Struttura>
            <UserID>{$this->baseDati[self::K_USERID]}</UserID>
        </Versatore>
        <Chiave>
            <Numero>{$this->baseDati[proConservazioneManagerHelper::K_NUMERO]}</Numero>
            <Anno>{$this->baseDati[proConservazioneManagerHelper::K_ANNO]}</Anno>
            <TipoRegistro>{$this->baseDati[proConservazioneManagerHelper::K_TIPOREGISTRO]}</TipoRegistro>
        </Chiave>
        <TipologiaUnitaDocumentaria>{$this->baseDati[self::K_TIPOLOGIAUNITADOCUMENTARIA]}</TipologiaUnitaDocumentaria>
    </Intestazione>
    {$ProfiloArchivistico}
    <ProfiloUnitaDocumentaria>
        <Oggetto>{$this->baseDati[proConservazioneManagerHelper::K_OGGETTO]}</Oggetto>
        <Data>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Data>
    </ProfiloUnitaDocumentaria>
    {$DatiSpecificiDocumento}
    <NumeroAllegati>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROALLEGATI]}</NumeroAllegati>
    <DocumentoPrincipale>
        <IDDocumento>{$BaseDatiDocPrincipale[self::K_IDDOCUMENTO]}</IDDocumento>
        <TipoDocumento>{$this->baseDati[self::K_TIPODOCUMENTO]}</TipoDocumento>
        {$DatiSpecifici}
        <StrutturaOriginale>
            <TipoStruttura>{$this->baseDati[self::K_TIPOSTRUTTURA]}</TipoStruttura>
            <Componenti>
                <Componente>
                    <ID>{$BaseDatiDocPrincipale[self::K_ID]}</ID>
                    <OrdinePresentazione>{$BaseDatiDocPrincipale[self::K_ORDINEPRESENTAZIONE]}</OrdinePresentazione>
                    <TipoComponente>{$BaseDatiDocPrincipale[self::K_TIPOCOMPONENTE]}</TipoComponente>
                    <TipoSupportoComponente>{$BaseDatiDocPrincipale[self::K_TIPOSUPPORTOCOMPONENTE]}</TipoSupportoComponente>
                    <NomeComponente>{$BaseDatiDocPrincipale[self::K_NOMECOMPONENTE]}</NomeComponente>
                    <FormatoFileVersato>{$BaseDatiDocPrincipale[self::K_FORMATOFILEVERSATO]}</FormatoFileVersato>
                    <HashVersato>{$BaseDatiDocPrincipale[self::K_HASHVERSATO]}</HashVersato>
                    <IDComponenteVersato>{$BaseDatiDocPrincipale[self::K_IDCOMPONENTEVERSATO]}</IDComponenteVersato>
                    <UtilizzoDataFirmaPerRifTemp>{$BaseDatiDocPrincipale[self::K_UTILIZZODATAFIRMAPERRIFTEMP]}</UtilizzoDataFirmaPerRifTemp>                                                
                </Componente>
            </Componenti>
        </StrutturaOriginale>
    </DocumentoPrincipale>
    $xmlAllegati
</UnitaDocumentaria>";

        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function creaXmlUnitaDocumentariaInterno() {
        /*
         * Scrivo l'xml
         * 
         */
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $ProfiloArchivistico = $this->getXmlProfiloArchivistico();
        $DatiSpecifici = $this->getXmlDatiSpecifici();
        $xmlAllegati = $this->getXmlAllegati();
        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<UnitaDocumentaria>
    <Intestazione>
        <Versione>{$this->baseDati[self::K_VERSIONE]}</Versione>
        <Versatore>
            <Ambiente>{$this->baseDati[self::K_AMBIENTE]}</Ambiente>
            <Ente>{$this->baseDati[self::K_ENTE]}</Ente>
            <Struttura>{$this->baseDati[self::K_STRUTTURA]}</Struttura>
            <UserID>{$this->baseDati[self::K_USERID]}</UserID>
        </Versatore>
        <Chiave>
            <Numero>{$this->baseDati[proConservazioneManagerHelper::K_NUMERO]}</Numero>
            <Anno>{$this->baseDati[proConservazioneManagerHelper::K_ANNO]}</Anno>
            <TipoRegistro>{$this->baseDati[proConservazioneManagerHelper::K_TIPOREGISTRO]}</TipoRegistro>
        </Chiave>
        <TipologiaUnitaDocumentaria>{$this->baseDati[self::K_TIPOLOGIAUNITADOCUMENTARIA]}</TipologiaUnitaDocumentaria>
    </Intestazione>
    {$ProfiloArchivistico}
    <ProfiloUnitaDocumentaria>
        <Oggetto>{$this->baseDati[proConservazioneManagerHelper::K_OGGETTO]}</Oggetto>
        <Data>{$this->baseDati[proConservazioneManagerHelper::K_DATA]}</Data>
        <Cartaceo>false</Cartaceo>
    </ProfiloUnitaDocumentaria>
    <NumeroAllegati>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROALLEGATI]}</NumeroAllegati>
    <DocumentoPrincipale>
        <IDDocumento>{$BaseDatiDocPrincipale[self::K_IDDOCUMENTO]}</IDDocumento>
        <TipoDocumento>{$this->baseDati[self::K_TIPODOCUMENTO]}</TipoDocumento>
        {$DatiSpecifici}
        <StrutturaOriginale>
            <TipoStruttura>{$this->baseDati[self::K_TIPOSTRUTTURA]}</TipoStruttura>
            <Componenti>
                <Componente>
                    <ID>{$BaseDatiDocPrincipale[self::K_ID]}</ID>
                    <OrdinePresentazione>{$BaseDatiDocPrincipale[self::K_ORDINEPRESENTAZIONE]}</OrdinePresentazione>
                    <TipoComponente>{$BaseDatiDocPrincipale[self::K_TIPOCOMPONENTE]}</TipoComponente>
                    <TipoSupportoComponente>{$BaseDatiDocPrincipale[self::K_TIPOSUPPORTOCOMPONENTE]}</TipoSupportoComponente>
                    <NomeComponente>{$BaseDatiDocPrincipale[self::K_NOMECOMPONENTE]}</NomeComponente>
                    <FormatoFileVersato>{$BaseDatiDocPrincipale[self::K_FORMATOFILEVERSATO]}</FormatoFileVersato>
                    <HashVersato>{$BaseDatiDocPrincipale[self::K_HASHVERSATO]}</HashVersato>
                    <IDComponenteVersato>{$BaseDatiDocPrincipale[self::K_IDCOMPONENTEVERSATO]}</IDComponenteVersato>
                    <UtilizzoDataFirmaPerRifTemp>{$BaseDatiDocPrincipale[self::K_UTILIZZODATAFIRMAPERRIFTEMP]}</UtilizzoDataFirmaPerRifTemp>                                                
                </Componente>
            </Componenti>
        </StrutturaOriginale>
    </DocumentoPrincipale>
    $xmlAllegati
</UnitaDocumentaria>";

        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function creaXmlUnitaDocumentariaAnnullamento() {
        /*
         * Scrivo l'xml
         * 
         */
        $DatiSpecifici = $this->getXmlDatiSpecifici();

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<UnitaDocAggAllegati xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
    <Intestazione>
        <Versione>{$this->baseDati[self::K_VERSIONE]}</Versione>
        <Versatore>
            <Ambiente>{$this->baseDati[self::K_AMBIENTE]}</Ambiente>
            <Ente>{$this->baseDati[self::K_ENTE]}</Ente>
            <Struttura>{$this->baseDati[self::K_STRUTTURA]}</Struttura>
            <UserID>{$this->baseDati[self::K_USERID]}</UserID>
        </Versatore>
        <Chiave>
            <Numero>{$this->baseDati[proConservazioneManagerHelper::K_NUMERO]}</Numero>
            <Anno>{$this->baseDati[proConservazioneManagerHelper::K_ANNO]}</Anno>
            <TipoRegistro>{$this->baseDati[proConservazioneManagerHelper::K_TIPOREGISTRO]}</TipoRegistro>
        </Chiave>
       
    </Intestazione>
      <Annesso>
            <IDDocumento>001</IDDocumento>
            <TipoDocumento>{$this->baseDati[self::K_TIPODOCUMENTO]}</TipoDocumento>
            <ProfiloDocumento>
                 <Descrizione>{$this->baseDati[proConservazioneManagerHelper::K_OGGETTO]}</Descrizione>
            </ProfiloDocumento>
            {$DatiSpecifici}
            <StrutturaOriginale>
                <TipoStruttura>{$this->baseDati[self::K_TIPOSTRUTTURA]}</TipoStruttura>
                <Componenti>
                    <Componente>
                        <ID>10510</ID>
                        <OrdinePresentazione>1</OrdinePresentazione>
                        <TipoComponente>CONTENUTO</TipoComponente>
                        <TipoSupportoComponente>METADATI</TipoSupportoComponente>
                  </Componente>
                </Componenti>
            </StrutturaOriginale>
    </Annesso>
</UnitaDocAggAllegati>";

        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function creaXmlUnitaDocumentariaAggiornamento() {
        /*
         * Scrivo l'xml
         * 
         */
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $ProfiloArchivistico = $this->getXmlProfiloArchivistico();
        $DatiSpecifici = $this->getXmlDatiSpecifici();
        $xmlAllegati = $this->getXmlAllegati();
        $this->baseDati[self::K_ALLEGATI] = array();
        $this->baseDati[self::K_DOCPRINCIPALE] = array();

        $xml = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<UnitaDocAggAllegati xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">
    <Intestazione>
        <Versione>{$this->baseDati[self::K_VERSIONE]}</Versione>
        <Versatore>
            <Ambiente>{$this->baseDati[self::K_AMBIENTE]}</Ambiente>
            <Ente>{$this->baseDati[self::K_ENTE]}</Ente>
            <Struttura>{$this->baseDati[self::K_STRUTTURA]}</Struttura>
            <UserID>{$this->baseDati[self::K_USERID]}</UserID>
        </Versatore>
        <Chiave>
            <Numero>{$this->baseDati[proConservazioneManagerHelper::K_NUMERO]}</Numero>
            <Anno>{$this->baseDati[proConservazioneManagerHelper::K_ANNO]}</Anno>
            <TipoRegistro>{$this->baseDati[proConservazioneManagerHelper::K_TIPOREGISTRO]}</TipoRegistro>
        </Chiave>
    </Intestazione>
      <Annesso>
            <IDDocumento>001</IDDocumento>
            <TipoDocumento>Documento protocollato aggiornato</TipoDocumento>
            <ProfiloDocumento>
                 <Descrizione>{$this->baseDati[proConservazioneManagerHelper::K_OGGETTO]}</Descrizione>
            </ProfiloDocumento>
            {$DatiSpecifici}
            <StrutturaOriginale>
                <TipoStruttura>{$this->baseDati[self::K_TIPOSTRUTTURA]}</TipoStruttura>
                <Componenti>
                    <Componente>
                        <ID>99999</ID>
                        <OrdinePresentazione>1</OrdinePresentazione>
                        <TipoComponente>CONTENUTO</TipoComponente>
                        <TipoSupportoComponente>METADATI</TipoSupportoComponente>
                  </Componente>
                </Componenti>
            </StrutturaOriginale>
    </Annesso>
</UnitaDocAggAllegati>";

        $this->xmlRichiesta = utf8_encode($xml);
    }

    private function getXmlAllegati() {
        $DatiSpecifici = $this->getXmlDatiSpecificiAllegati();
        $xmlAllegati = '';
        if ($this->baseDati[self::K_ALLEGATI]) {
            $xmlAllegati .= '<Allegati>';
            foreach ($this->baseDati[self::K_ALLEGATI] as $Allegato) {
                $xmlComponenteAlle = $this->getXmlComponenteAllegato($Allegato);
                $xmlAllegati .= '<Allegato>
                                   <IDDocumento>' . $Allegato[self::K_IDDOCUMENTO] . '</IDDocumento>
                                   <TipoDocumento>' . $this->baseDati[self::K_TIPODOCUMENTO] . '</TipoDocumento>
                                    ' . $DatiSpecifici . '           
                                    <StrutturaOriginale>
                                     <TipoStruttura>' . $this->baseDati[self::K_TIPOSTRUTTURA] . '</TipoStruttura> 
                                       <Componenti>
                                          <Componente>
                                           ' . $xmlComponenteAlle . '                                
                                          </Componente>
                                       </Componenti>
                                    </StrutturaOriginale>
                               </Allegato> ';
            }
            $xmlAllegati .= '</Allegati>';
        }

        return $xmlAllegati;
    }

    private function getXmlDatiSpecificiDocProtocollatoAllegato() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
                <Origine>{$BaseDatiDocPrincipale[self::K_ORIGINE]}</Origine>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiDocInternoAllegato() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiRegistroGiornalieroAllegato() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
                <Origine>{$BaseDatiDocPrincipale[self::K_ORIGINE]}</Origine>
            </DatiSpecifici>";
        return $xml;
    }

    //Per ora utilizzato default
    private function getXmlDatiSpecificiAnnullamentoAllegato() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiAllegati() {
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
                return $this->getXmlDatiSpecificiDocProtocollatoAllegato();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                return $this->getXmlDatiSpecificiDocInternoAllegato();

            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                return $this->getXmlDatiSpecificiRegistroGiornalieroAllegato();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                return $this->getXmlDatiSpecificiAnnullamentoAllegato();
                break;
            default:
                return false;
                break;
        }
    }

    private function getXmlComponenteAllegato($Allegato) {
        $xmlComponenteAlle = '
                                            <ID>' . $Allegato[self::K_ID] . '</ID>
                                            <OrdinePresentazione>' . $Allegato[self::K_ORDINEPRESENTAZIONE] . '</OrdinePresentazione>
                                            <TipoComponente>' . $Allegato[self::K_TIPOCOMPONENTE] . '</TipoComponente>
                                            <TipoSupportoComponente>' . $Allegato[self::K_TIPOSUPPORTOCOMPONENTE] . '</TipoSupportoComponente>
                                            <NomeComponente>' . $Allegato[self::K_NOMECOMPONENTE] . '</NomeComponente>
                                            <FormatoFileVersato>' . $Allegato[self::K_FORMATOFILEVERSATO] . '</FormatoFileVersato>
                                            <HashVersato>' . $Allegato[self::K_HASHVERSATO] . '</HashVersato>
                                            <IDComponenteVersato>' . $Allegato[self::K_IDCOMPONENTEVERSATO] . '</IDComponenteVersato>
					    <UtilizzoDataFirmaPerRifTemp>' . $Allegato[self::K_UTILIZZODATAFIRMAPERRIFTEMP] . '</UtilizzoDataFirmaPerRifTemp> ';
        return $xmlComponenteAlle;
    }

    private function getXmlAllegatiAgg() {
        $xmlAllegatiAgg = '';
        // Elenco Allegati Modificati: da modificare/aggiungere. PER PROVA GLI STESSI.
        // IL PRINCIPALE DEVE ESSERE QUI? MAGARI CON ORDINE 1?
        foreach ($this->baseDati[self::K_ALLEGATI] as $Allegato) {
            $xmlComponenteAlle = $this->getXmlComponenteAllegato($Allegato);
            $xmlAllegatiAgg .= " <Componente>
                               {$xmlComponenteAlle}
                               </Componente> ";
        }
        return $xmlAllegatiAgg;
    }

    private function getXmlProfiloArchivistico() {
        $xmlFascicoli = '';

        if ($this->baseDati[proConservazioneManagerHelper::K_FASCICOLI] || $this->baseDati[proConservazioneManagerHelper::K_FASCICOLOPRINCIPALE]) {
            $xmlFascicoli .= '
                            <ProfiloArchivistico>';
            if ($this->baseDati[proConservazioneManagerHelper::K_FASCICOLOPRINCIPALE]) {
                $FasPrinc = $this->baseDati[proConservazioneManagerHelper::K_FASCICOLOPRINCIPALE];
                $xmlFascicoli .= '
                            <FascicoloPrincipale>
                                <Classifica>' . $FasPrinc[proConservazioneManagerHelper::K_CLASSIFICAZIONE] . '</Classifica>';
                if ($FasPrinc[proConservazioneManagerHelper::K_FASCICOLO_CODICE]) {
                    $xmlFascicoli .= '<Fascicolo>
                                         <Identificativo>' . $FasPrinc[proConservazioneManagerHelper::K_FASCICOLO_CODICE] . '</Identificativo>
                                         <Oggetto>' . $FasPrinc[proConservazioneManagerHelper::K_FASCICOLO_OGGETTO] . '</Oggetto>
                                     </Fascicolo>
                            ';
                }
                if ($FasPrinc[proConservazioneManagerHelper::K_SOTTFAS_CODICE]) {
                    $xmlFascicoli .= '
                            <SottoFascicolo>
                                <Identificativo>' . $FasPrinc[proConservazioneManagerHelper::K_SOTTFAS_CODICE] . '</Identificativo>
                                <Oggetto>' . $FasPrinc[proConservazioneManagerHelper::_SOTTFAS_OGGETTO] . '</Oggetto>
                            </SottoFascicolo>
                            ';
                }
                $xmlFascicoli .= '</FascicoloPrincipale>';
            }
            if ($this->baseDati[proConservazioneManagerHelper::K_FASCICOLI]) {
                $xmlFascicoli .= '<FascicoliSecondari>';
                foreach ($this->baseDati[proConservazioneManagerHelper::K_FASCICOLI] as $Fascicolo) {
                    $xmlFascicoli .= '
                                        <FascicoloSecondario>
                                            <Classifica>' . $Fascicolo[proConservazioneManagerHelper::K_CLASSIFICAZIONE] . '</Classifica>
                                            <Fascicolo>
                                                <Identificativo>' . $Fascicolo[proConservazioneManagerHelper::K_FASCICOLO_CODICE] . '</Identificativo>
                                                <Oggetto>' . $Fascicolo[proConservazioneManagerHelper::K_FASCICOLO_OGGETTO] . '</Oggetto>
                                             </Fascicolo>
                                         ';
                    if ($Fascicolo[proConservazioneManagerHelper::K_SOTTFAS_CODICE]) {
                        $xmlFascicoli .= '
                                        <SottoFascicolo>
                                            <Identificativo>' . $Fascicolo[proConservazioneManagerHelper::K_SOTTFAS_CODICE] . '</Identificativo>
                                            <Oggetto>' . $Fascicolo[proConservazioneManagerHelper::_SOTTFAS_OGGETTO] . '</Oggetto>
                                        </SottoFascicolo>
                                        ';
                    }
                    $xmlFascicoli .= '</FascicoloSecondario>';
                }
                $xmlFascicoli .= "</FascicoliSecondari>";
            }
            $xmlFascicoli .= '</ProfiloArchivistico>
                     ';
        }
        return $xmlFascicoli;
    }

    private function getXmlDatiSpecificiDocProtocollato() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
                <Origine>{$BaseDatiDocPrincipale[self::K_ORIGINE]}</Origine>
                <MittenteDestinatari>{$this->baseDati[proConservazioneManagerHelper::K_MITTENTEDESTINATARI]}</MittenteDestinatari>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiSUAPDocumento() {
        $BaseDatiDSPE = $this->baseDati[self::K_DSPE];
        $xml = "  
            <DatiSpecifici>
                <SEGNATURA>{$BaseDatiDSPE[self::K_DSPE_SEGNATURA]}</SEGNATURA>
                <DATAREGISTRAZIONE>{$BaseDatiDSPE[self::K_DSPE_DATAREGISTRAZIONE]}</DATAREGISTRAZIONE>
                <OGGETTO>{$BaseDatiDSPE[self::K_DSPE_OGGETTO]}</OGGETTO>
                <MITTENTE_NOME>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_NOME]}</MITTENTE_NOME>
                <MITTENTE_COGNOME>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_COGNOME]}</MITTENTE_COGNOME>
                <MITTENTE_DENOMINAZIONE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_DENOMINAZIONE]}</MITTENTE_DENOMINAZIONE>
                <MITTENTE_CODICEFISCALE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_CODICEFISCALE]}</MITTENTE_CODICEFISCALE>
                <MITTENTE_PARTITAIVA>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_PARTITAIVA]}</MITTENTE_PARTITAIVA>
                <MITTENTE_STATO>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_STATO]}</MITTENTE_STATO>
                <MITTENTE_REGIONE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_REGIONE]}</MITTENTE_REGIONE>
                <MITTENTE_COMUNE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_STATO]}</MITTENTE_COMUNE>
                <MITTENTE_CAP>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_CAP]}</MITTENTE_CAP>
                <MITTENTE_INDIRIZZO>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_INDIRIZZO]}</MITTENTE_INDIRIZZO>
                <MITTENTE_EMAIL>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_EMAIL]}</MITTENTE_EMAIL>
                <MITTENTE_NUMCIVICO>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_NUMCIVICO]}</MITTENTE_NUMCIVICO>
                <MITTENTE_DATARICEZIONE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_DATARICEZIONE]}</MITTENTE_DATARICEZIONE>
                <MITTENTE_MEZZORICEZIONE>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_MEZZORICEZIONE]}</MITTENTE_MEZZORICEZIONE>
                <MITTENTE_SEGNATURAPROTOCOLLO>{$BaseDatiDSPE[self::K_DSPE_MITTENTE_SEGNATURAPROTOCOLLO]}</MITTENTE_SEGNATURAPROTOCOLLO>
                <SOGGPROD_CODICEIPA>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_CODICEIPA]}</SOGGPROD_CODICEIPA>
                <SOGGPROD_DENOMINAZIONE>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_DENOMINAZIONE]}</SOGGPROD_DENOMINAZIONE>
                <SOGGPROD_TIPOSOGGETTO>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_TIPOSOGGETTO]}</SOGGPROD_TIPOSOGGETTO>
                <SOGGPROD_CONDIZIONEGIURIDICA>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_CONDIZIONEGIURIDICA]}</SOGGPROD_CONDIZIONEGIURIDICA>
                <SOGGPROD_STATO>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_STATO]}</SOGGPROD_STATO>
                <SOGGPROD_REGIONE>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_REGIONE]}</SOGGPROD_REGIONE>
                <SOGGPROD_COMUNE>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_COMUNE]}</SOGGPROD_COMUNE>
                <SOGGPROD_CAP>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_CAP]}</SOGGPROD_CAP>
                <SOGGPROD_INDIRIZZO>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_INDIRIZZO]}</SOGGPROD_INDIRIZZO>
                <SOGGPROD_NUMCIV>{$BaseDatiDSPE[self::K_DSPE_SOGGPROD_NUMCIV]}</SOGGPROD_NUMCIV>
                <DESTINATARIO_NOME>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_NOME]}</DESTINATARIO_NOME>
                <DESTINATARIO_COGNOME>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_COGNOME]}</DESTINATARIO_COGNOME>
                <DESTINATARIO_DENOMINAZIONE>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_DENOMINAZIONE]}</DESTINATARIO_DENOMINAZIONE>
                <DESTINATARIO_CODICEFISCALE>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_CODICEFISCALE]}</DESTINATARIO_CODICEFISCALE>
                <DESTINATARIO_PARTITAIVA>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_PARTITAIVA]}</DESTINATARIO_PARTITAIVA>
                <DESTINATARIO_STATO>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_STATO]}</DESTINATARIO_STATO>
                <DESTINATARIO_REGIONE>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_REGIONE]}</DESTINATARIO_REGIONE>
                <DESTINATARIO_COMUNE>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_COMUNE]}</DESTINATARIO_COMUNE>
                <DESTINATARIO_CAP>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_CAP]}</DESTINATARIO_CAP>
                <DESTINATARIO_INDIRIZZO>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_INDIRIZZO]}</DESTINATARIO_INDIRIZZO>
                <DESTINATARIO_EMAIL>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_EMAIL]}</DESTINATARIO_EMAIL>
                <DESTINATARIO_NUMCIV>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_NUMCIV]}</DESTINATARIO_NUMCIV>
                <DESTINATARIO_RICEVUTEPEC_ACC>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_RICEVUTEPEC_ACC]}</DESTINATARIO_RICEVUTEPEC_ACC>
                <DESTINATARIO_RICEVUTEPEC_CONS>{$BaseDatiDSPE[self::K_DSPE_DESTINATARIO_RICEVUTEPEC_CONS]}</DESTINATARIO_RICEVUTEPEC_CONS>
                <LUOGODOCUMENTO>{$BaseDatiDSPE[self::K_DSPE_LUOGODOCUMENTO]}</LUOGODOCUMENTO>
                <TIPODOCUMENTO_CODIDENTIFICATIVO>{$BaseDatiDSPE[self::K_DSPE_TIPODOCUMENTO_CODIDENTIFICATIVO]}</TIPODOCUMENTO_CODIDENTIFICATIVO>
                <TIPODOCUMENTO_NATURA>{$BaseDatiDSPE[self::K_DSPE_TIPODOCUMENTO_NATURA]}</TIPODOCUMENTO_NATURA>
                <TIPODOCUMENTO_ACCESSIBILITA>{$BaseDatiDSPE[self::K_DSPE_TIPODOCUMENTO_ACCESSIBILITA]}</TIPODOCUMENTO_ACCESSIBILITA>
                <DATADOCUMENTO>{$BaseDatiDSPE[self::K_DSPE_DATADOCUMENTO]}</DATADOCUMENTO>
                <IDENTIFICATIVOPROCEDIMENTO>{$BaseDatiDSPE[self::K_DSPE_IDENTIFICATIVOPROCEDIMENTO]}</IDENTIFICATIVOPROCEDIMENTO>
                <ANNOAPERTURA>{$BaseDatiDSPE[self::K_DSPE_ANNOAPERTURA]}</ANNOAPERTURA>
                <ANNOCHIUSURA>{$BaseDatiDSPE[self::K_DSPE_ANNOCHIUSURA]}</ANNOCHIUSURA>
                <PROCAMM_CODICEPROCEDIMENTO>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_CODICEPROCEDIMENTO]}</PROCAMM_CODICEPROCEDIMENTO>
                <PROCAMM_DESCRIZIONE>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_DESCRIZIONE]}</PROCAMM_DESCRIZIONE>
                <PROCAMM_RIFERIMENTINORMATIVI>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_RIFERIMENTINORMATIVI]}</PROCAMM_RIFERIMENTINORMATIVI>
                <PROCAMM_DESTINATARI>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_DESTINATARI]}</PROCAMM_DESTINATARI>
                <PROCAMM_REGIMIABILITATIVI>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_REGIMIABILITATIVI]}</PROCAMM_REGIMIABILITATIVI>
                <PROCAMM_TIPOLOGIA>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_TIPOLOGIA]}</PROCAMM_TIPOLOGIA>
                <PROCAMM_SETTOREATTIVITA>{$BaseDatiDSPE[self::K_DSPE_PROCAMM_SETTOREATTIVITA]}</PROCAMM_SETTOREATTIVITA>
                <CODICEIPA>{$BaseDatiDSPE[self::K_DSPE_CODICEIPA]}</CODICEIPA>
                <AMMINISTRAZIONIPARTECIPANTI>{$BaseDatiDSPE[self::K_DSPE_AMMINISTRAZIONIPARTECIPANTI]}</AMMINISTRAZIONIPARTECIPANTI>
                <ACCESSIBILITA>{$BaseDatiDSPE[self::K_DSPE_ACCESSIBILITA]}</ACCESSIBILITA>
                <PROTOCOLLO>{$BaseDatiDSPE[self::K_DSPE_PROTOCOLLO]}</PROTOCOLLO>
                <TIPOPROTOCOLLO>{$BaseDatiDSPE[self::K_DSPE_TIPOPROTOCOLLO]}</TIPOPROTOCOLLO>
                <DATAPROTOCOLLO>{$BaseDatiDSPE[self::K_DSPE_DATAPROTOCOLLO]}</DATAPROTOCOLLO>
                <ALTRIMITTDEST>{$BaseDatiDSPE[self::K_DSPE_ALTRIMITTDEST]}</ALTRIMITTDEST>

            </DatiSpecifici>"; //TEMPOMINIMOCONSERVAZIONE
        return $xml;
    }

    private function getXmlDatiSpecificiDocInterno() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiRegistroGiornaliero() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
                <Origine>{$BaseDatiDocPrincipale[self::K_ORIGINE]}</Origine> 
                <DataChiusura>{$this->baseDati[proConservazioneManagerHelper::K_DATACHIUSURA]}</DataChiusura>    
                <SoggettoProduttore>{$this->baseDati[proConservazioneManagerHelper::K_SOGGETTOPRODUTTORE]}</SoggettoProduttore>    
                <Responsabile>{$this->baseDati[proConservazioneManagerHelper::K_RESPONSABILE]}</Responsabile>    
                <NumeroPrimaRegistrazione>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROPRIMAREGISTRAZIONE]}</NumeroPrimaRegistrazione>    
                <NumeroUltimaRegistrazione>{$this->baseDati[proConservazioneManagerHelper::K_NUMEROULTIMAREGISTRAZIONE]}</NumeroUltimaRegistrazione>    
                <DataPrimaRegistrazione>{$this->baseDati[proConservazioneManagerHelper::K_DATAPRIMAREGISTRAZIONE]}</DataPrimaRegistrazione>    
                <DataUltimaRegistrazione>{$this->baseDati[proConservazioneManagerHelper::K_DATAULTIMAREGISTRAZIONE]}</DataUltimaRegistrazione>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecificiAnnullamento() {
        $BaseDatiDocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
        $xml = "  
            <DatiSpecifici>
                <VersioneDatiSpecifici>{$this->baseDati[self::K_VERSIONEDATISPECIFICI]}</VersioneDatiSpecifici>
                <TipoDocumento>{$BaseDatiDocPrincipale[self::K_DATISPECIFICITIPODOCUMENTO]}</TipoDocumento>
                <Origine>{$BaseDatiDocPrincipale[self::K_ORIGINE]}</Origine>
                <DataAnnullamento>{$this->baseDati[proConservazioneManagerHelper::K_DATAANNULLAMENTO]}</DataAnnullamento>
                <MotivoAnnullamento>{$this->baseDati[proConservazioneManagerHelper::K_MOTIVOANNULLAMENTO]}</MotivoAnnullamento>
            </DatiSpecifici>";
        return $xml;
    }

    private function getXmlDatiSpecifici() {
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_PROT:
            case proConservazioneManagerHelper::K_UNIT_PROT_AGG:
                return $this->getXmlDatiSpecificiDocProtocollato();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_AGG:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO:
                return $this->getXmlDatiSpecificiDocInterno();

            case proConservazioneManagerHelper::K_UNIT_REGISTRO:
                return $this->getXmlDatiSpecificiRegistroGiornaliero();
                break;
            case proConservazioneManagerHelper::K_UNIT_PROT_ANN:
            case proConservazioneManagerHelper::K_UNIT_PROT_INTERNO_ANN:
                return $this->getXmlDatiSpecificiAnnullamento();
                break;
            //Sono dati specifici del documento
            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO:
            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO_AGG:
                return false;
            default:
                return false;
                break;
        }
    }

    private function getXmlDatiSpecificiDocumento() {
        switch ($this->unitaDocumentaria) {
            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO:
            case proConservazioneManagerHelper::K_UNIT_SUAP_DOCUMENTO_AGG:
                return $this->getXmlDatiSpecificiSUAPDocumento();

            default:
                return false;
                break;
        }
    }

    public function versaDGIPSincrono() {
        $assoc = array();

        /**
         * Versione
         */
        $assoc['VERSIONE'] = self::Versione;
        /**
         * Loginname
         */
        $assoc['LOGINNAME'] = $this->baseDati[self::K_USERID];
        /**
         * Password
         */
        $assoc['PASSWORD'] = $this->baseDati[self::K_PASSWORD];


        $assoc['XMLSIP'] = $this->xmlRichiesta;

        /*
         * File Binari
         */
        $files = array();
        /* Nuova Versione di Copia e Lettura Allegato. */
        if ($this->baseDati[self::K_DOCPRINCIPALE]) {
            $DocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
            $ID = $DocPrincipale[self::K_ID];
            $files[$ID] = array("filecontent" => $DocPrincipale[self::K_FILEPATH], "filename" => utf8_encode($DocPrincipale[self::K_NOMECOMPONENTE]));
        }
        foreach ($this->baseDati[self::K_ALLEGATI]as $Allegato) {
            $ID = $Allegato[self::K_ID];
            $files[$ID] = array("filecontent" => $Allegato[self::K_FILEPATH], "filename" => utf8_encode($Allegato[self::K_NOMECOMPONENTE]));
        }

        /*
         * Preparazione chiamata Rest
         */

        $data = array(
            "FIELDS" => $assoc,
            "FILES" => $files
        );
        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $restClient = new itaRestClient();

        $restClient->setDebugLevel(true);
        $restClient->setTimeout(10); //!!
        $restClient->setCurlopt_url($this->baseDati[self::K_URLSERVIZIO]);
        if ($restClient->postMultipart('', $data)) {
//            file_put_contents('C:/Works/debugRest.txt', $restClient->getDebug());
//        if ($restClient->post('', $assoc)) {
            $this->xmlResponso = $restClient->getResult();
        } else {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $restClient->getErrMessage();
            return false;
        }
        return true;
    }

    public function aggiornaDGIPSincrono() {
        $assoc = array();

        /**
         * Versione
         */
        $assoc['VERSIONE'] = self::Versione;
        /**
         * Loginname
         */
        $assoc['LOGINNAME'] = $this->baseDati[self::K_USERID];
        /**
         * Password
         */
        $assoc['PASSWORD'] = $this->baseDati[self::K_PASSWORD];


        $assoc['XMLSIP'] = $this->xmlRichiesta;

        /*
         * File Binari
         */
        $files = array();
        /* Nuova Versione di Copia e Lettura Allegato. */
        if ($this->baseDati[self::K_DOCPRINCIPALE]) {
            $DocPrincipale = $this->baseDati[self::K_DOCPRINCIPALE];
            $ID = $DocPrincipale[self::K_ID];
            $files[$ID] = array("filecontent" => $DocPrincipale[self::K_FILEPATH], "filename" => utf8_encode($DocPrincipale[self::K_NOMECOMPONENTE]));
        }
        foreach ($this->baseDati[self::K_ALLEGATI]as $Allegato) {
            $ID = $Allegato[self::K_ID];
            $files[$ID] = array("filecontent" => $Allegato[self::K_FILEPATH], "filename" => utf8_encode($Allegato[self::K_NOMECOMPONENTE]));
        }

        /*
         * Preparazione chiamata Rest
         */

        $data = array(
            "FIELDS" => $assoc,
            "FILES" => $files
        );
        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $restClient = new itaRestClient();

        $restClient->setDebugLevel(true);
        $restClient->setTimeout(10); //!!
        $restClient->setCurlopt_url($this->baseDati[self::K_URLMODIFICA]);
        if ($restClient->postMultipart('', $data)) {
//            file_put_contents('C:/Works/debugRest.txt', $restClient->getDebug());
//        if ($restClient->post('', $assoc)) {
            $this->xmlResponso = $restClient->getResult();
        } else {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = $restClient->getErrMessage();
            return false;
        }
        return true;
    }

    public function parseEsitoVersamento() {
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($this->xmlResponso);
        if (!$retXml) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "File XML . Impossibile leggere il contenuto del Messaggio xml.";
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Lettura XML. Impossibile estrarre i dati Messaggio xml.";
            return false;
        }
        $this->datiMinimiEsitoVersamento = array();
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CONSERVATORE] = self::CLASSE_PARAMETRI;
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_VERSIONE] = utf8_decode($arrayXml['Versione'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_DATAVERSAMENTO] = utf8_decode($arrayXml['DataVersamento'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_ESITO] = utf8_decode($arrayXml['EsitoGenerale'][0]['CodiceEsito'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CODICEERRORE] = utf8_decode($arrayXml['EsitoGenerale'][0]['CodiceErrore'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_MESSAGGIOERRORE] = utf8_decode($arrayXml['EsitoGenerale'][0]['MessaggioErrore'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] = "Ambiente:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Ambiente'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Ente:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Ente'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Struttura:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Struttura'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Numero:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['Numero'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Anno:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['Anno'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "TipoRegistro:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['TipoRegistro'][0]['@textNode']);
        // Qui potrebbe servire per utilizzo conservazione versione precedente a 1.4? Chiave potrebbe dare errore se non valorizzata?
        $this->datiMinimiEsitoVersamento[self::CHIAVE_ESITO_IDVERSAMENTO] .= utf8_decode($arrayXml['IdSIP'][0]['@textNode']);
        return true;
    }

    public function SalvaDatiConservazione() {
        // Provvisorio $model.
        $model = new itaModel();

        $xmlRichiesta = $this->xmlRichiesta;
        $xmlResp = $this->xmlResponso;
        // Portando i dati su "BaseDati" si potrebbe evitare la rilettura.
        //$Anapro_rec = $this->proLib->GetAnapro($ProNum, 'codice', $ProPar);
        $Anapro_rec = $this->baseDati[proConservazioneManagerHelper::K_ANAPRO_REC];

        $subPath = "proConservazione-work-" . md5(microtime());
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
        $errSalva = false;
        $randName = md5(rand() * time()) . ".xml";
        $DestinoXml = $tempPath . "/" . $randName;
        if (file_put_contents($DestinoXml, $xmlResp)) {
            $datetime_esito = date('Ymd_His');
            if ($this->parametri['NOMEFILEESITO']) {//!!
                $NomeFile = $FileInfo = $param['NOMEFILEESITO'];
            } else {
                $NomeFile = $FileInfo = "ESITO_CONSERVAZIONE_{$Anapro_rec['PRONUM']}_{$Anapro_rec['PROPAR']}_{$datetime_esito}.xml";
            }
            $this->LastNomeFileResponso = $NomeFile;
            $AllegatoDiServizio[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $DestinoXml,
                'FILENAME' => $randName,
                'FILEINFO' => $FileInfo,
                'DOCTIPO' => 'ALLEGATO',
                'DAMAIL' => '',
                'DOCNAME' => $NomeFile,
                'DOCIDMAIL' => '',
                'DOCFDT' => date('Ymd'),
                'DOCRELEASE' => '1',
                'DOCSERVIZIO' => 1,
            );
            /*
             * Salvataggio XML risultato:
             * 
             */
            $risultato = $this->proLibAllegati->GestioneAllegati($model, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AllegatoDiServizio, $Anapro_rec['PROCON'], $Anapro_rec['PRONOM']);
            if (!$risultato) {
                $this->errCode = self::ERR_CODE_WARNING;
                $this->errMessage = $this->proLibAllegati->getErrMessage();
                $errSalva = true;
            }
        } else {
            $this->errCode = self::ERR_CODE_WARNING;
            $this->errMessage = "Errore in salvataggio contenuto file xml.";
            $errSalva = true;
        }
        return true;
    }

    public function getRDV($UUID) {
        if ($this->parametriManager['DIGIP_RDV']) {
            $IFrameSrc = $this->parametriManager['DIGIP_RDV'] . "?UUID=" . $UUID;
            Out::openDocument($IFrameSrc);
        } else {
            Out::msginfo('Informazione', "Indirizzo RDV non definito nei parametri.");
        }
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

    public function setRDVFileFromUUID($UUID) {

        if ($this->RDVFile && file_exists($this->RDVFile)) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "RDV file già scaricato";
            return false;
        }

        $subPath = uniqid("work-rdv-");
        $path = itaLib::createAppsTempPath($subPath);
        $RVDArray = $this->getRDVArray($UUID);
        if (!$RVDArray) {
            return false;
        }

        $RDVHeaders = array_change_key_case($RVDArray['headers'][0], CASE_LOWER);
        $RDVBody = $RVDArray['body'];

//        list($disposition_type, $disposition_param) = explode(';', $RDVHeaders['content-disposition']);
//        list($key, $filename) = explode('=', trim(strtolower($disposition_param)));

        $filename = "rdv_$UUID.xml.p7m";


        if (file_put_contents($path . '/' . $filename, $RDVBody) === false) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Errore in salataggio RDV file: " . print_r(error_get_last(), true);
            return false;
        }
        $this->RDVFile = $path . '/' . $filename;
        return true;
    }

    public function getRDVArray($UUID) {
        if ($this->parametriManager['DIGIP_RDV']) {
            include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
            $restClient = new itaRestClient();
            $restClient->setDebugLevel(true);
            $restClient->setTimeout(10); //!!
            $restClient->setCurlopt_url($this->parametriManager['DIGIP_RDV']);
            $restClient->setCurlopt_header(true);
            $data['UUID'] = $UUID;
            $headers = array();
            $headers[] = 'Authorization: Basic ' . base64_encode($this->parametriManager['DIGIP_USERID'] . ':' . $this->parametriManager['DIGIP_PASSWORD']);
            if (!$restClient->get('', $data, $headers)) {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = $restClient->getErrMessage();
                return false;
            }

            if ($restClient->getHttpStatus() != 200) {
                $this->errCode = self::ERR_CODE_FATAL;
                $this->errMessage = "Response http Status: " . $restClient->getHttpStatus() . '  ' . $restClient->getErrMessage();
                return false;
            }

            $retRdv = array();
            $retRdv['body'] = $restClient->getResult();
            $retRdv['headers'] = $restClient->getHeaders();
            return $retRdv;
        } else {
            $this->errCode = self::ERR_CODE_INFO;
            $this->errMessage = 'Indirizzo RDV non definito nei parametri.';
            return false;
        }
    }

    public function parseXmlRDV($UUID, $PendingUUID = '') {
        if (!$UUID) {
            $this->errCode = self::ERR_CODE_FATAL;
            $this->errMessage = "Codice Versamento UUID non definito.";
            return false;
        }
        $this->logger->info('Inizio Controllo RDV. Prot: ' . $this->anapro_rec['PRONUM'] . $this->anapro_rec['PROPAR']);
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
        if (isset($arrayXml['RapportoDiVersamento'])) {
            $arrayXml = $arrayXml['RapportoDiVersamento'];
        }
        $this->RDVArrayXml = $arrayXml;
        /*
         * Salvataggio XML RDV
         */
        $this->logger->info('Salvataggio file XML RDV.');
        if (!$this->SalvataggioXmlRDV()) {
            return false;
        }
        /*
         * Salvataggio Metadati
         */
        $this->logger->info('Salvataggio Metadati RDV.');
        if (!$this->SalvataggioMetadatiRDV()) {
            return false;
        }
        $this->logger->info('Controllo RDV Terminato.');
        return true;
    }

    public function SalvataggioXmlRDV() {
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
            $CodiceEsito = 'NEGATIVO';
            if ($this->RDVArrayXml['SIP'][0]['CodiceEsitoRegole'][0]['@textNode'] == '000') {
                $CodiceEsito = 'POSITIVO';
            }
            $Proconser_rec['ESITOCONSERVAZIONE'] = $CodiceEsito;
            $Proconser_rec['CODICEESITOCONSERVAZIONE'] = $this->RDVArrayXml['SIP'][0]['CodiceEsitoRegole'][0]['@textNode'];
            $Proconser_rec['MESSAGGIOCONSERVAZIONE'] = $this->RDVArrayXml['SIP'][0]['DescrizioneEsitoRegole'][0]['@textNode'];

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

    public static function getBaseDatiSecificiSUAPDocumento(&$BaseDati) {
        $proLibTabDag = new proLibTabDag();
        $proLibFascicolo = new proLibFascicolo();
        $FonteDati = praLibFascicoloArch::FONTE_DATI_DOCUMENTO;
        $FonteDatiPrAmm = praLibFascicoloArch::FONTE_DATI_PRATICAAMM;
        $TDClasse = 'ANAPRO';
        $Anapro_rec = $BaseDati[proConservazioneManagerHelper::K_ANAPRO_REC];
        /*
         *  Possono esserci piu progressivi?
         */
        $TabDag_tab = $proLibTabDag->GetTabdag($TDClasse, 'codice', $Anapro_rec['ROWID'], '', 0, true, '', $FonteDati);
        $DatiTabdag = array();
        foreach ($TabDag_tab as $Tabdag_rec) {
            $Chiave = $Tabdag_rec['TDAGCHIAVE'];
            $Valore = $Tabdag_rec['TDAGVAL'];
            $DatiTabdag[$Chiave] = $Valore;
        }
        /*
         * Estrazione Tabdag pratica
         */
        $AnaproF_rec = $proLibFascicolo->getAnaproFascicolo($Anapro_rec['PROFASKEY']);
        $TabDag_tab = $proLibTabDag->GetTabdag($TDClasse, 'codice', $AnaproF_rec['ROWID'], '', 0, true, '', $FonteDatiPrAmm);
        $DatiTabdag_Pratica = array();
        foreach ($TabDag_tab as $Tabdag_rec) {
            $Chiave = $Tabdag_rec['TDAGCHIAVE'];
            $Valore = $Tabdag_rec['TDAGVAL'];
            $DatiTabdag_Pratica[$Chiave] = $Valore;
        }
        //
        $DatiSpecifici = array();
        $DatiSpecifici[self::K_DSPE_SEGNATURA] = $DatiTabdag[praLibFascicoloArch::K_SEGNATURA];
        $DataReg = $DatiTabdag[praLibFascicoloArch::K_DATAREGISTRAZIONE] ? date("Y-m-d", strtotime($DatiTabdag[praLibFascicoloArch::K_DATAREGISTRAZIONE])) : '';
        $DatiSpecifici[self::K_DSPE_DATAREGISTRAZIONE] = $DataReg;
        $DatiSpecifici[self::K_DSPE_OGGETTO] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_OGGETTO]);
        $DatiSpecifici[self::K_DSPE_MITTENTE_NOME] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_MITTENTE_NOME]);
        $DatiSpecifici[self::K_DSPE_MITTENTE_COGNOME] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_MITTENTE_COGNOME]);
        $DatiSpecifici[self::K_DSPE_MITTENTE_DENOMINAZIONE] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_MITTENTE_DENOMINAZIONE]);
        $DatiSpecifici[self::K_DSPE_MITTENTE_CODICEFISCALE] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_CODICEFISCALE];
        $DatiSpecifici[self::K_DSPE_MITTENTE_PARTITAIVA] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_PARTITAIVA];
        $DatiSpecifici[self::K_DSPE_MITTENTE_STATO] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_STATO];
        $DatiSpecifici[self::K_DSPE_MITTENTE_REGIONE] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_REGIONE];
        $DatiSpecifici[self::K_DSPE_MITTENTE_CAP] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_CAP];
        $DatiSpecifici[self::K_DSPE_MITTENTE_INDIRIZZO] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_INDIRIZZO];
        $DatiSpecifici[self::K_DSPE_MITTENTE_EMAIL] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_EMAIL];
        $DatiSpecifici[self::K_DSPE_MITTENTE_NUMCIVICO] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_NUMCIVICO];
        $DataRic = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_DATARICEZIONE] ? date("Y-m-d", strtotime($DatiTabdag[praLibFascicoloArch::K_MITTENTE_DATARICEZIONE])) : '';
        $DatiSpecifici[self::K_DSPE_MITTENTE_DATARICEZIONE] = $DataRic;
        $DatiSpecifici[self::K_DSPE_MITTENTE_MEZZORICEZIONE] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_MEZZORICEZIONE];
        $DatiSpecifici[self::K_DSPE_MITTENTE_SEGNATURAPROTOCOLLO] = $DatiTabdag[praLibFascicoloArch::K_MITTENTE_SEGNATURAPROTOCOLLO];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_CODICEIPA] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_CODICEIPA];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_DENOMINAZIONE] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_SOGGPROD_DENOMINAZIONE]);
        $DatiSpecifici[self::K_DSPE_SOGGPROD_TIPOSOGGETTO] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_TIPOSOGGETTO];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_CONDIZIONEGIURIDICA] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_CONDIZIONEGIURIDICA];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_STATO] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_STATO];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_REGIONE] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_REGIONE];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_COMUNE] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_COMUNE];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_CAP] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_CAP];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_INDIRIZZO] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_INDIRIZZO];
        $DatiSpecifici[self::K_DSPE_SOGGPROD_NUMCIV] = $DatiTabdag[praLibFascicoloArch::K_SOGGPROD_NUMCIV];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_NOME] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_NOME]);
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_COGNOME] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_COGNOME]);
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_DENOMINAZIONE] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_DENOMINAZIONE]);
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_CODICEFISCALE] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_CODICEFISCALE];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_PARTITAIVA] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_PARTITAIVA];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_STATO] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_STATO];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_REGIONE] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_REGIONE];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_COMUNE] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_COMUNE];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_CAP] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_CAP];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_INDIRIZZO] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_INDIRIZZO];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_EMAIL] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_EMAIL];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_NUMCIV] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_NUMCIV];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_RICEVUTEPEC_ACC] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_RICEVUTEPEC_ACC];
        $DatiSpecifici[self::K_DSPE_DESTINATARIO_RICEVUTEPEC_CONS] = $DatiTabdag[praLibFascicoloArch::K_DESTINATARIO_RICEVUTEPEC_CONS];
        $DatiSpecifici[self::K_DSPE_LUOGODOCUMENTO] = $DatiTabdag[praLibFascicoloArch::K_LUOGODOCUMENTO];
        $DatiSpecifici[self::K_DSPE_TIPODOCUMENTO_CODIDENTIFICATIVO] = $DatiTabdag[praLibFascicoloArch::K_TIPODOCUMENTO_CODIDENTIFICATIVO];
        $DatiSpecifici[self::K_DSPE_TIPODOCUMENTO_DENOMINAZIONE] = $DatiTabdag[praLibFascicoloArch::K_TIPODOCUMENTO_DENOMINAZIONE];
        $DatiSpecifici[self::K_DSPE_TIPODOCUMENTO_NATURA] = $DatiTabdag[praLibFascicoloArch::K_TIPODOCUMENTO_NATURA];
        $DatiSpecifici[self::K_DSPE_TIPODOCUMENTO_ACCESSIBILITA] = $DatiTabdag[praLibFascicoloArch::K_TIPODOCUMENTO_ACCESSIBILITA];
        $DataDoc = $DatiTabdag[praLibFascicoloArch::K_DATADOCUMENTO] ? date("Y-m-d", strtotime($DatiTabdag[praLibFascicoloArch::K_DATADOCUMENTO])) : '';
        $DatiSpecifici[self::K_DSPE_DATADOCUMENTO] = $DataDoc;
        $DatiSpecifici[self::K_DSPE_IDENTIFICATIVOPROCEDIMENTO] = $DatiTabdag[praLibFascicoloArch::K_IDENTIFICATIVOPROCEDIMENTO];
        $DatiSpecifici[self::K_DSPE_TEMPOMINIMOCONSERVAZIONE] = 0;
        // Dati Pratica Amministrativa 
        $DatiSpecifici[self::K_DSPE_ANNOAPERTURA] = $DatiTabdag_Pratica[praLibFascicoloArch::K_ANNOAPERTURA];
        $DatiSpecifici[self::K_DSPE_ANNOCHIUSURA] = $DatiTabdag_Pratica[praLibFascicoloArch::K_ANNOCHIUSURA];
        $DatiSpecifici[self::K_DSPE_PROCAMM_CODICEPROCEDIMENTO] = $DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_CODICEPROCEDIMENTO];
        $DatiSpecifici[self::K_DSPE_PROCAMM_DESCRIZIONE] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_DESCRIZIONE]);
        $DatiSpecifici[self::K_DSPE_PROCAMM_RIFERIMENTINORMATIVI] = $DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_RIFERIMENTINORMATIVI];
        $DatiSpecifici[self::K_DSPE_PROCAMM_DESTINATARI] = proConservazioneManagerHelper::proteggiCarattteri($DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_DESTINATARI]);
        $DatiSpecifici[self::K_DSPE_PROCAMM_REGIMIABILITATIVI] = $DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_REGIMIABILITATIVI];
        $DatiSpecifici[self::K_DSPE_PROCAMM_TIPOLOGIA] = $DatiTabdag_Pratica[praLibFascicoloArch::K_PROCAMM_TIPOLOGIA];
        $DatiSpecifici[self::K_DSPE_PROCAMM_SETTOREATTIVITA] = $DatiTabdag_Pratica[praLibFascicoloArch::K_MITTENTE_CODICEFISCALE];
        // Protocollo
        $DatiSpecifici[self::K_DSPE_PROTOCOLLO] = $DatiTabdag[praLibFascicoloArch::K_PROTOCOLLO];
        $DatiSpecifici[self::K_DSPE_TIPOPROTOCOLLO] = $DatiTabdag[praLibFascicoloArch::K_TIPOPROTOCOLLO];
        $DataProt = $DatiTabdag[praLibFascicoloArch::K_DATAPROTOCOLLO] ? date("Y-m-d", strtotime($DatiTabdag[praLibFascicoloArch::K_DATAPROTOCOLLO])) : '';
        $DatiSpecifici[self::K_DSPE_DATAPROTOCOLLO] = $DataProt;
        $DatiSpecifici[self::K_DSPE_ALTRIMITTDEST] = $DatiTabdag[praLibFascicoloArch::K_ALTRIMITTDEST];
        //
        $DatiSpecifici[self::K_DSPE_CHIAVE_PASSO] = $DatiTabdag[praLibFascicoloArch::K_ALTRIMITTDEST];
        $DatiSpecifici[self::K_DSPE_NUMEROPASSO] = $DatiTabdag[praLibFascicoloArch::K_NUMEROPASSO];
        $DatiSpecifici[self::K_DSPE_SERIEARCCODICE] = $DatiTabdag[praLibFascicoloArch::K_SERIEARCCODICE];
        $DatiSpecifici[self::K_DSPE_SERIEARCSIGLA] = $DatiTabdag[praLibFascicoloArch::K_SERIEARCSIGLA];
        $DatiSpecifici[self::K_DSPE_SERIEARCPROGRESSIVO] = $DatiTabdag[praLibFascicoloArch::K_SERIEARCPROGRESSIVO];
        $DatiSpecifici[self::K_DSPE_SERIEANNO] = $DatiTabdag[praLibFascicoloArch::K_SERIEANNO];

        $BaseDati[self::K_DSPE] = $DatiSpecifici;
        return true;
    }

}
