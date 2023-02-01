<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     AlessandroMucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    30.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibIndice.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
include_once (ITA_BASE_PATH . '/apps/Accessi/accLib.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDocReader.class.php';

class praLibFascicoloArch extends itaModel {

    const FONTE_DATI_PRATICAAMM = 'DATI_PRATICA_AMM';
    const FONTE_DATI_DOCUMENTO = 'DATI_SUAP_DOCUMENTO';

    public $accLib;
    public $praLib;
    public $proLib;
    public $proLibAllegati;
    public $proLibSerie;
    public $proLibFascicolo;
    public $proLibTabDag;
    public $proLibIndice;
    public $proLibConservazione;
    public $segLib;
    private $errCode;
    private $errMessage;
    private $GeskeyCreato;
    private $LastAnapro = '';

    const K_SEGNATURA = 'SEGNATURA';
    const K_DATAREGISTRAZIONE = 'DATAREGISTRAZIONE';
    const K_OGGETTO = 'OGGETTO';
    const K_MITTENTE_NOME = 'MITTENTE_NOME';
    const K_MITTENTE_COGNOME = 'MITTENTE_COGNOME';
    const K_MITTENTE_DENOMINAZIONE = 'MITTENTE_DENOMINAZIONE';
    const K_MITTENTE_CODICEFISCALE = 'MITTENTE_CODICEFISCALE';
    const K_MITTENTE_PARTITAIVA = 'MITTENTE_PARTITAIVA';
    const K_MITTENTE_STATO = 'MITTENTE_STATO';
    const K_MITTENTE_REGIONE = 'MITTENTE_REGIONE';
    const K_MITTENTE_COMUNE = 'MITTENTE_COMUNE';
    const K_MITTENTE_CAP = 'MITTENTE_CAP';
    const K_MITTENTE_INDIRIZZO = 'MITTENTE_INDIRIZZO';
    const K_MITTENTE_EMAIL = 'MITTENTE_EMAIL';
    const K_MITTENTE_NUMCIVICO = 'MITTENTE_NUMCIVICO';
    const K_MITTENTE_DATARICEZIONE = 'MITTENTE_DATARICEZIONE';
    const K_MITTENTE_MEZZORICEZIONE = 'MITTENTE_MEZZORICEZIONE';
    const K_MITTENTE_SEGNATURAPROTOCOLLO = 'MITTENTE_SEGNATURAPROTOCOLLO';
    const K_SOGGPROD_CODICEIPA = 'SOGGPROD_CODICEIPA';
    const K_SOGGPROD_DENOMINAZIONE = 'SOGGPROD_DENOMINAZIONE';
    const K_SOGGPROD_TIPOSOGGETTO = 'SOGGPROD_TIPOSOGGETTO';
    const K_SOGGPROD_CONDIZIONEGIURIDICA = 'SOGGPROD_CONDIZIONEGIURIDICA';
    const K_SOGGPROD_STATO = 'SOGGPROD_STATO';
    const K_SOGGPROD_REGIONE = 'SOGGPROD_REGIONE';
    const K_SOGGPROD_COMUNE = 'SOGGPROD_COMUNE';
    const K_SOGGPROD_CAP = 'SOGGPROD_CAP';
    const K_SOGGPROD_INDIRIZZO = 'SOGGPROD_INDIRIZZO';
    const K_SOGGPROD_NUMCIV = 'SOGGPROD_NUMCIV';
    const K_DESTINATARIO_NOME = 'DESTINATARIO_NOME';
    const K_DESTINATARIO_COGNOME = 'DESTINATARIO_COGNOME';
    const K_DESTINATARIO_DENOMINAZIONE = 'DESTINATARIO_DENOMINAZIONE';
    const K_DESTINATARIO_CODICEFISCALE = 'DESTINATARIO_CODICEFISCALE';
    const K_DESTINATARIO_PARTITAIVA = 'DESTINATARIO_PARTITAIVA';
    const K_DESTINATARIO_STATO = 'DESTINATARIO_STATO';
    const K_DESTINATARIO_REGIONE = 'DESTINATARIO_REGIONE';
    const K_DESTINATARIO_COMUNE = 'DESTINATARIO_COMUNE';
    const K_DESTINATARIO_CAP = 'DESTINATARIO_CAP';
    const K_DESTINATARIO_INDIRIZZO = 'DESTINATARIO_INDIRIZZO';
    const K_DESTINATARIO_EMAIL = 'DESTINATARIO_EMAIL';
    const K_DESTINATARIO_NUMCIV = 'DESTINATARIO_NUMCIV';
    const K_DESTINATARIO_RICEVUTEPEC_ACC = 'DESTINATARIO_RICEVUTEPEC_ACC';
    const K_DESTINATARIO_RICEVUTEPEC_CONS = 'DESTINATARIO_RICEVUTEPEC_CONS';
    const K_DESTINATARIAGGIUNTIVI = 'DESTINATARIAGGIUNTIVI';
    const K_LUOGODOCUMENTO = 'LUOGODOCUMENTO';
    const K_TIPODOCUMENTO_CODIDENTIFICATIVO = 'TIPODOCUMENTO_CODIDENTIFICATIVO';
    const K_TIPODOCUMENTO_DENOMINAZIONE = 'TIPODOCUMENTO_DENOMINAZIONE';
    const K_TIPODOCUMENTO_NATURA = 'TIPODOCUMENTO_NATURA';
    const K_TIPODOCUMENTO_ACCESSIBILITA = 'TIPODOCUMENTO_ACCESSIBILITA';
    const K_DATADOCUMENTO = 'DATADOCUMENTO';
    const K_IDENTIFICATIVOPROCEDIMENTO = 'IDENTIFICATIVOPROCEDIMENTO';
    const K_TEMPOMINIMOCONSERVAZIONE = 'TEMPOMINIMOCONSERVAZIONE';
    const K_ANNOAPERTURA = 'ANNOAPERTURA';
    const K_ANNOCHIUSURA = 'ANNOCHIUSURA';
    const K_PROCAMM_CODICEPROCEDIMENTO = 'PROCAMM_CODICEPROCEDIMENTO';
    const K_PROCAMM_DESCRIZIONE = 'PROCAMM_DESCRIZIONE';
    const K_PROCAMM_RIFERIMENTINORMATIVI = 'PROCAMM_RIFERIMENTINORMATIVI';
    const K_PROCAMM_DESTINATARI = 'PROCAMM_DESTINATARI';
    const K_PROCAMM_REGIMIABILITATIVI = 'PROCAMM_REGIMIABILITATIVI';
    const K_PROCAMM_TIPOLOGIA = 'PROCAMM_TIPOLOGIA';
    const K_PROCAMM_SETTOREATTIVITA = 'PROCAMM_SETTOREATTIVITA';
    const K_CODICEIPA = 'CODICEIPA';
    const K_AMMINISTRAZIONIPARTECIPANTI = 'AMMINISTRAZIONIPARTECIPANTI';
    const K_ACCESSIBILITA = 'ACCESSIBILITA';
    const K_CHIAVE_PASSO = 'CHIAVE_PASSO';
    const K_NUMEROPASSO = 'NUMEROPASSO';
    //
    const K_SERIEARCCODICE = 'SERIEARCCODICE';
    const K_SERIEARCSIGLA = 'SERIEARCSIGLA';
    const K_SERIEARCPROGRESSIVO = 'SERIEARCPROGRESSIVO';
    const K_SERIEANNO = 'SERIEANNO';
    // dati aggiuntivi protocollo
    const K_PROTOCOLLO = 'PROTOCOLLO';
    const K_TIPOPROTOCOLLO = 'TIPOPROTOCOLLO';
    const K_DATAPROTOCOLLO = 'DATAPROTOCOLLO';
    const K_ALTRIMITTDEST = 'ALTRIMITTDEST';

    public static $ElencoChiaviAttiveTabDag = array(
        self::K_SEGNATURA => 'SEGNATURA',
        self::K_DATAREGISTRAZIONE => 'DATAREGISTRAZIONE',
        self::K_OGGETTO => 'OGGETTO',
        self::K_MITTENTE_NOME => 'MITTENTE_NOME',
        self::K_MITTENTE_COGNOME => 'MITTENTE_COGNOME',
        self::K_MITTENTE_DENOMINAZIONE => 'MITTENTE_DENOMINAZIONE',
        self::K_MITTENTE_CODICEFISCALE => 'MITTENTE_CODICEFISCALE',
        self::K_MITTENTE_PARTITAIVA => 'MITTENTE_PARTITAIVA',
        self::K_MITTENTE_STATO => 'MITTENTE_STATO',
        self::K_MITTENTE_REGIONE => 'MITTENTE_REGIONE',
        self::K_MITTENTE_COMUNE => 'MITTENTE_COMUNE',
        self::K_MITTENTE_CAP => 'MITTENTE_CAP',
        self::K_MITTENTE_INDIRIZZO => 'MITTENTE_INDIRIZZO',
        self::K_MITTENTE_EMAIL => 'MITTENTE_EMAIL',
        self::K_MITTENTE_NUMCIVICO => 'MITTENTE_NUMCIVICO',
        self::K_MITTENTE_DATARICEZIONE => 'MITTENTE_DATARICEZIONE',
        self::K_MITTENTE_MEZZORICEZIONE => 'MITTENTE_MEZZORICEZIONE',
        self::K_MITTENTE_SEGNATURAPROTOCOLLO => 'MITTENTE_SEGNATURAPROTOCOLLO',
        self::K_SOGGPROD_CODICEIPA => 'SOGGPROD_CODICEIPA',
        self::K_SOGGPROD_DENOMINAZIONE => 'SOGGPROD_DENOMINAZIONE',
        self::K_SOGGPROD_TIPOSOGGETTO => 'SOGGPROD_TIPOSOGGETTO',
        self::K_SOGGPROD_CONDIZIONEGIURIDICA => 'SOGGPROD_CONDIZIONEGIURIDICA',
        self::K_SOGGPROD_STATO => 'SOGGPROD_STATO',
        self::K_SOGGPROD_REGIONE => 'SOGGPROD_REGIONE',
        self::K_SOGGPROD_COMUNE => 'SOGGPROD_COMUNE',
        self::K_SOGGPROD_CAP => 'SOGGPROD_CAP',
        self::K_SOGGPROD_INDIRIZZO => 'SOGGPROD_INDIRIZZO',
        self::K_SOGGPROD_NUMCIV => 'SOGGPROD_NUMCIV',
        self::K_DESTINATARIO_NOME => 'DESTINATARIO_NOME',
        self::K_DESTINATARIO_COGNOME => 'DESTINATARIO_COGNOME',
        self::K_DESTINATARIO_DENOMINAZIONE => 'DESTINATARIO_DENOMINAZIONE',
        self::K_DESTINATARIO_CODICEFISCALE => 'DESTINATARIO_CODICEFISCALE',
        self::K_DESTINATARIO_PARTITAIVA => 'DESTINATARIO_PARTITAIVA',
        self::K_DESTINATARIO_STATO => 'DESTINATARIO_STATO',
        self::K_DESTINATARIO_REGIONE => 'DESTINATARIO_REGIONE',
        self::K_DESTINATARIO_COMUNE => 'DESTINATARIO_COMUNE',
        self::K_DESTINATARIO_CAP => 'DESTINATARIO_CAP',
        self::K_DESTINATARIO_INDIRIZZO => 'DESTINATARIO_INDIRIZZO',
        self::K_DESTINATARIO_EMAIL => 'DESTINATARIO_EMAIL',
        self::K_DESTINATARIO_NUMCIV => 'DESTINATARIO_NUMCIV',
        self::K_DESTINATARIO_RICEVUTEPEC_ACC => 'DESTINATARIO_RICEVUTEPEC_ACC',
        self::K_DESTINATARIO_RICEVUTEPEC_CONS => 'DESTINATARIO_RICEVUTEPEC_CONS',
        self::K_LUOGODOCUMENTO => 'LUOGODOCUMENTO',
        self::K_TIPODOCUMENTO_CODIDENTIFICATIVO => 'TIPODOCUMENTO_CODIDENTIFICATIVO',
        self::K_TIPODOCUMENTO_DENOMINAZIONE => 'TIPODOCUMENTO_DENOMINAZIONE',
        self::K_TIPODOCUMENTO_NATURA => 'TIPODOCUMENTO_NATURA',
        self::K_TIPODOCUMENTO_ACCESSIBILITA => 'TIPODOCUMENTO_ACCESSIBILITA',
        self::K_DATADOCUMENTO => 'DATADOCUMENTO',
        self::K_IDENTIFICATIVOPROCEDIMENTO => 'IDENTIFICATIVOPROCEDIMENTO',
        self::K_TEMPOMINIMOCONSERVAZIONE => 'TEMPOMINIMOCONSERVAZIONE',
        self::K_CHIAVE_PASSO => 'CHIAVE_PASSO',
        self::K_NUMEROPASSO => 'NUMEROPASSO',
        self::K_ANNOAPERTURA => 'ANNOAPERTURA',
        self::K_ANNOCHIUSURA => 'ANNOCHIUSURA',
        self::K_PROCAMM_CODICEPROCEDIMENTO => 'PROCAMM_CODICEPROCEDIMENTO',
        self::K_PROCAMM_DESCRIZIONE => 'PROCAMM_DESCRIZIONE',
        self::K_PROCAMM_RIFERIMENTINORMATIVI => 'PROCAMM_RIFERIMENTINORMATIVI',
        self::K_PROCAMM_DESTINATARI => 'PROCAMM_DESTINATARI',
        self::K_PROCAMM_REGIMIABILITATIVI => 'PROCAMM_REGIMIABILITATIVI',
        self::K_PROCAMM_TIPOLOGIA => 'PROCAMM_TIPOLOGIA',
        self::K_PROCAMM_SETTOREATTIVITA => 'PROCAMM_SETTOREATTIVITA',
        self::K_CODICEIPA => 'CODICEIPA',
        self::K_AMMINISTRAZIONIPARTECIPANTI => 'AMMINISTRAZIONIPARTECIPANTI',
        self::K_PROTOCOLLO => 'PROTOCOLLO',
        self::K_TIPOPROTOCOLLO => 'TIPOPROTOCOLLO',
        self::K_DATAPROTOCOLLO => 'DATAPROTOCOLLO',
        self::K_ALTRIMITTDEST => 'ALTRIMITTDEST',
        self::K_SERIEARCCODICE => 'SERIEARCCODICE',
        self::K_SERIEARCSIGLA => 'SERIEARCSIGLA',
        self::K_SERIEARCPROGRESSIVO => 'SERIEARCPROGRESSIVO',
        self::K_SERIEANNO => 'SERIEANNO',
    );

    function __construct() {
        $this->accLib = new accLib();
        $this->praLib = new praLib();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibSerie = new proLibSerie();
        $this->proLibFascicolo = new proLibFascicolo();
        $this->proLibTabDag = new proLibTabDag();
        $this->segLib = new segLib(); //Verificare se serve dopo prove di creazione ANPARO "I" senza indice
        $this->proLibIndice = new proLibIndice();
        $this->proLibConservazione = new proLibConservazione();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getGeskeyCreato() {
        return $this->GeskeyCreato;
    }

    public function setGeskeyCreato($GeskeyCreato) {
        $this->GeskeyCreato = $GeskeyCreato;
    }

    public function getLastAnapro() {
        return $this->LastAnapro;
    }

    public function setLastAnapro($LastAnapro) {
        $this->LastAnapro = $LastAnapro;
    }

    public function ControlliFascicoloArchivistico($Gesnum) {
        /*
         * Se non serve crearlo torna 
         */
        $Filent46 = $this->praLib->GetFilent(46);
        if (!$Filent46['FILVAL']) {
            return true;
        }

        $Proges_rec = $this->praLib->GetProges($Gesnum, 'codice');
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica con codice ' . $Gesnum . ' non trovata.');
            return false;
        }
        if (!$Proges_rec['SERIECODICE']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Serie non definita per il fascicolo. Non è possibile creare il fascicolo archivistico.');
            return false;
        }
        if (!$Proges_rec['SERIEPROGRESSIVO']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Progressivo serie non definito,  non è possibile creare il fascicolo archivistico. ');
            return false;
        }

        $titolario = $this->GetClassificazioneConservazione($Proges_rec['GESPRO'], $Gesnum);
        if ($titolario == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Titolario conservazione non definito per il fasciolo n. $Gesnum.");
            return false;
        }

//        $SerieConnTit_rec = $this->proLibSerie->GetTitolariConnSerie($Proges_rec['SERIECODICE'], $Versione_T);
//        if (!$SerieConnTit_rec) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Nessun titolario collegato alla serie: ' . $Proges_rec['SERIECODICE']);
//            return false;
//        }
//        $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
//        if (!$Anatsp_rec) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Sportello Pratica n. ' . $Gesnum . ' non definito. ');
//            return false;
//        }
//        $arrTrasmissioni = explode("|", $Anatsp_rec['TSPUOP']);
//        list($codiceUfficio, $codiceDest) = explode(".", $arrTrasmissioni[0]);
//        if (!$codiceDest) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Responsabile non definito nello Sportello: ' . $Proges_rec['GESTSP'] . ' della pratica: ' . $Gesnum);
//            return false;
//        }
//        if (!$codiceUfficio) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Ufficio non definito nello Sportello: ' . $Proges_rec['GESTSP'] . ' della pratica: ' . $Gesnum);
//            return false;
//        }
        $Ananom_rec = $this->praLib->GetAnanom($Proges_rec['GESRES']);
        if (!$Ananom_rec || !$Ananom_rec['NOMDEP']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Dipendente : ' . $Proges_rec['GESRES'] . ' non trovato per la pratica: ' . $Gesnum);
            return false;
        }
        $codiceDest = $Ananom_rec['NOMDEP'];

        $uffdes_tab = $this->proLib->GetUffdes($codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
        if (!$uffdes_tab || !isset($uffdes_tab[0]['UFFCOD'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ufficio non definito per il destinatario: $codiceDest della pratica: $Gesnum");
            return false;
        }
        $codiceUfficio = $uffdes_tab[0]['UFFCOD'];

        /*
         * Controllo UFFICIO e RESPONSABILE
         */
        $Anamed_rec = $this->proLib->GetAnamed($codiceDest);
        if (!$Anamed_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Responsabile con codice ' . $codiceDest . ' non trovato in anagrafica.');
            return false;
        }
        $Anauff_rec = $this->proLib->GetAnauff($codiceUfficio);
        if (!$Anauff_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Ufficio con codice ' . $codiceUfficio . ' non trovato in anagrafica.');
            return false;
        }

        $utenti_rec = $this->accLib->GetUtenti($codiceDest, 'uteana1');
        if (!$utenti_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Login Utente per codice soggetto: ' . $codiceDest . ' non trovato.');
            return false;
        }

        /*
         * Tipo Documento Definitito
         */
        $anaent_59 = $this->proLib->GetAnaent('59');
        if (!$anaent_59['ENTDE1']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Tipo Documento Pratica non definito.');
            return false;
        }
        /*
         * Controllo oggetto pratica valorizzato
         */
        if (!$this->GetOggettoPratica($Gesnum)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Oggetto della pratica non definito.');
            return false;
        }


        return true;
    }

    public function CreazioneFascicoloArchivistico($Gesnum) {
        /*
         * 1. Lettura Pratica e
         *    Controllo parametri
         */
        $Proges_rec = $this->praLib->GetProges($Gesnum, 'codice');
        if (!$Proges_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Pratica con codice ' . $Gesnum . ' non trovata.');
            return false;
        }

        if ($Proges_rec['GESKEY']) {
            // Aggiorno solo data chiusura.
            //$this->ControllaAggiornaFascicolo();
            return true;
        }

        /*
         * Se non serve crearlo torna 
         */
        $Filent46 = $this->praLib->GetFilent(46);
        if (!$Filent46['FILVAL']) {
            return true;
        }
        switch ($Filent46['FILVAL']) {
            case'1':
                break;

            case '2':
                // Caso creazione alla creazione fascicolo: sospeso.
                return true;
                break;
        }
        /*
         * Esecuzione Controlli:
         */
        if (!$this->ControlliFascicoloArchivistico($Gesnum)) {
            return false;
        }
        /*
         * Preparazione Vraibili:
         */
        $DatiFascicolo = array();
        $MetadatiFascicolo = array();
        $Serie_rec = array();
        /*
         * Estrazione SERIE:
         */
        $Serie_rec['CODICE'] = $Proges_rec['SERIECODICE'];
        $Serie_rec['PROGSERIE'] = $Proges_rec['SERIEPROGRESSIVO'];
        $Serie_rec['FORZA_PROGSERIE'] = $Proges_rec['SERIEPROGRESSIVO'];
        $Serie_rec['ANNOGSERIE'] = $Proges_rec['SERIEANNO']; // Ai fascicoli di protocollo non serve...
        $DatiFascicolo['SERIE'] = $Serie_rec;
        /*
         * Estrazione TITOLARIO:
         */
        $Versione_T = $this->proLib->GetTitolarioCorrente();
//        $SerieConnTit_rec = $this->proLibSerie->GetTitolariConnSerie($Serie_rec['CODICE'], $Versione_T);
//        if (!$SerieConnTit_rec) {
//            $this->setErrCode(-1);
//            $this->setErrMessage('Nessun titolario collegato alla serie: ' . $Serie_rec['CODICE']);
//            return false;
//        }
//        $DatiFascicolo['VERSIONE_T'] = $Versione_T;
//        $DatiFascicolo['TITOLARIO'] = $SerieConnTit_rec['ORGCCF'];


        $tmpTitolario = $this->GetClassificazioneConservazione($Proges_rec['GESPRO'], $Gesnum);
        if ($tmpTitolario == "") {
            $this->setErrCode(-1);
            $this->setErrMessage("Titolario conservazione non definito per il fasciolo n. $Gesnum.");
            return false;
        }

        $arrTit = explode(".", $tmpTitolario);
        $titolario = "";
        foreach ($arrTit as $value) {
            if ($value) {
                $titolario .= str_pad($value, 4, "0", STR_PAD_LEFT);
            }
        }


        $DatiFascicolo['VERSIONE_T'] = $Versione_T;
        $DatiFascicolo['TITOLARIO'] = $titolario;
        /*
         * Estrazione OGGETTO:
         */
        $DescrizioneOggetto = $this->GetOggettoPratica($Gesnum);

        /*
         * Estrazione Responsabile/Ufficio:
         */

        /*

          $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
          if (!$Anatsp_rec) {
          $this->setErrCode(-1);
          $this->setErrMessage('Sportello Pratica n. ' . $Gesnum . ' non definito. ');
          return false;
          }
          $arrTrasmissioni = explode("|", $Anatsp_rec['TSPUOP']);
          list($codiceUfficio, $codiceDest) = explode(".", $arrTrasmissioni[0]);
         */

        $Ananom_rec = $this->praLib->GetAnanom($Proges_rec['GESRES']);
        if (!$Ananom_rec || !$Ananom_rec['NOMDEP']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Dipendente : ' . $Proges_rec['GESRES'] . ' non trovato per la pratica: ' . $Gesnum);
            return false;
        }
        $codiceDest = $Ananom_rec['NOMDEP'];

        $uffdes_tab = $this->proLib->GetUffdes($codiceDest, 'uffkey', true, ' ORDER BY UFFFI1__3 DESC', true);
        if (!$uffdes_tab || !isset($uffdes_tab[0]['UFFCOD'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ufficio non definito per il destinatario: $codiceDest della pratica: $Gesnum");
            return false;
        }
        $codiceUfficio = $uffdes_tab[0]['UFFCOD'];

        $DatiFascicolo['UFF'] = $codiceUfficio;
        $DatiFascicolo['GESPROUFF'] = $codiceUfficio;
        $DatiFascicolo['RES'] = $codiceDest;
        if (!$DatiFascicolo['RES']) {
            $this->setErrCode(-1);
            $this->setErrMessage('Responsabile non definito per la pratica: ' . $Gesnum);
            return false;
        }
        if (!$DatiFascicolo['GESPROUFF']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Ufficio non definito per il destinatario: $codiceDest della pratica: $Gesnum");
            return false;
        }
        /*
         * Controllo UFFICIO e RESPONSABILE
         */
        $Anamed_rec = $this->proLib->GetAnamed($DatiFascicolo['RES']);
        if (!$Anamed_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Responsabile con codice ' . $codiceDest . ' non trovato in anagrafica.');
            return false;
        }
        $DatiFascicolo['RES_DESCRIZIONE'] = $Anamed_rec['MEDNOM'];
        $Anauff_rec = $this->proLib->GetAnauff($DatiFascicolo['UFF']);
        if (!$Anauff_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Ufficio con codice ' . $codiceUfficio . ' non trovato in anagrafica.');
            return false;
        }
        $DatiFascicolo['UFF_DESCRIZIONE'] = $Anauff_rec['UFFDES'];
        /*
         * Estrazione DATA CHIUSURA:
         * GESDCH
         */
        $DatiFascicolo['DATACHIUSURA'] = $Proges_rec['GESDCH'];
        /*
         * Estrazione ALLEGATI
         */

        $praFascicolo = new praFascicolo($Gesnum);

        /*
         * Dati Aggiuntivi
         */
        $DatiFascicolo['DATA_FASCICOLO'] = $Proges_rec['GESDRE']; // Verificare se serve
        $DatiFascicolo['ANNO_FASCICOLO'] = $Proges_rec['SERIEANNO']; //Verificare utilizzo/se serve
        $codiceProcedimento = $Gesnum; // Codice Procedimento:
        /*
         * Utente Inseritore:
         * Ricavo UTENTE_INS da UTEANA1
         */
        $utenti_rec = $this->accLib->GetUtenti($DatiFascicolo['RES'], 'uteana1');
        if (!$utenti_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Login Utente per codice soggetto: ' . $DatiFascicolo['RES'] . ' non trovato.');
            return false;
        }
        $DatiFascicolo['UTENTE_INS'] = $utenti_rec['UTELOG'];
        /*
         * Parametro Tipo Registro
         */
        $anaent_59 = $this->proLib->GetAnaent('59');
        $DatiFascicolo['PROCODTIPODOC'] = $anaent_59['ENTDE1']; //'PRAM';//PARAMETRO!
        $DatiFascicolo['DATI_ANAORG']['GESNUMFASC'] = $Gesnum;

        /*
         * Preparazione Metadati
         */
        $MetadatiFascicolo = $this->PreparaMetadatiFascicolo($Proges_rec, $DatiFascicolo, $DescrizioneOggetto, $codiceProcedimento);

        /*
         * Chiamata a libreria di protocollo per creazione fascicolo.
         */
        $model = 'proGestPratica';
        $formObj = itaModel::getInstance($model);
        $GesKey = $this->proLibFascicolo->creaFascicoloArchivistico($formObj, $DatiFascicolo, $DescrizioneOggetto, $codiceProcedimento);
        if (!$GesKey) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->proLibFascicolo->getErrMessage());
            return false; // La chiusura della pratica quindi deve avvenire dopo.
        }
        /*
         * Aggiungo ai Metadati Fascicoli le chiavi
         */
        $MetadatiFascicolo['IDENTIFICATIVOFASCICOLO'] = $GesKey;
        $this->GeskeyCreato = $GesKey;
        /*
         * Lettura PROGES Protocollo
         */
        $ProgesProt_rec = $this->proLib->GetProges($GesKey, 'geskey');
        /*
         * Collego la pratica al fascicolo archivistico:
         */
        $Proges_rec['GESKEY'] = $GesKey;
        try {
            ItaDB::DBUpdate($this->praLib->getPRAMDB(), 'PROGES', 'ROWID', $Proges_rec);
        } catch (Exception $exc) {
            $this->setErrCode(2); //Errore ma fascicolo creato.
            $this->setErrMessage("Errore in collegamento pratica al fascicolo archivistico $GesKey. " . $exc->getMessage());
            return false;
        }

        $AnaproFascicolo_rec = $this->proLibFascicolo->getAnaproFascicolo($GesKey);
        if (!$AnaproFascicolo_rec) {
            $this->setErrCode(2);
            $this->setErrMessage("Errore in lettura Anapro fascicolo archivistico $GesKey. ");
            return false;
        }
        /*
         * Estrazione Metadati Fascicolo
         */
        if (!$this->SalvaMetadatiFascicolo($MetadatiFascicolo, $AnaproFascicolo_rec)) {
            return false; // Potrebbe continuare
        }

        /*
         * Elaborazione Passi:
         * Creazione ANAPRO di tipo "I" per ogni passo.
         */
        $ElencoPassiDaConservare = $this->GetPassiDaConservare($Proges_rec);
        foreach ($ElencoPassiDaConservare as $Passo) {
            if (!$this->CreaDocumentoFascicoloDaPasso($formObj, $Passo, $DatiFascicolo, $AnaproFascicolo_rec)) {
                // False o accodo errori per messaggio finale..
                return false;
            }
        }
        /*
         * Chiusura Fascicolo:
         */
        if (!$this->proLibFascicolo->chiudiFascicolo($formObj, $ProgesProt_rec['GESNUM'], $DatiFascicolo['DATACHIUSURA'])) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore in Chiusura Fascicolo Archivistico. " . $exc->getMessage());
            return false;
        }

        return true;
    }

    public function CreaDocumentoFascicoloDaPasso($formObj, $DatiPasso, $DatiFascicolo, $AnaproFascicolo_rec) {
        // $progressivo = $this->segLib->PrenotaProgressivoDocumento('DOCUMENTOGENERICO', true);
        /*
         * Creazione ANAPRO "I" senza indice.
         */
        $Indice_rec = array();
        $Indice_rec['IOGGETTO'] = $DatiPasso['DATI']['OGGETTO'];
        $DatiProtocollo = array();
        $DatiProtocollo['FIRMATARIO']['DESCOD'] = $DatiFascicolo['RES'];
        $DatiProtocollo['FIRMATARIO']['DESNOM'] = $DatiFascicolo['RES_DESCRIZIONE'];
        $DatiProtocollo['FIRMATARIO']['DESCUF'] = $DatiFascicolo['UFF'];
        $DatiProtocollo['PROUTE'] = $DatiFascicolo['UTENTE_INS'];
        $DatiProtocollo['PROUOF'] = $DatiFascicolo['UFF'];
        $DatiProtocollo['PROSECURE'] = '';
        $DatiProtocollo['PROTSO'] = '';
        $DatiProtocollo['PRORISERVA'] = '';
        $DatiProtocollo['VERSIONE_T'] = $DatiFascicolo['VERSIONE_T'];
        $DatiProtocollo['DESTINATARIINTERNI'] = '';
        $DatiProtocollo['ALTRIDESTINATARI'] = ''; // ci sono?
        $DatiProtocollo['UFFICI'] = '';
        $Procat = substr($DatiFascicolo['TITOLARIO'], 0, 4);
        $Procla = substr($DatiFascicolo['TITOLARIO'], 0, 8);
        $DatiProtocollo['PROCAT'] = $Procat;
        $DatiProtocollo['PROCCA'] = $Procla;
        $DatiProtocollo['PROCCF'] = $DatiFascicolo['TITOLARIO'];
        $DatiProtocollo['PROCODTIPODOC'] = $DatiFascicolo['PROCODTIPODOC'];
        //
        if ($DatiPasso['DESTINATARIAGGIUNTIVI']) {
            foreach ($DatiPasso['DESTINATARIAGGIUNTIVI']as $key => $record) {
                $AltroDestinatario = array();
                $AltroDestinatario['DESNOM'] = $record['NOME'];
                $AltroDestinatario['DESFIS'] = $record['FISCALE'];
                $AltroDestinatario['DESIND'] = $record['INDIRIZZO'];
                $AltroDestinatario['DESCAP'] = $record['CAP'];
                $AltroDestinatario['DESCIT'] = $record['COMUNE'];
                $AltroDestinatario['DESPRO'] = $record['PROVINCIA'];
                $AltroDestinatario['DESDAT'] = $record['DATAINVIO'];
                $AltroDestinatario['DESMAIL'] = $record['MAIL'];
                $AltroDestinatario['DESIDMAIL'] = $record['IDMAIL'];
                $AltroDestinatario['DESTSP'] = $record['TIPOINVIO'];
            }
            $DatiProtocollo['ALTRIDESTINATARI'][] = $AltroDestinatario;
        }

        $rowid_anapro = $this->proLibIndice->getNewIndiceAnapro($formObj, $Indice_rec, $DatiProtocollo);
        if (!$rowid_anapro) {
            $this->setErrCode(2);
            $this->setErrMessage('Errore in creazione Documento. ' . $this->proLibIndice->getErrMessage());
            return false;
        }
        $Anapro_rec = $this->proLib->GetAnapro($rowid_anapro, 'rowid');
        $this->LastAnapro = $rowid_anapro;
        /*
         * Carico gli ANADOC:
         */
        $subPath = "proFascArch-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        $gesnum = $DatiPasso['DATI']['IDENTIFICATIVOPROCEDIMENTO'];

        foreach ($DatiPasso['ALLEGATI'] as $allegato) {
            if (!$this->AggiungiAllegatoAlDocumento($formObj, $Anapro_rec, $allegato, $gesnum, $tempPath)) {
                return false;
            }
        }
        /*
         * Salvataggio dei Metadati
         */
        if (!$this->SalvaMetadatiDocumento($DatiPasso['DATI'], $Anapro_rec)) {
            return false;
        }
        /*
         * Inserimento in fascicolo 
         */
        if (!$this->proLibFascicolo->insertDocumentoFascicolo($formObj, $this->GeskeyCreato, $Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'], $AnaproFascicolo_rec['PRONUM'], $AnaproFascicolo_rec['PROPAR'])) {
            $this->setErrCode(2);
            $this->setErrMessage('Errore in inserimento Documento nel fascicolo. ' . $this->proLibFascicolo->getErrMessage());
            return false;
        }

        return true;
    }

    public function GetPassiDaConservare($Proges_rec) {
        // Verificare se sono da prendere tutti. Per ora solo i protocollati.
        $PassiDaConservare = array();
        $Gesnum = $Proges_rec['GESNUM'];

        $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);
        if (!$Anatsp_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Sportello Pratica n. ' . $Gesnum . ' non definito. ');
            return false;
        }
        /*
         * Lettura Serie:
         */
        $decod_serie = $this->proLibSerie->GetSerie($Proges_rec['SERIECODICE'], 'codice');

        /*
         * Passo Principale
         */
        $Passo = array();
        $sql = "SELECT * FROM PASDOC WHERE PASKEY LIKE '$Gesnum%' AND PASPRTCLASS = 'PROGES' AND PASPRTROWID = {$Proges_rec['ROWID']}  ";
        $Pasdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        $Passo['ALLEGATI'] = $Pasdoc_tab;
        /*
         * Preparazione dati Pratica Principale
         */
        $Dati = array();
        //
        $Dati['DATAREGISTRAZIONE'] = $Proges_rec['GESDRE']; // Data registrazione
        $Dati['OGGETTO'] = $Proges_rec['GESOGG'];
        // Dati Protocollo:
        $Dati['PROTOCOLLO'] = $Proges_rec['GESNPR'];
        $Dati['TIPOPROTOCOLLO'] = $Proges_rec['GESPAR'];
        if (!$Dati['TIPOPROTOCOLLO']) {
            $Dati['TIPOPROTOCOLLO'] = 'A';
        }
        if ($Proges_rec['GESDPR']) {
            $Dati['DATAPROTOCOLLO'] = $Proges_rec['GESDPR'];
        } else {
            if ($Proges_rec['GESMETA']) {
                $Metadati = unserialize($Proges_rec['GESMETA']);
                if (isset($Metadati['DatiProtocollazione']['Data']['value'])) {
                    $time = strtotime($Metadati['DatiProtocollazione']['Data']['value']);
                    $Dati['DATAPROTOCOLLO'] = date('Ymd', $time);
                }
            }
        }
        if (!$Dati['DATAPROTOCOLLO']) {
            $Dati['DATAPROTOCOLLO'] = $Proges_rec['GESDRE'];
        }
        if (!$Dati['OGGETTO']) {
            $praLibVar = new praLibVariabili();
            $Filent_rec = $this->praLib->GetFilent(3);
            $praLibVar->setCodicePratica($Gesnum);
            $Dati['OGGETTO'] = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_rec['FILVAL']);
        }
        /*
         * Dati Mittente: per ora il primo.
         */
        $Anades_rec = $this->praLib->GetAnades($Gesnum, 'codice');

        $Dati['MITTENTE_NOME'] = $Anades_rec['DESNOME'];
        $Dati['MITTENTE_COGNOME'] = $Anades_rec['DESCOGNOME'];
        $Dati['MITTENTE_DENOMINAZIONE'] = $Anades_rec['DESRAGSOC'] ? $Anades_rec['DESRAGSOC'] : $Anades_rec['DESNOM'];
        $Dati['MITTENTE_CODICEFISCALE'] = $Anades_rec['DESFIS'];
        $Dati['MITTENTE_PARTITAIVA'] = $Anades_rec['DESPIVA'];
        $Dati['MITTENTE_INDIRIZZO'] = $Anades_rec['DESIND'];
        $Dati['MITTENTE_STATO'] = $Anades_rec['DESNAZ'];
        $Dati['MITTENTE_REGIONE'] = '';
        $Dati['MITTENTE_CAP'] = $Anades_rec['DESCAP'];
        $Dati['MITTENTE_COMUNE'] = $Anades_rec['DESCIT'];
        $Email = $Anades_rec['DESPEC'];
        if (!$Email) {
            $Anades_rec['DESEMA'];
        }
        $Dati['MITTENTE_EMAIL'] = $Email;
        $Dati['MITTENTE_DATARICEZIONE'] = $Proges_rec['GESDRI'];
        $Dati['MITTENTE_MEZZORICEZIONE'] = ''; // Non presente un campo.
        if ($Proges_rec['GESMETA']) {
            $Metadati = unserialize($Proges_rec['GESMETA']);
            if (isset($Metadati['DatiProtocollazione']['Segnatura']['value'])) {
                $Dati['MITTENTE_SEGNATURAPROTOCOLLO'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
            }
        }
        /*
         * Soggetto Produttore.
         */
        $Dati['SOGGPROD_CODICEIPA'] = $Anatsp_rec['TSPAMMIPA'];
        $Dati['SOGGPROD_DENOMINAZIONE'] = $Anatsp_rec['TSPDES'];
        $Dati['SOGGPROD_TIPOSOGGETTO'] = $Anatsp_rec['TSPAMMIPA']; // Giuridico?
        $Dati['SOGGPROD_CONDIZIONEGIURIDICA'] = $Anatsp_rec['TSPAMMIPA'];
        $Dati['SOGGPROD_COMUNE'] = $Anatsp_rec['TSPCOM'];
        $Dati['SOGGPROD_CAP'] = $Anatsp_rec['TSPCAP'];
        $Dati['SOGGPROD_STATO'] = ''; // non presente un campo
        $Dati['SOGGPROD_REGIONE'] = ''; // non presente un campo
        $Dati['SOGGPROD_INDIRIZZO'] = $Anatsp_rec['TSPIND'];
        $Dati['SOGGPROD_NUMCIV'] = $Anatsp_rec['TSPNCI'];
        /*
         * PRATICA IN ARRIVO. DESTINATARIO NON PRESENTE 
         */
        $Dati['TIPODOCUMENTO_CODIDENTIFICATIVO'] = $Anatsp_rec['TSPTDO'];
        $Dati['TIPODOCUMENTO_DENOMINAZIONE'] = $Anatsp_rec['TSPTDO'];
        /*
         * Altri Dati
         */
        $Dati['LUOGODOCUMENTO'] = '';
        $Dati['DATADOCUMENTO'] = $Proges_rec['GESDRI']; // Data ricezione
        $Dati['IDENTIFICATIVOPROCEDIMENTO'] = $Gesnum; // Chiave del procedimento.
        $Dati['TEMPOMINIMOCONSERVAZIONE'] = '';
        $Dati['CHIAVE_PASSO'] = $Gesnum;
        $Dati['NUMEROPASSO'] = '';
        /*
         * Valorizzazione Serie Archivistiche.
         */
        $Dati['SERIEARCCODICE'] = $Proges_rec['SERIECODICE'];
        $Dati['SERIEARCSIGLA'] = $decod_serie['SIGLA'];
        $Dati['SERIEARCPROGRESSIVO'] = $Proges_rec['SERIEPROGRESSIVO'];
        $Dati['SERIEARCANNO'] = $Proges_rec['SERIEANNO'];



        /*
         * Assegnazione dati passo
         */
        $Passo['DATI'] = $Dati;
        $PassiDaConservare[] = $Passo;



        /*
         * Estrazione Passi:
         */
        $sql = "SELECT * FROM PRACOM WHERE COMNUM = '$Gesnum' AND COMPRT <> '' ";
        $Pracom_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        // Estrazione allegati per ogni singola comunicazione
        foreach ($Pracom_tab as $Pracom_rec) {
            $Passo = array();
            /*
             * Estrazione PROPAS
             */
            $Propas_rec = $this->praLib->GetPropas($Pracom_rec['COMPAK'], 'propak');
            /*
             * Estrazione Allegati
             */
            $sql = "SELECT * FROM  PASDOC WHERE PASKEY LIKE '$Gesnum%' AND PASPRTCLASS = 'PRACOM' AND PASPRTROWID = {$Pracom_rec['ROWID']} ";
            $Pasdoc_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
            /*
             * Se NON sono presenti allegati nel passo, non è conservabile.
             */
            if (!$Pasdoc_tab) {
                // Non è presente alcun allegato
                continue;
            }
            //
            $Passo['ALLEGATI'] = $Pasdoc_tab;
            $Dati = array();
            $Dati['DATAREGISTRAZIONE'] = $Pracom_rec['COMDPR'];
            $Dati['OGGETTO'] = $Propas_rec['PRODPA'];
            $Dati['PROTOCOLLO'] = $Pracom_rec['COMPRT'];
            $Dati['TIPOPROTOCOLLO'] = $Pracom_rec['COMTIP'];
            $Dati['DATAPROTOCOLLO'] = $Pracom_rec['COMDPR'];
            if ($Pracom_rec['COMTIP'] == 'A') {
                $Dati['MITTENTE_NOME'] = '';
                $Dati['MITTENTE_COGNOME'] = '';
                $Dati['MITTENTE_DENOMINAZIONE'] = $Pracom_rec['COMNOM'];
                $Dati['MITTENTE_CODICEFISCALE'] = $Pracom_rec['COMFIS'];
                $Dati['MITTENTE_PARTITAIVA'] = $Pracom_rec['COMFIS'];
                $Dati['MITTENTE_STATO'] = ''; // Non presente come campo
                $Dati['MITTENTE_REGIONE'] = ''; // Non presente come campo
                $Dati['MITTENTE_INDIRIZZO'] = $Pracom_rec['COMIND'];
                $Dati['MITTENTE_CAP'] = $Pracom_rec['COMCAP'];
                $Dati['MITTENTE_COMUNE'] = $Pracom_rec['COMCIT'];
                $Dati['MITTENTE_EMAIL'] = $Pracom_rec['COMMLD'];
                $Dati['MITTENTE_DATARICEZIONE'] = $Pracom_rec['COMDAT'];
                $Dati['MITTENTE_MEZZORICEZIONE'] = $Pracom_rec['COMTIN'];
                if ($Pracom_rec['COMMETA']) {
                    $Metadati = unserialize($Pracom_rec['COMMETA']);
                    if (isset($Metadati['DatiProtocollazione']['Segnatura']['value'])) {
                        $Dati['MITTENTE_SEGNATURAPROTOCOLLO'] = $Metadati['DatiProtocollazione']['Segnatura']['value'];
                    }
                }
                // TSPTDOENDOARR
                $Dati['TIPODOCUMENTO_CODIDENTIFICATIVO'] = $Anatsp_rec['TSPTDOENDOARR']; // Definire cosa vuole
                $Dati['TIPODOCUMENTO_DENOMINAZIONE'] = $Anatsp_rec['TSPTDOENDOARR']; // Definire cosa vuole
                // Mittenti aggiuntivi presenti?
            } else {
                /*
                 * Preso primo destinatario.
                 */
                $Dati['DESTINATARIO_NOME'] = '';
                $Dati['DESTINATARIO_COGNOME'] = '';
                $Dati['DESTINATARIO_DENOMINAZIONE'] = $Pracom_rec['COMNOM'];
                $Dati['DESTINATARIO_CODICEFISCALE'] = $Pracom_rec['COMFIS'];
                $Dati['DESTINATARIO_PARTITAIVA'] = $Pracom_rec['COMFIS'];
                $Dati['DESTINATARIO_INDIRIZZO'] = $Pracom_rec['COMIND'];
                $Dati['DESTINATARIO_CAP'] = $Pracom_rec['COMCAP'];
                $Dati['DESTINATARIO_COMUNE'] = $Pracom_rec['COMCIT'];
                $Dati['DESTINATARIO_EMAIL'] = $Pracom_rec['COMMLD'];
                $Dati['DESTINATARIO_NUMCIV'] = ''; // Insieme ad indirizzo.
                $Dati['DESTINATARIO_RICEVUTEPEC_ACC'] = ''; // Definire cosa vuole
                $Dati['DESTINATARIO_RICEVUTEPEC_CONS'] = ''; // Definire cosa vuole
                // Tipo Documento   TSPTDOENDOPAR
                $Dati['TIPODOCUMENTO_CODIDENTIFICATIVO'] = $Anatsp_rec['TSPTDOENDOPAR'];
                $Dati['TIPODOCUMENTO_DENOMINAZIONE'] = $Anatsp_rec['TSPTDOENDOPAR'];
                /*
                 * Estrazione Destinatari Aggiuntivi
                 */
                $DestinatariAggiuntivi = $this->praLib->GetPraDestinatari($Pracom_rec['COMPAK'], 'codice', true);
                $Passo['DESTINATARIAGGIUNTIVI'] = $DestinatariAggiuntivi;
                $AltriDestinatari = '';
                foreach ($DestinatariAggiuntivi as $Dest) {
                    $AltriDestinatari .= $Dest['NOME'] . ' - ';
                    $AltriDestinatari .= $Dest['INDIRIZZO'] . ' ' . $Dest['COMUNE'] . ' ' . $Dest['CAP'] . ' ' . $Dest['PROVINCIA'] . ' - ';
                    $AltriDestinatari .= $Dest['MAIL'] . ' - ' . $Dest['FISCALE'] . ' | ';
                }
                $Dati['ALTRIMITTDEST'] = $AltriDestinatari;
            }
            /*
             * Dati soggetto produttore: Letti dallo sportello.
             * Estrazione come funzione "getdatiproduttore"
             */
            $Dati['SOGGPROD_CODICEIPA'] = $Anatsp_rec['TSPAMMIPA'];
            $Dati['SOGGPROD_DENOMINAZIONE'] = $Anatsp_rec['TSPDES'];
            $Dati['SOGGPROD_TIPOSOGGETTO'] = $Anatsp_rec['TSPAMMIPA']; // Giuridico?
            $Dati['SOGGPROD_CONDIZIONEGIURIDICA'] = $Anatsp_rec['TSPAMMIPA'];
            $Dati['SOGGPROD_COMUNE'] = $Anatsp_rec['TSPCOM'];
            $Dati['SOGGPROD_CAP'] = $Anatsp_rec['TSPCAP'];
            $Dati['SOGGPROD_INDIRIZZO'] = $Anatsp_rec['TSPIND'];
            $Dati['SOGGPROD_NUMCIV'] = $Anatsp_rec['TSPNCI'];
            /*
             * Altri Dati
             */
            $Dati['LUOGODOCUMENTO'] = '';
            $Dati['DATADOCUMENTO'] = $Propas_rec['PROINI']; // Data apertura passo.
            // Ipotesi chiave con indicazione tipo protocollo. Nello stesso passo possono esserci 2 protocolli (un arrivo e una partenza)
            $Dati['IDENTIFICATIVOPROCEDIMENTO'] = $Pracom_rec['COMPAK'] . '-' . $Pracom_rec['COMTIP'];
            $Dati['TEMPOMINIMOCONSERVAZIONE'] = '';
            $Dati['CHIAVE_PASSO'] = $Propas_rec['PROPAK'];
            $Dati['NUMEROPASSO'] = $Propas_rec['PROSEQ'];
            /*
             * Valorizzazione Serie Archivistiche.
             */
            $Dati['SERIEARCCODICE'] = $Proges_rec['SERIECODICE'];
            $Dati['SERIEARCSIGLA'] = $decod_serie['SIGLA'];
            $Dati['SERIEARCPROGRESSIVO'] = $Proges_rec['SERIEPROGRESSIVO'];
            $Dati['SERIEARCANNO'] = $Proges_rec['SERIEANNO'];


            /*
             * Assegnazioen dati passo.
             */
            $Passo['DATI'] = $Dati;
            $PassiDaConservare[] = $Passo;
        }
        return $PassiDaConservare;
    }

    public function PreparaMetadatiFascicolo($Proges_rec, $DatiFascicolo, $DescrizioneOggetto, $codiceProcedimento) {

        $Anapra_rec = $this->praLib->GetAnapra($Proges_rec['GESPRO'], 'codice');
        $Anatip_rec = $this->praLib->GetAnatip($Proges_rec['GESTIP'], 'codice');
        $Anaset_rec = $this->praLib->GetAnaset($Proges_rec['GESSTT'], 'codice');
        $Anatsp_rec = $this->praLib->GetAnatsp($Proges_rec['GESTSP']);

        $MetadatiFascicolo = array();
        $MetadatiFascicolo['OGGETTOFASCICOLO'] = $DescrizioneOggetto;
        $MetadatiFascicolo['ANNOAPERTURA'] = $DatiFascicolo['ANNO_FASCICOLO']; //
        $MetadatiFascicolo['ANNOCHIUSURA'] = substr($DatiFascicolo['DATACHIUSURA'], 0, 4);
        $MetadatiFascicolo['PROCAMM_CODICEPROCEDIMENTO'] = $codiceProcedimento;

        $MetadatiFascicolo['PROCAMM_DESCRIZIONE'] = $Anapra_rec['PRADES__1'];
        $MetadatiFascicolo['PROCAMM_RIFERIMENTINORMATIVI'] = ''; // cosa sono?
        $MetadatiFascicolo['PROCAMM_DESTINATARI'] = ''; //Da dove prendere
        $MetadatiFascicolo['PROCAMM_REGIMIABILITATIVI'] = ''; // non definito
        $MetadatiFascicolo['PROCAMM_TIPOLOGIA'] = $Anatip_rec['TIPDES'];
        $MetadatiFascicolo['PROCAMM_SETTOREATTIVITA'] = $Anaset_rec['SETDES'];
        $MetadatiFascicolo['CODICEIPA'] = $Anatsp_rec['TSPAMMIPA'];
        $MetadatiFascicolo['AMMINISTRAZIONIPARTECIPANTI'] = '';
        $MetadatiFascicolo['TEMPOMINIMODICONSERVAZIONE'] = '';
        $MetadatiFascicolo['ACCESSIBILITA'] = '';
        //
        $Orgccf = $DatiFascicolo['TITOLARIO'];
        $classificazione = '';
        if (strlen($Orgccf) === 4) {
            $classificazione = intval($Orgccf);
        } else if (strlen($Orgccf) === 8) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4));
        } else if (strlen($Orgccf) === 12) {
            $classificazione = intval(substr($Orgccf, 0, 4)) . '.' . intval(substr($Orgccf, 4, 4)) . '.' . intval(substr($Orgccf, 8, 4));
        }
        $MetadatiFascicolo['CLASSIFICAFASCICOLO'] = $classificazione;


        return $MetadatiFascicolo;
    }

    public function SalvaMetadatiFascicolo($MetadatiFascicolo, $AnaproFascicolo_rec) {
        /*
         *  Salvataggio dei Metadati
         */
        $TFonte = self::FONTE_DATI_PRATICAAMM;
        $rowidClasse = $AnaproFascicolo_rec['ROWID'];
        if (!$this->proLibTabDag->SalvataggioFonteTabdag('ANAPRO', $rowidClasse, $TFonte, $MetadatiFascicolo)) {
            $this->setErrCode(2);
            $this->setErrMessage("Errore in salvataggio TABDAG Fascicolo. " . $this->proLibTabDag->getErrMessage());
            return false;
        }

        return true;
    }

    public function SalvaMetadatiDocumento($MetadatiDocumento, $Anapro_rec) {
        /*
         *  Salvataggio dei Metadati
         */
        $TFonte = self::FONTE_DATI_DOCUMENTO;
        $rowidClasse = $Anapro_rec['ROWID'];
        if (!$this->proLibTabDag->SalvataggioFonteTabdag('ANAPRO', $rowidClasse, $TFonte, $MetadatiDocumento)) {
            $this->setErrCode(2);
            $this->setErrMessage("Errore in salvataggio TABDAG. " . $this->proLibTabDag->getErrMessage());
            return false;
        }

        return true;
    }

    public function AggiungiAllegatoAlDocumento($model, $Anapro_rec, $allegato, $gesnum, $tempPath) {
        $iteKey = $this->proLib->IteKeyGenerator($Anapro_rec['PRONUM'], '', date('Ymd'), $Anapro_rec['PROPAR']);
        if (!$iteKey) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->proLib->getErrMessage());
            return false;
        }

        $FileRel = array();
        if ($gesnum == $allegato['PASKEY']) {
            $FileRel['REL_CLASSE'] = proDocReader::REL_CLASSE_PROGES;
            $FileRel['REL_CHIAVE'] = $allegato['ROWID'];
            // $FileRel['REL_SHA256'] = $allegato['PASSHA2'];
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($gesnum, 0, 4), $gesnum, "PROGES");
        } else {
            $FileRel['REL_CLASSE'] = proDocReader::REL_CLASSE_PASSO;
            $FileRel['REL_CHIAVE'] = $allegato['ROWID'];
            //$FileRel['REL_SHA256'] = $allegato['PASSHA2'];
            $pramPath = $this->praLib->SetDirectoryPratiche(substr($allegato['PASKEY'], 0, 4), $allegato['PASKEY'], "PASSO", false);
        }
        /*
         * Calcolo Hash
         */
        $sorgFile = $pramPath . "/" . $allegato['PASFIL'];
        if (!$sorgFile) {
            $this->setErrCode(-1);
            $this->setErrMessage('File per il calcolo impronta non trovato: ' . $allegato['PASFIL']);
            return false;
        }
        $sha256 = hash_file('sha256', $sorgFile);
        $FileRel['REL_SHA256'] = $sha256;

        $ExtraDati = array();
        $ExtraDati['FILEREL'] = $FileRel;
        $randName = md5(rand() * time()) . "." . pathinfo($allegato['PASNAME'], PATHINFO_EXTENSION);
        $risultato = $this->proLibAllegati->AggiungiAllegato($model, $Anapro_rec, $randName, $allegato['PASNAME'], '', $ExtraDati);
        if (!$risultato) {
            $this->setErrCode(-1);
            $this->setErrMessage($this->proLibAllegati->getErrMessage());
            return false;
        }

        return $risultato;
    }

    public function ApriFascicolo($nameform, $gesnum) {
        $Proges_rec = $this->praLib->GetProges($gesnum, 'codice');
        itaLib::openForm('praGest');
        $modelAtto = itaModel::getInstance('praGest');
        $modelAtto->setEvent('openform');
        $modelAtto->setReturnModel($nameform);
        $modelAtto->parseEvent();
        $modelAtto->Dettaglio($Proges_rec['ROWID']);
        return true;
    }

    public function GetOggettoPratica($Gesnum) {
        $Proges_rec = $this->praLib->GetProges($Gesnum, 'codice');
        if (!$Proges_rec['GESOGG']) {
            $praLibVar = new praLibVariabili();
            $Filent_rec = $this->praLib->GetFilent(3);
            $praLibVar->setCodicePratica($Gesnum);
            $DescrizioneOggetto = $this->praLib->GetStringDecode($praLibVar->getVariabiliPratica(true)->getAllData(), $Filent_rec['FILVAL']);
        } else {
            $DescrizioneOggetto = $Proges_rec['GESOGG'];
        }
        return $DescrizioneOggetto;
    }

    /**
     * 
     * @param type $$Gesnum
     */
    public function CheckFascicoloConservato($Gesnum) {
        /*
         * Lettura proges pratica
         */
        $Proges_rec = $this->praLib->GetProges($Gesnum, 'codice');
        $GesKey = $Proges_rec['GESKEY'];
        if (!$GesKey) {
            return false;
        }
        $ProgesProt_rec = $this->proLib->GetProges($GesKey, 'geskey');
        /*
         * Estrazione I del fascicolo
         * Controllo se almeno uno conservato: il fascicolo è in conservazione.
         */
        $sql .= "SELECT * FROM ANAPRO WHERE ANAPRO.PROPAR = 'I' AND ANAPRO.PROFASKEY = '$GesKey' ";
        $Anapro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        foreach ($Anapro_tab as $Anapro_rec) {
            if ($this->proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR'])) {
                return true;
            }
        }
        return false;
    }

    public function CheckPraticaSenzaFascicoloArchivistico($Gesnum) {
        /*
         * Se non serve crearlo torna 
         */
        $Filent46 = $this->praLib->GetFilent(46);
        if (!$Filent46['FILVAL']) {
            return false;
        }

        //
        $Proges_rec = $this->praLib->GetProges($Gesnum, 'codice');
        if (!$Proges_rec) {
            return false;
        }

        /*
         * Se è già un fascicolo archivisitco
         */
        if ($Proges_rec['GESKEY']) {
            return false;
        }

        /*
         * Se la pratica non è chiusa verrà creato alla chiusura.
         * Non serve quindi controllare
         */
        if ($Proges_rec['GESDCH'] == '') {
            return false;
        }

        return true;
    }

    function GetClassificazioneConservazione($procedimento, $gesnum) {
        if ($gesnum) {
            $proges_rec = $this->praLib->GetProges($gesnum);
            if ($proges_rec['GESATT'] != 0) {   //attività
                $Anaatt_rec = $this->praLib->GetAnaatt($proges_rec['GESATT']);
            }
            if ($proges_rec['GESSTT'] != 0) { //settore commerciale
                $Anaset_rec = $this->praLib->GetAnaset($proges_rec['GESSTT']);
            }
            if ($proges_rec['GESTSP'] != 0) { //sportello on line
                $Anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
            }

            if ($Anaatt_rec['ATTCLACONS']) { //attività
                return $Anaatt_rec['ATTCLACONS'];
            }
            if ($Anaset_rec['SETCLACONS']) {  //settore
                return $Anaset_rec['SETCLACONS'];
            }
            if ($Anatsp_rec['TSPCLACONS']) { //sportello
                return $Anatsp_rec['TSPCLACONS'];
            }
        }
        if ($procedimento) {
            $Anapra_rec = $this->praLib->GetAnapra($procedimento); //procedimento
            if ($Anapra_rec['PRACLACONS'] != "") { //procedimento amministrativo
                return $Anapra_rec['PRACLACONS'];
            }
        }
    }

}
