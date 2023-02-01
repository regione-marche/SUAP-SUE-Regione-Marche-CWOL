<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Helper Conservazione
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    15.11.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
//include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';

class proConservazioneManagerHelper {
    /*
     * Codici tipi conservazione
     * 
     */

    const MANAGER_DIGIP = 'DIGIP_MARCHE';
    const MANAGER_ASMEZDOC = 'ASMEZDOC';
    const MANAGER_NAMIRIAL = 'NAMIRIAL';
    /*
     *  Classi
     */
    const CLASS_DIGIP = 'proConservazioneManagerDigiP';
    const CLASS_ASMEZDOC = 'proConservazioneManagerAsmezDoc';
    const CLASS_NAMIRIAL = 'proConservazioneManagerNamirial';

    /*
     *  Dati Protocollo/Documentale
     */
    const K_ANAPRO_REC = 'ANAPRO_REC';
    const K_ANADOC_TAB = 'ANADOC_TAB';
    const K_MAIL_ARCHIVIO_TAB = 'MAIL_ARCHIVIO_TAB';
    const K_ANADOC_KEY_PRINCIPALE = 'ANADOC_KEY_PRINCIPALE';
    const K_TIPOREGISTRO = 'TIPOREGISTRO';
    const K_NUMERO = 'NUMERO';
    const K_ANNO = 'ANNO';
    const K_TIPOPROT = 'TIPOPROT';
    const K_OGGETTO = 'OGGETTO';
    const K_DATA = 'DATA';
    const K_NUMEROALLEGATI = 'NUMEROALLEGATI';
    const K_PROTIPODOC = 'PROTIPODOC';
    const K_CLASSIFICAZIONE = 'CLASSIFICAZIONE';
    const K_FASCICOLOPRINCIPALE = 'FASCICOLO_PRINCIPALE';
    const K_FASCICOLI = 'FASCICOLI';
    const K_FASCICOLO_CODICE = 'FASCICOLO_CODICE';
    const K_FASCICOLO_OGGETTO = 'FASCICOLO_OGGETTO';
    const K_SOTTFAS_CODICE = 'SOTTOFASCICOLO_CODICE ';
    const K_SOTTFAS_OGGETTO = 'SOTTOFASCICOLO_OGGETTO';
    const K_MITTENTEDESTINATARI = 'MITTENTEDESTINATARI';
    const K_DATAANNULLAMENTO = 'DATAANNULLAMENTO';
    const K_MOTIVOANNULLAMENTO = 'MOTIVOANNULLAMENTO';

    /* Keys Documento Principale: Registro */
    const K_DATACHIUSURA = 'DATACHIUSURA';
    const K_SOGGETTOPRODUTTORE = 'SOGGETTOPRODUTTORE';
    const K_RESPONSABILE = 'RESPONSABILE';
    const K_NUMEROPRIMAREGISTRAZIONE = 'NUMEROPRIMAREGISTRAZIONE';
    const K_NUMEROULTIMAREGISTRAZIONE = 'NUMEROULTIMAREGISTRAZIONE';
    const K_DATAPRIMAREGISTRAZIONE = 'DATAPRIMAREGISTRAZIONE';
    const K_DATAULTIMAREGISTRAZIONE = 'DATAULTIMAREGISTRAZIONE';
    const K_NUMEROPROTOCOLLI = 'NUMEROPROTOCOLLI';
    const K_CFRESPONSABILE = 'CFRESPONSABILE';

    /*
     * Chiavi Unità Documentarie:
     */
    const K_UNIT_REGISTRO = 'REGISTRO';
    const K_UNIT_PROT = 'PROTOCOLLO';
    const K_UNIT_PROT_INTERNO = 'PROTOCOLLO_INTERNO';
    const K_UNIT_PROT_AGG = 'PROTOCOLLO_AGGIORNATO';
    const K_UNIT_PROT_INTERNO_AGG = 'PROTOCOLLO_INTERNO_AGGIORNATO';
    const K_UNIT_PROT_ANN = 'PROTOCOLLO_ANNULLATO';
    const K_UNIT_PROT_INTERNO_ANN = 'PROTOCOLLO_INTERNO_ANNULLATO';
    const K_UNIT_SUAP_DOCUMENTO = 'SUAP_DOCUMENTO';
    const K_UNIT_SUAP_DOCUMENTO_AGG = 'SUAP_DOCUMENTO_AGGIORNATO'; //Verificare se serve.

    /*
     * Error Code
     */
    const ERR_CODE_SUCCESS = 0;
    const ERR_CODE_FATAL = -1;
    const ERR_CODE_QUESTION = -2;
    const ERR_CODE_INFO = -3;
    const ERR_CODE_WARNING = -4;

    private static $lastErrorCode;
    private static $lastErrorMessage;
    private static $allegatiNonConservabili = array();

    static function getLastErrorCode() {
        return self::$lastErrorCode;
    }

    static function getLastErrorMessage() {
        return self::$lastErrorMessage;
    }

    static function setLastErrorCode($lastErrorCode) {
        self::$lastErrorCode = $lastErrorCode;
    }

    static function setLastErrorMessage($lastErrorMessage) {
        self::$lastErrorMessage = $lastErrorMessage;
    }

    static function clearLastError() {
        self::$lastErrorMessage = null;
        self::$lastErrorCode = null;
    }

    public static function getAllegatiNonConservabili() {
        return self::$allegatiNonConservabili;
    }

    public static function setAllegatiNonConservabili($allegatiNonConservabili) {
        self::$allegatiNonConservabili = $allegatiNonConservabili;
    }

    /**
     * Legge i parametri di protocollo per la conservazione
     * 
     * @return array
     */
    public static function getParametriConservazione() {
        $proLib = new proLib();

        $anaent_41 = $proLib->GetAnaent('41');
        $anaent_42 = $proLib->GetAnaent('42');
        $anaent_53 = $proLib->GetAnaent('53');
        $anaent_58 = $proLib->GetAnaent('58');
        $parametri = array();
        $parametri['TIPOCONSERVAZIONE'] = $anaent_42['ENTDE6'];
        $parametri['DIFFERENZAGIORNI'] = $anaent_53['ENTDE1'];
        $parametri['DATALIMITE'] = $anaent_53['ENTDE2'];
        $parametri['TIPODOCREGISTRO'] = $anaent_41['ENTVAL'];
        $parametri['FILEPATHLOGGER'] = $anaent_53['ENTDE5'];
        $parametri['CFENTE'] = $anaent_58['ENTDE2'];

        return $parametri;
    }

    /**
     * Estrae un dizionario per la conservazione del protocollo indicato
     * @param type $rowidAnapro
     * @param type $dataConservazione data per il il reperimento dei dati alla data di versione del protocollo sepcificata
     * @return array()
     */
    static public function getBaseDatiUnitaDocumentarie($rowidAnapro, $dataConservazione = null, $oraConservazione = null) {
        self::clearLastError();
        if (!$rowidAnapro) {
            self::setLastErrorCode(-1);
            self::setLastErrorMessage('Identificativo protocollo non indicato.');
            return false;
        }
        if ($dataConservazione === null) {
            $dataConservazione = date("Ymd");
        }

        if ($oraConservazione === null) {
            $oraConservazione = '24:00:00';
        }

        /*
         * Istanzio le librerie necessarie
         */
        $proLib = new proLib();
        $proLibVariazioni = new proLibVariazioni();


        $baseDati = array();
        /*
         * Ricavo anapro_rec corrente:
         */
        $Anapro_current_rec = $proLib->GetAnapro($rowidAnapro, 'rowid');
        if (!$Anapro_current_rec) {
            self::setLastErrorCode(-1);
            self::setLastErrorMessage('Impossibile leggere il protocollo indicato.');
            return false;
        }

        /*
         * Controllo se sono intercorse variazioni dopo la conservazione
         */
        if ($Anapro_current_rec['PRORDA'] > $dataConservazione || ($Anapro_current_rec['PRORDA'] >= $dataConservazione && $Anapro_current_rec['PROROR'] > $oraConservazione)) {
            /*
             * protocollo alla variazione subito antecedente al versamento
             * cerco la variazione giusta (data e ora)
             */
            $Anapro_rec_save = $proLibVariazioni->getLastAnaproSave($Anapro_current_rec['PRONUM'], $Anapro_current_rec['PROPAR'], $dataConservazione, $oraConservazione);
            if (!$Anapro_rec_save) {
                self::setLastErrorCode(-1);
                self::setLastErrorMessage('Salvataggio del protocollo non trovato.');
                return false;
            }
            $Anaogg_rec = $proLibVariazioni->GetAnaogg($Anapro_rec_save['PRONUM'], $Anapro_rec_save['PROPAR'], $Anapro_rec_save['SAVEDATA'], $Anapro_rec_save['SAVEORA']);

            /*
             * Carico Allegati:
             */
            //$Allegati = self::getBaseDatiAllegati($Anapro_rec_save, $Anapro_rec_save['SAVEDATA'], $Anapro_rec_save['SAVEORA']);
            /*
             * Classificazione e Fascicoli.
             *  Nessun errore in 
             */
            self::getBaseDatiFascicoli($baseDati, $Anapro_rec_save, $Anapro_rec_save['SAVEDATA'], $Anapro_rec_save['SAVEORA']);
            $MittentiDestinatari = self::EstraiMittentiDestinatari($Anapro_rec_save, $Anapro_rec_save['SAVEDATA'], $Anapro_rec_save['SAVEORA']);
        } else {
            /*
             * Protocollo attuale
             */
            $Anapro_rec = $Anapro_current_rec;
            $Anaogg_rec = $proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            $Allegati = self::getBaseDatiAllegati($Anapro_rec);
            /*
             * Classificazione e Fascicoli.
             *  Nessun errore in 
             */
            self::getBaseDatiFascicoli($baseDati, $Anapro_rec);
            $MittentiDestinatari = self::EstraiMittentiDestinatari($Anapro_current_rec);
        }

        /*
         * Predisposizione Variabili Standard Unità documnetaria
         * Estrazione Dati Principali:
         */
        $Anapro_rec = $Anapro_current_rec;
        $Numero = intval(substr($Anapro_rec['PRONUM'], 4));
        $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $Data = date("Y-m-d", strtotime($Anapro_rec['PRODAR']));
        $Oggetto = $Anaogg_rec['OGGOGG'];

        $Classificazione = '';
        switch (substr($Anapro_rec['PROPAR'], 0, 1)) {
            case 'C':
                $TipoRegistro = $proLib->GetCodiceRegistroDocFormali();
                break;
            case 'A':
            case 'P':
                $TipoRegistro = $proLib->GetCodiceRegistroProtocollo();
                break;
            case 'I':
                $TipoRegistro = $proLib->GetCodiceRegistroSuapDocumento();
                break;
        }
        $baseDati[self::K_ANAPRO_REC] = $Anapro_rec;
        $baseDati[self::K_TIPOREGISTRO] = $TipoRegistro;
        $baseDati[self::K_NUMERO] = $Numero;
        $baseDati[self::K_ANNO] = $Anno;
        $baseDati[self::K_TIPOPROT] = $Anapro_rec['PROPAR'];
        $baseDati[self::K_OGGETTO] = self::proteggiCarattteri($Oggetto);
        $baseDati[self::K_DATA] = $Data;
        $baseDati[self::K_CLASSIFICAZIONE] = $Classificazione;
        $baseDati[self::K_PROTIPODOC] = $Anapro_rec['PROCODTIPODOC'];

        // Qui estrazione mittente/destinatari.
        $baseDati[self::K_MITTENTEDESTINATARI] = $MittentiDestinatari;

        /*
         * Protocollo Annullato:
         */
        // K_DATAANNULLAMENTO
        $baseDati[self::K_DATAANNULLAMENTO] = '';
        $baseDati[self::K_MOTIVOANNULLAMENTO] = '';
        if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
            $AnaproSave_rec = $proLib->GetLastAnaproSave($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            $DataAnn = date("Y-m-d", strtotime($AnaproSave_rec['SAVEDATA']));
            $baseDati[self::K_MOTIVOANNULLAMENTO] = $Anapro_rec['PROANNMOTIVO'];
            $baseDati[self::K_DATAANNULLAMENTO] = $DataAnn;
        }


        $baseDati[self::K_ANADOC_TAB] = $Allegati['ANADOC_TAB'];
        $baseDati[self::K_MAIL_ARCHIVIO_TAB] = $Allegati['MAIL_ARCHIVIO_TAB'];
        $baseDati[self::K_ANADOC_KEY_PRINCIPALE] = $Allegati['KEY_PRINCIPALE'];
        if (!$Allegati['ANADOC_TAB']) {
            $baseDati[self::K_NUMEROALLEGATI] = 0;
        } else {
            $baseDati[self::K_NUMEROALLEGATI] = count($Allegati['ANADOC_TAB']) - 1;
        }


        return $baseDati;
    }

    public static function getBaseDatiRegistroProtocollo(&$baseDati) {
        $Anapro_rec = $baseDati[self::K_ANAPRO_REC];
        /*
         * Dati aggiuntivi registro giornaliero??!! 
         *    !!! POTREBBE SPOSTARSI SU DOC PRINCIPALE !!!
         * DatiSpecifici
         */
        $campoDataRegistro = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_REGISTRO];
        $campoDataIniziale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoDataFinale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoPrimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_INIZIALE];
        $campoUltimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_FINALE];
        $campoSoggettoProduttore = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_SOGGETTO_PRODUTTORE];
        $campoSoggettoResponsabile = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_RESPONSABILE];
        $campoCFSoggettoResponsabile = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_CF_RESPONSABILE];
        //Aggiuntivi:
        //$campoDatafirma = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_FIRMA];
        //$campoCodicefiscaleresponsabile = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CFRESPONSABILE];
        $campoNumerodocumentiregistrati = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_DOCUMENTI_REGISTRATI];
        // $Codicefiscalesoggettoproduttore = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_CF_SOGGETTO_PRODUTTORE];

        /*
         * Lettura Metadati
         * Metadati Specifici Registri protocollo
         */
        $proLibGiornaliero = new proLibGiornaliero();
        $metaDati = $proLibGiornaliero->getAnaproAndMetadati($Anapro_rec['ROWID']);
        if (!$metaDati) {
            self::setLastErrorCode(self::ERR_CODE_FATAL);
            self::setLastErrorCode('Metadati del registro giornaliero non trovati.');
            return false;
        }
        $Data = date("Y-m-d", strtotime($Anapro_rec['PRODAR']));

        $DataIniziale = date("Y-m-d", strtotime($metaDati[$campoDataRegistro]));
        $DataFinale = date("Y-m-d", strtotime($metaDati[$campoDataRegistro]));
        if ($metaDati[$campoDataIniziale]) {
            $DataIniziale = date("Y-m-d", strtotime($metaDati[$campoDataIniziale]));
        }
        if ($metaDati[$campoDataFinale]) {
            $DataFinale = date("Y-m-d", strtotime($metaDati[$campoDataFinale]));
        }
        $PrimoNumero = 0;
        $UltimoNumero = 0;
        if ($metaDati[$campoPrimoNumero]) {
            $PrimoNumero = $metaDati[$campoPrimoNumero];
        }
        if ($metaDati[$campoUltimoNumero]) {
            $UltimoNumero = $metaDati[$campoUltimoNumero];
        }
        $SoggettoProduttore = $metaDati[$campoSoggettoProduttore];
        $SoggettoResponsabile = $metaDati[$campoSoggettoResponsabile];
        $CFSoggettoResponsabile = $metaDati[$campoCFSoggettoResponsabile];
        // Dati specifici
        $baseDati[self::K_DATACHIUSURA] = $Data;
        $baseDati[self::K_SOGGETTOPRODUTTORE] = $SoggettoProduttore;
        $baseDati[self::K_RESPONSABILE] = $SoggettoResponsabile;
        $baseDati[self::K_CFRESPONSABILE] = $CFSoggettoResponsabile;
        $baseDati[self::K_NUMEROPRIMAREGISTRAZIONE] = $PrimoNumero;
        $baseDati[self::K_NUMEROULTIMAREGISTRAZIONE] = $UltimoNumero;
        $baseDati[self::K_DATAPRIMAREGISTRAZIONE] = $DataIniziale;
        $baseDati[self::K_DATAULTIMAREGISTRAZIONE] = $DataFinale;
        //Metadati Aggiuntivi.
        $baseDati[self::K_NUMEROPROTOCOLLI] = $campoNumerodocumentiregistrati;
        return true;
    }

    private static function getBaseDatiAllegati($Anapro_rec, $dataVariazione = '', $oraVariazione = '') {
        /*
         * Estraggo i documenti
         */
        $Anadoc_tab = self::GetAnadocDaConservare($Anapro_rec, $dataVariazione, $oraVariazione);
        if ($Anadoc_tab === false) {
            if ($this->errCode == -1) {
                return false;
            }
        }

        /*
         * Elenco Hash: 
         * Controllo stesso file presente.
         */
        $HashAllegati = array();
        foreach ($Anadoc_tab as $key => $Anadoc_rec) {
            /* Controllo se hash già presente: */
            if (isset($HashAllegati[$Anadoc_rec['DOCSHA2']])) {
                $chiave = $HashAllegati[$Anadoc_rec['DOCSHA2']];
                $TipoCorrente = $Anadoc_rec['DOCTIPO'];
                $TipoPrec = $Anadoc_tab[$chiave]['DOCTIPO'];
                /* Tengo il principale : 
                 *  - Se è il corrente rimuovo il precedente.
                 *  - Se è il precedente rimuovo il corrente.
                 * Altrimenti tengo il primo allegato e rimuovo il corrente.
                 *  */
                if ($TipoCorrente == 'PRINCIPALE') {
                    unset($Anadoc_tab[$chiave]);
                } else if ($TipoPrec == 'PRINCIPALE') {
                    unset($Anadoc_tab[$key]);
                } else {
                    unset($Anadoc_tab[$key]);
                }
            }
            $HashAllegati[$Anadoc_rec['DOCSHA2']] = $key;
        }

        /* Controlli se Principale o Allegato */
        $key_principale = null;
        foreach ($Anadoc_tab as $key => $Anadoc_rec) {
            if ($Anadoc_rec['DOCTIPO'] == 'PRINCIPALE') {
                /* Principale */
                $key_principale = $key;
                break;
            }
        }

        /*
         * Estraggo Accettazione e Consegna:
         */
        $MailArchivio_tab = self::GetNotificheMailDaConservare($Anapro_rec);

        return array(
            "KEY_PRINCIPALE" => $key_principale,
            "ANADOC_TAB" => $Anadoc_tab,
            "MAIL_ARCHIVIO_TAB" => $MailArchivio_tab
        );
    }

    private static function getBaseDatiFascicoli(&$baseDati, $Anapro_rec, $dataVariazione = '', $oraVariazione = '') {
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();
        if ($dataVariazione) {
            $proLibVariazioni = new proLibVariazioni();
            $ElencoFascicoli = $proLibVariazioni->EstraiFascicoliProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], '', $dataVariazione, $oraVariazione);
        } else {
            $ElencoFascicoli = $proLibFascicolo->EstraiFascicoliProtocollo($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        }
        if ($Anapro_rec['PROCCF']) {
            $Classificazione = self::DecodClassifica($Anapro_rec['PROCCF']);
            $baseDati[self::K_FASCICOLOPRINCIPALE][self::K_CLASSIFICAZIONE] = $Classificazione;
        }
        foreach ($ElencoFascicoli as $Fascicolo) {
            $BaseFas = array();
            // Classificazione ORGCCF
            $Classificazione = self::DecodClassifica($Fascicolo['ORGCCF']);
            $BaseFas[self::K_CLASSIFICAZIONE] = $Classificazione;
            $BaseFas[self::K_FASCICOLO_CODICE] = $Fascicolo['ORGKEY'];
            $BaseFas[self::K_FASCICOLO_OGGETTO] = self::ProteggiCarattteri($Fascicolo['OGGOGG_FASCICOLO']);
            if ($Fascicolo['CODICE_SOTTOFAS']) {
                $BaseFas[self::K_SOTTFAS_CODICE] = $Fascicolo['CODICE_SOTTOFAS'];
                $BaseFas[self::K_SOTTFAS_OGGETTO] = self::ProteggiCarattteri($Fascicolo['OGGETTO_SOTTOFAS']);
            }
            /* Fascicolo principale */
            if ($Anapro_rec['PROFASKEY'] && $Fascicolo['ORGKEY'] == $Anapro_rec['PROFASKEY']) {
                $baseDati[self::K_FASCICOLOPRINCIPALE] = $BaseFas;
            } else {
                $baseDati[self::K_FASCICOLI][] = $BaseFas;
            }
        }

        return $baseDati;
    }

    /**
     * 
     * @param type $Anapro_rec
     * @return boolean|string
     */
    public static function GetAnadocDaConservare($Anapro_rec, $dataVariazione = '', $oraVariazione = '') {
        $proLib = new proLib();
        $proLibAllegati = new proLibAllegati();
        /*
         * Estratto i documenti
         * @TODO Criteri di estrazione.! [per ora non quelli di servizio, ma accettazioni e consegne dovrebbero.
         */
        $where = ' AND DOCSERVIZIO=0 ORDER BY DOCTIPO ASC,ROWID ASC';
        if ($dataVariazione) {
            $proLibVariazioni = new proLibVariazioni();
            $Anadoc_tab = $proLibVariazioni->GetAnadoc($Anapro_rec['PRONUM'], 'protocollo', true, $Anapro_rec['PROPAR'], $where, $dataVariazione, $oraVariazione);
        } else {
            $Anadoc_tab = $proLib->GetAnadoc($Anapro_rec['PRONUM'], 'protocollo', true, $Anapro_rec['PROPAR'], $where);
        }

        /*
         * Allegati e Doc principale:
         */
        $AnadocTab = array();
        $Principale = false;
        $EstensioniConservabili = $proLib->getEestensioniUtilizzabili();
        self::$allegatiNonConservabili = array();

        foreach ($Anadoc_tab as $Anadoc_rec) {
            // PREPARO ANADOC
            $hashVersato = $Anadoc_rec['DOCSHA2'];
            if (!$hashVersato) {
                self::setLastErrorCode(self::ERR_CODE_FATAL);
                self::setLastErrorMessage($proLibAllegati->getErrMessage());
                return false;
            }

            $Anadoc_rec['DOCNAME'] = self::ProteggiCarattteri($Anadoc_rec['DOCNAME']);
            /*
             *  Nuove Chiavi:
             */
            $Estensione = self::GetEstensione($Anadoc_rec['DOCNAME'], 1);
            $ArrExt = explode('.', $Estensione);
            $Estensione = $ArrExt[0];

            $Anadoc_rec['HASHFILE'] = $hashVersato;
            $Anadoc_rec['ESTENSIONE'] = $Estensione;
            /*
             * Escludo File Non contemplati:
             */
            $ExtKey = strtolower($Estensione);
            if ($EstensioniConservabili) {
                if (!$EstensioniConservabili[$ExtKey] || $EstensioniConservabili[$ExtKey]['EXTCONSER'] != 1) {
                    self::$allegatiNonConservabili[] = $Anadoc_rec['DOCNAME'];
                    continue;
                }
            }
            /*
             * Controllo p7m estensione:
             */
            if ($ExtKey == 'p7m') {
                $EstensioneString = self::GetEstensione(strtolower($Anadoc_rec['DOCNAME']), 10);
                $ArrExt = explode('.', $EstensioneString);
                $ExtLiv0 = $ArrExt[0];
                if ($EstensioniConservabili) {
                    if (!$EstensioniConservabili[$ExtLiv0] || $EstensioniConservabili[$ExtLiv0]['EXTCONSER'] != 1) {
                        self::$allegatiNonConservabili[] = $Anadoc_rec['DOCNAME'];
                        continue;
                    }
                }
            }

            /* Controlli se Principale o Allegato */
            if ($Anadoc_rec['DOCTIPO'] == '') {
                /* Controllo presenza più allegati principali: */
                if ($Principale == true) {
                    /* Principale già presente: il secondo è un Allegato. */
                    $Anadoc_rec['DOCTIPO'] = 'ALLEGATO';
                } else {
                    /* E' il Principale */
                    $Principale = true;
                    $Anadoc_rec['DOCTIPO'] = 'PRINCIPALE';
                }
            }
            /* Aggiungo A AnadocTab */
            $AnadocTab[] = $Anadoc_rec;
        }
        /* Controllo mancanza Doc Principale */
        if ($Principale == false) {
            if ($AnadocTab) {
                $AnadocTab[0]['DOCTIPO'] = 'PRINCIPALE';
            }
        }
        return $AnadocTab;
    }

    public static function GetElencoMailProt($Anapro_rec) {
        $proLib = new proLib();
        $MailArchivio_tab = array();

        switch ($Anapro_rec['PROPAR']) {
            case 'A':
                $MailArchivio_tab = $proLib->getMailArrivo($Anapro_rec);
                break;

            case 'P':
                /* Notifiche Anapro Principale */
                if ($Anapro_rec['PROIDMAILDEST']) {
                    $MailArchivio_tab = array_merge($MailArchivio_tab, $proLib->getNotifiche($Anapro_rec['PROIDMAILDEST']));
                }
                /* Notifiche Altri Destinatari */
                $AltriDestinatari = $proLib->caricaAltriDestinatari($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], false);
                foreach ($AltriDestinatari as $Destinatario) {
                    if ($Destinatario['DESIDMAIL']) {
                        $MailArchivio_tab = array_merge($MailArchivio_tab, $proLib->getNotifiche($Destinatario['DESIDMAIL']));
                    }
                }
                break;
        }
        return $MailArchivio_tab;
    }

    public static function GetNotificheMailDaConservare($Anapro_rec) {

        $MailArchivio_tab = self::GetElencoMailProt($Anapro_rec);
        /*
         * Elaborazione
         */
        foreach ($MailArchivio_tab as $key => $MailArchvio_rec) {
            $ext = self::GetEstensione($MailArchvio_rec['DATAFILE']);
            $pectipo = $MailArchvio_rec['PECTIPO'];
            if (!$MailArchvio_rec['PECTIPO']) {
                $pectipo = 'Email';
            }
            $NomeFile = $pectipo . '-' . $MailArchvio_rec['ROWID'] . '.' . $ext;
            $MailArchivio_tab[$key]['NOMEFILE'] = $NomeFile;
            $MailArchivio_tab[$key]['HASHFILE'] = hash_file('sha256', $MailArchvio_rec['DATASOURCE']);
            $MailArchivio_tab[$key]['ESTENSIONE'] = $ext;
            $MailArchivio_tab[$key]['DOCTIPO'] = 'ALLEGATO';
        }

        return $MailArchivio_tab;
    }

    public static function getRisorseAnadoc(&$baseDati) {
        $proLib = new proLib();
        $proLibAllegati = new proLibAllegati();

        $subPath = "proDocAllegati-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        if (!$tempPath) {
            self::setLastErrorCode(self::ERR_CODE_FATAL);
            self::setLastErrorMessage('Errore in creazione cartella temporanea.');
            return false;
        }

        foreach ($baseDati[self::K_ANADOC_TAB] as $key => $Anadoc_rec) {
            // Filepath:
            $filecontent = $proLibAllegati->CopiaDocAllegato($Anadoc_rec['ROWID'], $tempPath . '/' . $Anadoc_rec['DOCFIL']);
            if (!$filecontent) {
                self::setLastErrorCode(self::ERR_CODE_FATAL);
                self::setLastErrorMessage($proLibAllegati->getErrMessage());
                return false;
            }
            /*
             *  Nuove Chiavi:
             */
            $baseDati[self::K_ANADOC_TAB][$key]['FILEPATH'] = $filecontent;
        }


        return true;
    }

    public static function getRisorseMail(&$baseDati) {

        $subPath = "proMailAllegati-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        if (!$tempPath) {
            self::setLastErrorCode(self::ERR_CODE_FATAL);
            self::setLastErrorMessage('Errore in creazione cartella temporanea.');
            return false;
        }

        foreach ($baseDati[self::K_MAIL_ARCHIVIO_TAB] as $key => $Mail_rec) {
            $filecontent = $tempPath . '/' . $Mail_rec['DATAFILE'];
            if (!@copy($Mail_rec['DATASOURCE'], $filecontent)) {
                self::setLastErrorCode(self::ERR_CODE_FATAL);
                self::setLastErrorMessage("Copia Allegato. Errore durante la copia del file nell'ambiente temporaneo di lavoro.");
                return false;
            }
            /*
             *  Nuove Chiavi:
             */
            $baseDati[self::K_MAIL_ARCHIVIO_TAB][$key]['FILEPATH'] = $filecontent;
        }

        return true;
    }

    public static function proteggiCarattteri($Stringa) {
        return htmlspecialchars($Stringa, ENT_COMPAT, 'ISO-8859-1');
    }

    public static function GetEstensione($NomeFile, $LivCtr = 3) {
        $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
        if ($ext == 'p7m') {
            return self::GetEstensioneP7m($NomeFile, $LivCtr);
        }
        $Estensione = pathinfo($NomeFile, PATHINFO_EXTENSION);
        return $Estensione;
    }

    public static function GetEstensioneP7m($NomeFile, $LivCtr = 3) {
        $Ctr = 1;
        $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
        $Estensione = ".$ext";
        while ($ext === 'p7m') {
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            $ext = strtolower(pathinfo($NomeFile, PATHINFO_EXTENSION));
            $Estensione = '.' . $ext . $Estensione;
            if ($Ctr == $LivCtr) {
                break;
            }
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    /**
     * Funzione per decodifica classificazione protocollo.
     * @param type $Orgccf
     * @return string
     */
    public static function DecodClassifica($Orgccf) {
        $classificazione = '';
        if (strlen($Orgccf) === 4) {
            $classificazione = intval($Orgccf);
        } else if (strlen($Orgccf) === 8) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4));
        } else if (strlen($Orgccf) === 12) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4)) . '.' . intval(substr($Orgccf, 8, 4));
        }
        return $classificazione;
    }

    /**
     * 
     * @param type $Anapro_rec
     */
    public static function EstraiMittentiDestinatari($Anapro_rec, $saveData = '', $saveOra = '') {
        $MittentiDestinatari = '';
        $proLib = new proLib();
        $proLibVariazioni = new proLibVariazioni();
        if ($saveData || $saveOra) {

            /*
             * SAVE!
             * Mittente/Dest Principale:
             */
            $StrMitDestPrinc = $Anapro_rec['PRONOM'] . ' ' . $Anapro_rec['PROIND'] . ' ' . $Anapro_rec['PROCAP'] . ' ' . $Anapro_rec['PROCIT'] . ' ' . $Anapro_rec['PROORI'] . ' ' . $Anapro_rec['PROMAIL'];
            $MittentiDestinatari = str_replace(';', '.', $StrMitDestPrinc) . '; ';
            /*
             * Altri Destinatari:
             */
            $proLib = new proLib();
            $AltriDestinatari = $proLibVariazioni->caricaAltriDestinatari($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], false, $saveData, $saveOra); // OK ORDINATO.
            foreach ($AltriDestinatari as $Destinatario) {
                $StrDest = $Destinatario['DESNOM'] . ' ' . $Destinatario['DESIND'] . ' ' . $Destinatario['DESCAP'] . ' ' . $Destinatario['DESCIT'] . ' ' . $Destinatario['DESPRO'] . ' ' . $Destinatario['DESMAIL'];
                $MittentiDestinatari .= str_replace(';', '.', $StrDest) . '; ';
            }

            /*
             * Altri Mittenti:
             */
            $MittentiAggiuntivi = $proLibVariazioni->getPromitagg($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR'], $saveData, $saveOra); // OK ORDINATO.
            foreach ($MittentiAggiuntivi as $Mittente) {
                $StrMitt = $Mittente['PRONOM'] . ' ' . $Mittente['PROIND'] . ' ' . $Mittente['PROCAP'] . ' ' . $Mittente['PROCIT'] . ' ' . $Mittente['PROORI'] . ' ' . $Mittente['PROMAIL'];
                $MittentiDestinatari .= str_replace(';', '.', $StrMitt) . '; ';
            }
        } else {
            /*
             * Mittente/Dest Principale:
             */
            $StrMitDestPrinc = $Anapro_rec['PRONOM'] . ' ' . $Anapro_rec['PROIND'] . ' ' . $Anapro_rec['PROCAP'] . ' ' . $Anapro_rec['PROCIT'] . ' ' . $Anapro_rec['PROORI'] . ' ' . $Anapro_rec['PROMAIL'];
            $MittentiDestinatari = str_replace(';', '.', $StrMitDestPrinc) . '; ';
            /*
             * Altri Destinatari:
             */
            $proLib = new proLib();
            $AltriDestinatari = $proLib->caricaAltriDestinatari($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], false); // OK ORDINATO.
            foreach ($AltriDestinatari as $Destinatario) {
                $StrDest = $Destinatario['DESNOM'] . ' ' . $Destinatario['DESIND'] . ' ' . $Destinatario['DESCAP'] . ' ' . $Destinatario['DESCIT'] . ' ' . $Destinatario['DESPRO'] . ' ' . $Destinatario['DESMAIL'];
                $MittentiDestinatari .= str_replace(';', '.', $StrDest) . '; ';
            }

            /*
             * Altri Mittenti:
             */
            $MittentiAggiuntivi = $proLib->getPromitagg($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR']); // OK ORDINATO.
            foreach ($MittentiAggiuntivi as $Mittente) {
                $StrMitt = $Mittente['PRONOM'] . ' ' . $Mittente['PROIND'] . ' ' . $Mittente['PROCAP'] . ' ' . $Mittente['PROCIT'] . ' ' . $Mittente['PROORI'] . ' ' . $Mittente['PROMAIL'];
                $MittentiDestinatari .= str_replace(';', '.', $StrMitt) . '; ';
            }
        }
        return self::ProteggiCarattteri($MittentiDestinatari);
    }

}
