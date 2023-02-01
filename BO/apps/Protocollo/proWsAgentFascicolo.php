<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft Srl
 * @license
 * @version    02.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php');
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');

class proWsAgentFascicolo extends wsModel {

    public $PROT_DB;
    public $proLib;
    public $proLibTitolario;
    public $proLibFascicolo;
    public $errCode;
    public $errMessage;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->proLibTitolario = new proLibTitolario();
            $this->proLibFascicolo = new proLibFascicolo();
            $this->PROT_DB = $this->proLib->getPROTDB();
        } catch (Exception $e) {
            
        }
    }

    function __destruct() {
        parent::__destruct();
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

    public function CreaFascicolo($dati = array()) {
        /**
         * Carico Librerie
         */
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();
        $proLibTitolario = new proLibTitolario();

        /**
         * Messaggio di ritorno base
         */
        $messageResult = array();
        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = '';
        $result = array();
        $result['messageResult'] = $messageResult;
        $msgWarning = '';

        /*
         * Controllo dei permessi di creazione di un fascicolo.
         */
        $permessiFascicolo = $proLibFascicolo->GetPermessiFascicoli();
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_CREAZIONE]) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Utente non abilitato alla creazione di Fascicoli.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * Controllo dei dati immessi.
         */
        /* Controllo titolario */
        if (!$dati['titolario']) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Titolario obbligatorio per la creazione di un fascicolo.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        // Controllo validità titolario.
        $codiceTitolario = $proLibTitolario->CheckTitolario($dati['titolario'], '');
        if (!$codiceTitolario) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $proLibTitolario->getErrMessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /* Controllo responsabile */
        if (!$dati['responsabile'] || !$dati['ufficioResponsabile']) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Responsabile obbligatorio. Indicare il codice e ufficio del responsabile.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        /* ControlloAssociazioneUtenteUfficio */
        if (!$proLib->ControlloAssociazioneUtenteUfficio($dati['responsabile'], $dati['ufficioResponsabile'], 'responsabile')) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $proLib->getErrMessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }
        /* Controllo la natura fascicolo */
        switch ($dati['natura']) {
            case '':
            case '1':
            case '2':
                break;
            default:
                $messageResult['tipoRisultato'] = 'Error';
//            $messageResult['descrizione'] = 'La Natura del fascicolo può valere: vuoto per Digitale, 1 per Cartaceo e 2 per Ibrido.';
                $messageResult['descrizione'] = 'Indicare una natura valida per il fascicolo.';
                $result['messageResult'] = $messageResult;
                return $result;
                break;
        }
        /* Ufficio Operatore riletto in caso sia vuoto: se manca do errore. */
        $ufficioOperatore = $dati['ufficioOperatore'];
        if (!$dati['ufficioOperatore']) {
            $ufficioOperatore = $proLib->GetUfficioUtentePredef();
        }
        if (!$ufficioOperatore) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Ufficio operatore mancante. Non è stato possibile ricavarlo dal profilo utente.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * Preparazione dei dati.
         */
//    $Titolario = $dati['titolario'];
        $Serie_rec = array();
        if ($dati['titolario']) {
            $Serie_rec['CODICE'] = $dati['codiceSerie'];
            $Serie_rec['PROGSERIE'] = $dati['progressivoSerie'];
        }
        //$codiceSerie, $progressivoSerie
        $VersioneCorrente = $proLib->GetTitolarioCorrente();
        $datiFascicolazione = array(
            "VERSIONE_T" => $VersioneCorrente,
            "TITOLARIO" => $codiceTitolario,
            'UFF' => $dati['ufficioResponsabile'],
            'RES' => $dati['responsabile'],
            'GESPROUFF' => $ufficioOperatore,
            'SERIE' => $Serie_rec,
            'DATI_ANAORG' => array('NATFAS' => $dati['natura'])
        );
        $descrizione = $dati['descrizione'];
        $codiceProcedimento = ''; // Per ora non serve..?
        $rowidFascicolo = $proLibFascicolo->creaFascicolo($this, $datiFascicolazione, $descrizione, $codiceProcedimento);
        if (!$rowidFascicolo) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = "Errore in creazione fascicolo. " . $proLibFascicolo->getErrMessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }
        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = 'Fascicolo creato con successo. ' . $msgWarning;
        $result['messageResult'] = $messageResult;
        $retDati = $this->GetDatiFascicolo($rowidFascicolo, 'rowid');
        $result['datiFascicolo'] = $retDati;
        return $result;
    }

    public function FascicolaProtocollo($annoProt = '', $numeroProt = '', $tipoProt = '', $codiceFascicolo = '', $codiceSottoFascicolo = '') {
        /**
         * Carico Librerie
         */
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();

        /**
         * Messaggio di ritorno base
         */
        $messageResult = array();
        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = '';
        $result = array();
        $result['messageResult'] = $messageResult;
        $msgWarning = '';

        /*
         * Controllo se sono presenti i dati del protocollo
         */
        if (!$annoProt || !$numeroProt || !$tipoProt) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Numero protocollo non completo.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        /*
         * Controllo se indicato il codice fascicolo.
         */
        if (!$codiceFascicolo) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Codice fascicolo mancante.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        // Allineo numero protocollo.
        $NumeroProtocollo = str_pad($annoProt, 4, '0', STR_PAD_RIGHT) . str_pad($numeroProt, 6, '0', STR_PAD_LEFT);

        /*
         *  Controllo accesso al protocollo.
         */
        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($proLib, 'codice', $NumeroProtocollo, $tipoProt);
        if (!$anaproctr_rec) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Il protocollo da fascicolare non è accessibile.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * Lettura del fascicolo
         */
        $proGes_rec = $proLib->GetProges($codiceFascicolo, 'geskey');
        if (!$proGes_rec) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Il Fascicolo indicato è inesistente.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        // fascicolo secure tab
        $anapro_fascicolo_secure_tab = proSoggetto::getSecureAnaproFromIdUtente($proLib, 'fascicolo', $codiceFascicolo);
        if (!$anapro_fascicolo_secure_tab) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Fascicolo non accessibile.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * Controllo se il fascicolo è chiuso:
         */
        if ($proGes_rec['GESDCH']) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Fascicolo Chiuso non è possibile procedere alla fascicolazione.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        $sottoFasc = $tipoSottoFasc = '';
        if ($codiceSottoFascicolo) {
            //PROSUBKEY
            $AnaproN_rec = $this->proLibFascicolo->GetSottofascicolo($codiceFascicolo, $codiceSottoFascicolo);
            if (!$AnaproN_rec) {
                $messageResult['tipoRisultato'] = 'Error';
                $messageResult['descrizione'] = 'Sottofascicolo inesistente.';
                $result['messageResult'] = $messageResult;
                return $result;
            }
            $sottoFasc = $AnaproN_rec['PRONUM'];
            $tipoSottoFasc = 'N';
        }
        /*
         * Controllo dei permessi di movimentazione nel fascicolo
         */
        $permessiFascicolo = $proLibFascicolo->GetPermessiFascicoli('', $proGes_rec['GESNUM'], 'gesnum', $sottoFasc, $tipoSottoFasc);
        if (!$permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_PROTOCOLLI]) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Utente non abilitato alla movimentazione di questo fascicolo.'; // O sottofascicolo?
            $result['messageResult'] = $messageResult;
            return $result;
        }
        /*
         *  Aggiunta a fascicolo.
         */
        /* Ricavo anapro del fascicolo. */
        $anapro_fascicolo = $proLibFascicolo->getAnaproFascicolo($proGes_rec['GESKEY']);
        $pronumR = $anapro_fascicolo['PRONUM'];
        $proparR = $anapro_fascicolo['PROPAR'];
        if ($codiceSottoFascicolo) {
            $pronumR = $sottoFasc;
            $proparR = $tipoSottoFasc;
        }

        if (!$proLibFascicolo->insertDocumentoFascicolo($this, $proGes_rec['GESKEY'], $NumeroProtocollo, $tipoProt, $pronumR, $proparR)) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Errore in aggiunta protocollo in fascicolo. ' . $proLibFascicolo->getErrMessage(); // O sottofascicolo?
            $result['messageResult'] = $messageResult;
            return $result;
        }

        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = 'Protocollo fascicolato correttamente.' . $msgWarning;
        $result['messageResult'] = $messageResult;
        // Potrebbe andre in una funzione..
        $result['retDatiFascicolo']['codiceProtocollo'] = $NumeroProtocollo . '-' . $tipoProt;
        $result['retDatiFascicolo']['codiceFascicolo'] = $proGes_rec['GESKEY'];
        $result['retDatiFascicolo']['codiceSottoFascicolo'] = $sottoFasc;
        return $result;
    }

    public function GetFascicoliProtocollo($annoProt = '', $numeroProt = '', $tipoProt = '') {
        /**
         * Carico Librerie
         */
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();

        /**
         * Messaggio di ritorno base
         */
        $messageResult = array();
        $messageResult['tipoRisultato'] = 'Info';
        $messageResult['descrizione'] = '';
        $result = array();
        $result['messageResult'] = $messageResult;
        $msgWarning = '';

        /*
         * Controllo se sono presenti i dati del protocollo
         */
        if (!$annoProt || !$numeroProt || !$tipoProt) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Numero protocollo non completo.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        // Allineo numero protocollo.
        $NumeroProtocollo = str_pad($annoProt, 4, '0', STR_PAD_RIGHT) . str_pad($numeroProt, 6, '0', STR_PAD_LEFT);

        /*
         *  Controllo accesso al protocollo.
         */
        $anaproctr_rec = proSoggetto::getSecureAnaproFromIdUtente($proLib, 'codice', $NumeroProtocollo, $tipoProt);
        if (!$anaproctr_rec) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Il protocollo indicato non è accessibile.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        $anapro_rec = $proLib->GetAnapro($NumeroProtocollo, 'codice', $tipoProt);
        $ElencoFascicoli = $proLibFascicolo->CaricaFascicoliProtocollo($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $anapro_rec['PROFASKEY']);
        $retDatiFascicoli = array();
        foreach ($ElencoFascicoli as $key => $Fascicolo) {
            $DatiFascicolo = array();
            $DatiFascicolo['codiceFascicolo'] = $Fascicolo['ORGKEY'];
            $DatiFascicolo['descrizioneFascicolo'] = $Fascicolo['ORGDES'];
            $DatiFascicolo['titolario'] = $Fascicolo['ORGCCF'];
            $DatiFascicolo['codiceSottofascicolo'] = $Fascicolo['CODICE_SOTTOFAS'];
            $DatiFascicolo['descrizioneSottofascicolo'] = $Fascicolo['OGGETTO_SOTTOFAS'];
            $Principale = 0;
            if ($anapro_rec['PROFASKEY'] == $Fascicolo['ORGKEY']) {
                $Principale = 1;
            }
            $DatiFascicolo['principale'] = $Principale;
            $retDatiFascicoli['ElencoFascicoli'][$key] = $DatiFascicolo;
        }
        return $retDatiFascicoli;
    }

    // Esempio.. da usare
    public function ElaboraElencoFascicoli($ElencoFascicoli = array()) {
        if (!$ElencoFascicoli) {
            return false;
        }
        $dati = array();
        foreach ($ElencoFascicoli as $key => $Fascicolo) {
            $retDati = $this->GetDatiFascicolo($Fascicolo['ORGKEY'], 'geskey');
            $dati['ElencoFascicoli'][$key] = $retDati;
        }

        return $dati;
    }

    public function GetDatiFascicolo($chiaveFascicolo, $tipoChiave = 'geskey') {
        $proges_rec = $this->proLib->GetProges($chiaveFascicolo, $tipoChiave);
        $anaorg_rec = $this->proLib->GetAnaorg($proges_rec['GESKEY'], 'orgkey');
        $Titolario = $this->proLibTitolario->DecodTitolario($anaorg_rec['ORGCCF']);
        $anamedResp = $this->proLib->GetAnamed($proges_rec['GESRES'], 'codice');
        $anauff_rec = $this->proLib->GetAnauff($proges_rec['GESUFFRES'], 'codice');
        $anapro_fascicolo = $this->proLibFascicolo->getAnaproFascicolo($proges_rec['GESKEY']);

// Potrebbe andre in una funzione..
        $retDati = array();
        $retDati['codiceFascicolo'] = $proges_rec['GESKEY'];
        if ($anapro_fascicolo['PRORISERVA']) {
            $retDati['descrizioneFascicolo'] = 'RISERVATO';
        } else {
            $retDati['descrizioneFascicolo'] = $proges_rec['GESOGG'];
        }
        $retDati['titolario'] = $Titolario['classificazione'];
        $retDati['titolarioDescrizione'] = $Titolario['classificazione_Descrizione'];
        $retDati['dataFascicolo'] = date("d/m/Y", strtotime($proges_rec['GESDRE'])); // serve formattarla?
        $retDati['dataChiusuraFascicolo'] = '';
        if ($proges_rec['GESDCH']) {
            $retDati['dataChiusuraFascicolo'] = date("d/m/Y", strtotime($proges_rec['GESDCH'])); // serve formattarla?
        }
        $retDati['responsabile'] = $proges_rec['GESRES'];
        $retDati['nomeResponsabile'] = $anamedResp['MEDNOM'];
        $retDati['ufficioResopnsabile'] = $proges_rec['GESUFFRES'];
        $retDati['descUfficioResponsabile'] = $anauff_rec['UFFDES'];
        $retDati['naturaFascicolo'] = $anaorg_rec['NATFAS'] . ' - ' . $this->DecodNaturaFascicolo($anaorg_rec['NATFAS']);
        $retDati['codiceSerie'] = $anaorg_rec['CODSERIE'];
        $retDati['progressivoSerie'] = $anaorg_rec['PROGSERIE'];
        $retDati['segnatura'] = $anaorg_rec['ORGSEG'];
        $retDatiSottofas = $this->GetDatiSottofascicolo($anapro_fascicolo['PRONUM'], $anapro_fascicolo['PROPAR']);
        $retDati['sottoFascicoli'] = $retDatiSottofas;
        return $retDati;
    }

    public function GetDatiSottofascicolo($Pronumparent, $proparparent) {
        $ElencoSottoFascicoli = $this->proLibFascicolo->GetOrgconParent($Pronumparent, $proparparent, 'N');
        $retDatiSottofas = array();
        foreach ($ElencoSottoFascicoli as $key => $sottofascicolo) {
            $AnaproN_rec = $this->proLib->GetAnapro($sottofascicolo['PRONUM'], 'codice', $sottofascicolo['PROPAR']);
            $Anaogg_rec = $this->proLib->GetAnaogg($sottofascicolo['PRONUM'], $sottofascicolo['PROPAR']);
            $retDatiSottofas[$key]['codiceSottofascicolo'] = $AnaproN_rec['PROSUBKEY'];
            $retDatiSottofas[$key]['descrizioneSottofascicolo'] = $Anaogg_rec['OGGOGG'];
            $TestSottoFascicoli = $this->proLibFascicolo->GetOrgconParent($AnaproN_rec['PRONUM'], $AnaproN_rec['PROPAR'], 'N');
            $Sottofascicoli = array();
            if ($TestSottoFascicoli) {
                $Sottofascicoli = $this->GetDatiSottofascicolo($AnaproN_rec['PRONUM'], $AnaproN_rec['PROPAR']);
            }
            $retDatiSottofas[$key]['sottoFascicoli'] = $Sottofascicoli;
        }

        return $retDatiSottofas;
    }

    public function GetElencoFascicoli($ParamRicerca = array()) {
        // Serve solo orgkey
        //  
        // Qui controllo parametri di ricerca:
        if (!$this->CheckParamRicerca($ParamRicerca)) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $this->getErrMessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }

        $sql = $this->proLibFascicolo->CreaSqlVisibilitaFascicoli($ParamRicerca);
        $ElencoFascicoli = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        if (!$ElencoFascicoli) {
            $messageResult['tipoRisultato'] = 'Info';
            $messageResult['descrizione'] = 'Nessun fascicolo trovato con i parametri di ricerca impostati.';
            $result['messageResult'] = $messageResult;
            return $result;
        }
        $retDatiFascicoli = $this->ElaboraElencoFascicoli($ElencoFascicoli);
        return $retDatiFascicoli;
    }

    public function CheckParamRicerca(&$ParamRicerca) {
        if ($ParamRicerca['TITOLARIO']) {
            $codiceTitolario = $this->proLibTitolario->CheckTitolario($ParamRicerca['TITOLARIO'], '');
            if (!$codiceTitolario) {
                $this->setErrMessage($this->proLibTitolario->getErrMessage());
                return false;
            }
            $ParamRicerca['TITOLARIO'] = $codiceTitolario;
        }

        return true;
    }

    public function DecodNaturaFascicolo($Natura) {
        $DescNatura = '';
        switch ($Natura) {
            case '1':
                $DescNatura = 'Cartaceo';
                break;
            case '2':
                $DescNatura = 'Ibrido';
                break;

            default:
                $DescNatura = 'Digitale';
                break;
        }
        return $DescNatura;
    }

}

?>