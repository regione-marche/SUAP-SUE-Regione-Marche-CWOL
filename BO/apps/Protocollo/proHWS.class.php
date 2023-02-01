<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    15.06.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
//include_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaHWSClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaCercaRubrica.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaRubrica.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaProtocolloIngresso.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaProtocolloUscita.class.php');
//include_once(ITA_LIB_PATH . '/itaPHPHWS/itaCercaDocumentoProtocollo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/itaCercaDocumentoHWS.class.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/lib/dump.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/lib/utility.php');
include_once(ITA_LIB_PATH . '/itaPHPHWS/lib/class.csoapclient.php');
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proHWS extends proWsClient{

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Halley- ICCS
     */
    private $param = array();
    public $praLib;
    public $accLib;
    private $HWScodiceOperatore;
    private $HWSaoo;
    private $HWScodiceSpedizione;
    private $JDBC;

    function __construct() {
        $this->praLib = new praLib();
    }

    private function setClientConfig($HWSClient) {
        $devLib = new devLib();
        //$envConfig_rec=$this->devLib->getEnv_confing('PALEOWSCONNECTION','codice','WSPALEOENDPOINT', false);
        $uri = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $HWSClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHWSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $HWSClient->setWebservices_wsdl($wsdl2);
        $username = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHUTENTE', false);
        $username2 = $username['CONFIG'];
        $password = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHPASSWORD', false);
        $password2 = $password['CONFIG'];
        $HWSClient->setUsername($username2);
        $HWSClient->setpassword($password2);
        //settaggio parametri operatore che effettua la chiamata a WS
        $this->HWScodiceOperatore = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHCODICEOPERATORE', false);
        $this->HWScodiceOperatore = $this->HWScodiceOperatore['CONFIG'];
        $this->HWSaoo = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHAOO', false);
        $this->HWSaoo = $this->HWSaoo['CONFIG'];
        $this->HWScodiceSpedizione = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHSPEDIZIONE', false);
        $this->HWScodiceSpedizione = $this->HWScodiceSpedizione['CONFIG'];
        $this->JDBC = $devLib->getEnv_config('HWSCONNECTION', 'codice', 'WSHJDBC', false);
        $this->JDBC = $this->JDBC['CONFIG'];
    }

    public function protocollazioneIngresso($elementi) {

        /*
         * controlli preliminari
         * non esegue il controllo su DataArrivo perchè può essere una protocollazione in arrivo di una comunicazione
         * senza che sia stata protocollata la pratica.
         * In caso di dato fondamentale mancante viene gestito il generico messaggio di errore del Web Service.
         */
//        if (!isset($elementi['dati']['DataArrivo']) || $elementi['dati']['DataArrivo'] == '') {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "Attenzione!\nNon è stata valorizzata la data di ricezione!";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }


        /*
         * acquisizione elementi
         */
        $anno = date("Y");
        isset($elementi['dati']['Oggetto']) ? $oggetto = $elementi['dati']['Oggetto'] : $oggetto = "";
        $classificazione = $elementi['dati']['Classificazione'];
        isset($elementi['dati']['fascicoli']) ? $fascicoli = $elementi['dati']['fascicoli'] : $fascicoli = array();
        isset($elementi['dati']['codiceSpedizione']) ? $codiceSpedizione = $elementi['dati']['codiceSpedizione'] : $codiceSpedizione = "0";
        isset($elementi['dati']['statoProtocollo']) ? $statoProtocollo = $elementi['dati']['statoProtocollo'] : $statoProtocollo = "1";
        isset($elementi['dati']['protocolloRiscontro']) ? $protocolloRiscontro = $elementi['dati']['protocolloRiscontro'] : $protocolloRiscontro = "0";
        $dataScadenza = soapDateTime(strtotime($elementi['dati']['dataScadenza']));
        isset($elementi['dati']['protocolloCollegato']) ? $protocolloCollegato = $elementi['dati']['protocolloCollegato'] : $protocolloCollegato = "0";
        $accesso = '1'; //1 = accesso per tutti
        isset($elementi['dati']['note']) ? $note = $elementi['dati']['note'] : $note = "";
        isset($elementi['dati']['protocolloEmergenza']) ? $protocolloEmergenza = $elementi['dati']['protocolloEmergenza'] : $protocolloEmergenza = "0";
        isset($elementi['dati']['segnatura']) ? $segnatura = $elementi['dati']['segnatura'] : $segnatura = "";
        isset($elementi['dati']['comunicazioneInterna']) ? $comunicazioneInterna = $elementi['dati']['comunicazioneInterna'] : $comunicazioneInterna = "0";
        isset($elementi['dati']['numeroProtocolloMittente']) ? $numeroProtocolloMittente = $elementi['dati']['numeroProtocolloMittente'] : $numeroProtocolloMittente = "";
        isset($elementi['dati']['casellaMittente']) ? $casellaMittente = $elementi['dati']['casellaMittente'] : $casellaMittente = "";
        ($elementi['dati']['corrispondente']) ? $corrispondente = $elementi['dati']['corrispondente'] : $corrispondente = '0';
        isset($elementi['dati']['ufficio']) ? $ufficio = $elementi['dati']['ufficio'] : $ufficio = ""; //valore di prova, poi deve essere parametrizzato

        $documentoPrincipale = $elementi['dati']['Principale'];
        $allegati = $elementi['dati']['Allegati'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri per la protocollazione
         */
        $HWSClient = new itaHWSClient();
        $this->setClientConfig($HWSClient);
        /**
         * scelta del corrispondente
         */
//        $CercaRubrica = new itaCercaRubrica();
//        $CercaRubrica->setDescrizione($DenominazioneMittente);
//        $CercaRubrica->setIdFiscale($CFMittente);
//        $ret = $HWSClient->ws_CercaRubrica($CercaRubrica);
//        if (!$ret) {
//            if ($HWSClient->getFault()) {
//                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($HWSClient->getFault(), true) . '</pre>');
//            } elseif ($HWSClient->getError()) {
//                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($HWSClient->getError(), true) . '</pre>');
//            }
//            return;
//        }
//        $risultato = $HWSClient->getResult();
//        $risultato = $this->objectToArray($risultato);
//        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
//        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
//        //gestione del messaggio d'errore
//        if (!$TipoRisultato) {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $HWSClient->getError() . "";
//            $ritorno["RetValue"] = false;
//            $trovato = false;
//        } elseif ($TipoRisultato == "Error") {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
//            $ritorno["RetValue"] = false;
//            $trovato = false;
//        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
//            if ($risultato['return']['items']) {
//                $items = array();
//                app::log('risultato della ricerca da ws');
//                app::log($risultato['return']);
//                $trovato = true;
//                if ($risultato['return']['items'][0]) {
//                    $items = $risultato['return']['items'];
//                } else {
//                    $items[0] = $risultato['return']['items'];
//                }
//            } else {
//                Out::msgInfo("Attenzione", "Nessun record trovato");
//                $trovato = false;
//            }
//        } else {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br><pre>" . $risultato . "</pre>";
//            $ritorno["RetValue"] = false;
//        }
//        if ($trovato) {
//            //inserisco utiRic per visualizzare le anagrafiche
//            Out::msgInfo("Selezione Anagrafica", print_r($items, true));
//        }
//
        //isset($elementi['dati']['corrispondente']) ? $corrispondente = $elementi['dati']['corrispondente'] : $corrispondente = '0';
        /**
         * fine scelta corrispondente
         */
        $ProtocolloIngresso = new itaProtocolloIngresso();
        $ProtocolloIngresso->setJDBC($this->JDBC);
        $ProtocolloIngresso->setCodice('0');
        $ProtocolloIngresso->setAoo($this->HWSaoo);
        $ProtocolloIngresso->setAnno($anno);
        $ProtocolloIngresso->setNumero('0');
        $ProtocolloIngresso->setTipo('1');  //1=ingresso
        if (isset($elementi['dati']['dataRegistrazione'])) {
            $ProtocolloIngresso->setDataRegistrazione(soapDateTime(strtotime($elementi['dati']['dataRegistrazione'])));
        } else {
            $ProtocolloIngresso->setDataRegistrazione(soapDateTime(time())); //la dataRegistrazione la inserisco in ogni caso in questo modo
        }
        $ProtocolloIngresso->setOggetto($oggetto);
        $ProtocolloIngresso->setClassificazione($classificazione);
        //settaggi Classificazione
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'] != '') {
            $CercaDocumentoProtocollo = new itaCercaDocumentoHWS();
            $CercaDocumentoProtocollo->setJDBC($this->JDBC);
            //$numeroProtocollo = substr($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'], 4);
            $numeroProtocollo = $elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'];
            $CercaDocumentoProtocollo->setNumeroDocumento($numeroProtocollo);
            $CercaDocumentoProtocollo->setAnnoCompetenza($elementi['dati']['MetaDati']['DatiProtocollazione']['Anno']['value']);
            $ret = $HWSClient->ws_CercaDocumentoProtocollo($CercaDocumentoProtocollo);
            if (!$ret) {
                if ($HWSClient->getFault()) {
                    $msg = $HWSClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il protocollo: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($HWSClient->getError()) {
                    $msg = $HWSClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il protocollo: <br>" . print_r($msg, true) . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $HWSClient->getResult();
            $risultato = $this->objectToArray($risultato);
            $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
            $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il protocollo " . $numeroProtocollo . ": <br>" . print_r($risultato, true) . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca protocollo avvenuta con successo!";
                $ritorno["RetValue"] = $risultato['return']['fascicoli']['idFascicolo'];
            }
            if ($ritorno['Status'] == "0") {
                $idFascicolo = $ritorno["RetValue"];
            } else {
                return $ritorno;
            }
            //$CodiceFascicolo=$risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
        } else {
            App::log('da prohws');
            App::log($elementi);
            if ($elementi['dati']['Classificazione'] != '') {
                $NuovoFascicolo['idClassificazione'] = $elementi['dati']['Classificazione'];
            } else {
                Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
                return;
                //$NuovoFascicolo['CodiceClassifica']="1.2"; //se non è settato ne metto uno di default
            }
            $NuovoFascicolo['idAOO'] = $this->HWSaoo;
            $NuovoFascicolo['annoRiferimento'] = '';
            //$NuovoFascicolo['dataApertura'] = '';
            $NuovoFascicolo['dataApertura'] = date("Y-m-d");
            $NuovoFascicolo['dataChiusura'] = '';
            //$NuovoFascicolo['descrizioneFascicolo'] = "Fascicolo Da WS PU";
            $NuovoFascicolo['descrizioneFascicolo'] = $oggetto;
            $NuovoFascicolo['note'] = "";
            $NuovoFascicolo['consultazionePubblica'] = "1";
            $NuovoFascicolo['statoProtocollo'] = "1";
            $NuovoFascicolo['responsabileProcedimento'] = $this->HWScodiceOperatore;
            $NuovoFascicolo['tempoConservazione'] = '';
            $idFascicolo = "";
        }
        //fine settaggio fascicoli
        if ($idFascicolo != '') {
            $ProtocolloIngresso->setFascicoli(array('idFascicolo' => $idFascicolo));
        } else {
            $ProtocolloIngresso->setFascicoli(array('nuovoFascicolo' => $NuovoFascicolo));
        }

        //$ProtocolloIngresso->setCodiceSpedizione($codiceSpedizione);
        if ($this->HWScodiceSpedizione) {
            $ProtocolloIngresso->setCodiceSpedizione($this->HWScodiceSpedizione);
        } else {
            $ProtocolloIngresso->setCodiceSpedizione($codiceSpedizione);
        }
        $ProtocolloIngresso->setStatoProtocollo($statoProtocollo);
        $ProtocolloIngresso->setProtocolloRiscontro($protocolloRiscontro);
        if (isset($elementi['dati']['dataScadenza'])) {
            //$ProtocolloIngresso->setDataScadenza(soapDateTime(strtotime($elementi['dati']['dataScadenza'])));
            $ProtocolloIngresso->setDataScadenza(date("Y-m-d", strtotime($elementi['dati']['DataScadenza'])));
        }
        $ProtocolloIngresso->setStatoPratica('0'); //0=aperta, 1=chiusa
        $ProtocolloIngresso->setCodiceOperatore($this->HWScodiceOperatore);
        $ProtocolloIngresso->setProtocolloCollegato($protocolloCollegato);
        $ProtocolloIngresso->setAccesso($accesso);
        $ProtocolloIngresso->setNote($note);
        $ProtocolloIngresso->setProtocolloEmergenza($protocolloEmergenza);
        $ProtocolloIngresso->setSegnatura($segnatura);
        $ProtocolloIngresso->setComunicazioneInterna($comunicazioneInterna);
        if (isset($elementi['dati']['dataDocumento'])) {
            $ProtocolloIngresso->setDataDocumento(soapDateTime(strtotime($elementi['dati']['dataDocumento'])));
        }
        $ProtocolloIngresso->setFlagCartaceo('0'); //per fascicoli elettronici è sempre 0
        $ProtocolloIngresso->setFlagInArchivio('0'); //se sto protocollando una nuova pratica la considero sempre come nuovo documento e quindi non presente in archivio
        if (isset($elementi['dati']['DataArrivo'])) {
            $ProtocolloIngresso->setDataArrivo(soapDateTime(strtotime($elementi['dati']['DataArrivo'])));
        }
        if (isset($elementi['dati']['dataProtocolloMittente'])) {
            $ProtocolloIngresso->setDataProtocolloMittente(soapDateTime(strtotime($elementi['dati']['dataProtocolloMittente'])));
        }
        $ProtocolloIngresso->setNumeroProtocolloMittente($numeroProtocolloMittente);
        $ProtocolloIngresso->setCasellaMittente($casellaMittente);
        $ProtocolloIngresso->setCorrispondente($corrispondente);
        $ProtocolloIngresso->setUfficio($ufficio);
        if (isset($elementi['dati']['Principale'])) {
            //modifica per passare anche il parametro 'firmaDigitale'
            if (strtolower($elementi['dati']['Principale']['estensione']) == 'p7m') {
                $elementi['dati']['Principale']['firmaDigitale'] = '1'; //da verificare se va bene '1' oppure necessita di true
            } else {
                $elementi['dati']['Principale']['firmaDigitale'] = '0';
            }
            $ProtocolloIngresso->setDocumentoPrincipale($elementi['dati']['Principale']);
        }
        if (isset($elementi['dati']['Allegati'])) {
            //modifica per passare anche il parametro 'firmaDigitale'
            foreach ($elementi['dati']['Allegati'] as $key => $Allegato) {
                if (strtolower($Allegato['estensione']) == 'p7m') {
                    $elementi['dati']['Allegati'][$key]['firmaDigitale'] = '1';
                } else {
                    $elementi['dati']['Allegati'][$key]['firmaDigitale'] = '0';
                }
            }
            $ProtocolloIngresso->setAllegati($elementi['dati']['Allegati']);
        }
        /**
         * fine settaggio parametri
         */
        $ret = $HWSClient->ws_ProtocollazioneIngresso($ProtocolloIngresso);
        //if (!$ret) {
        if ($HWSClient->getFault()) {
            $msg = $HWSClient->getFault();
            $ritorno["Status"] = "-1";
            //$ritorno["Message"]="(Fault) Rilevato un errore in fase di protocollazione: <br>".$msg['!']."";
            $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } elseif ($HWSClient->getError()) {
            $msg = $HWSClient->getError();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
//            break;
//        }

        $risultato = $HWSClient->getResult();
        $risultato = $this->objectToArray($risultato);
        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if (!$TipoRisultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $HWSClient->getError() . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
            $Data = soapDateTime(time());
            //$proNum = substr($Data, 0, 4) . $risultato['return']['numeroProtocollo'];
            $proNum = $risultato['return']['numeroProtocollo']; // non più usato
            $codiceProtocollo = $risultato['return']['codiceProtocollo'];
            $Anno = substr($Data, 0, 4);
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'WSPU', 'status' => true, 'msg' => 'ProtocollazioneIngresso'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'codiceProtocollo' => array('value' => $codiceProtocollo, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br><pre>" . print_r($risultato, true) . "</pre>";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }
    
    // TODO mettere privato
    public function protocollazioneUscita($elementi) {
        App::log('entro proHWS protocollazioneUscita');

        /*
         * controlli preliminari
         */
//        if (!isset($elementi['dati']['DataArrivo']) || $elementi['dati']['DataArrivo'] == '') {
//            $ritorno["Status"] = "-1";
//            $ritorno["Message"] = "Attenzione!\nNon è stata valorizzata la data di ricezione!";
//            $ritorno["RetValue"] = false;
//            return $ritorno;
//        }

        /*
         * acquisizione elementi
         */
        //$codice = $elementi['dati']['codice']; //sempre 0
        //$aoo = $elementi['dati']['aoo'];
        //$anno = $elementi['dati']['anno'];
        $anno = date("Y");
        //$numero = $elementi['dati']['numero']; //sempre 0
        //$tipo = '1';//$elementi['dati']['tipo']; //1=ingresso, 2=uscita
        //($elementi['dati']['dataRegistrazione']!="") ? $dataRegistrazione=soapDateTime(strtotime($elementi['dati']['dataRegistrazione'])) : $dataRegistrazione=soapDateTime(time());
        //$dataRegistrazione = $elementi['dati']['dataRegistrazione'];
        isset($elementi['dati']['Oggetto']) ? $oggetto = $elementi['dati']['Oggetto'] : $oggetto = "";
        $classificazione = $elementi['dati']['Classificazione'];
        isset($elementi['dati']['fascicoli']) ? $fascicoli = $elementi['dati']['fascicoli'] : $fascicoli = "";
        isset($elementi['dati']['codiceSpedizione']) ? $codiceSpedizione = $elementi['dati']['codiceSpedizione'] : $codiceSpedizione = "8";
        isset($elementi['dati']['statoProtocollo']) ? $statoProtocollo = $elementi['dati']['statoProtocollo'] : $statoProtocollo = "1";
        isset($elementi['dati']['protocolloRiscontro']) ? $protocolloRiscontro = $elementi['dati']['protocolloRiscontro'] : $protocolloRiscontro = "0";
        $dataScadenza = soapDateTime(strtotime($elementi['dati']['dataScadenza']));
        //$statoPratica = $elementi['dati']['statoPratica'];
        //$codiceOperatore = $elementi['dati']['codiceOperatore'];
        isset($elementi['dati']['protocolloCollegato']) ? $protocolloCollegato = $elementi['dati']['protocolloCollegato'] : $protocolloCollegato = "0";
        $accesso = '1'; //1 = accesso per tutti
        isset($elementi['dati']['note']) ? $note = $elementi['dati']['note'] : $note = "";
        //$protocolloEmergenza = $elementi['dati']['protocolloEmergenza'];
        isset($elementi['dati']['protocolloEmergenza']) ? $protocolloEmergenza = $elementi['dati']['protocolloEmergenza'] : $protocolloEmergenza = "0";
        isset($elementi['dati']['segnatura']) ? $segnatura = $elementi['dati']['segnatura'] : $segnatura = "";
        isset($elementi['dati']['comunicazioneInterna']) ? $comunicazioneInterna = $elementi['dati']['comunicazioneInterna'] : $comunicazioneInterna = "0";
        //$dataDocumento = soapDateTime(strtotime($elementi['dati']['dataDocumento']));
        //$flagCartaceo = $elementi['dati']['flagCartaceo'];
        //$flagInArchivio = $elementi['dati']['flagInArchivio'];
        //$dataArrivo = $elementi['dati']['dataArrivo'];
        //$dataProtocolloMittente = soapDateTime(strtotime($elementi['dati']['dataProtocolloMittente']));
        isset($elementi['dati']['numeroProtocolloMittente']) ? $numeroProtocolloMittente = $elementi['dati']['numeroProtocolloMittente'] : $numeroProtocolloMittente = "";
        isset($elementi['dati']['casellaMittente']) ? $casellaMittente = $elementi['dati']['casellaMittente'] : $casellaMittente = "";
        //isset($elementi['dati']['corrispondente']) ? $corrispondente = $elementi['dati']['corrispondente'] : $corrispondente = $this->HWScodiceOperatore;

        /**
         * scelta del corrispondente
         */
        //$CognomeNome = explode(" ", $elementi['dati']['MittDest']['Denominazione']);
        isset($elementi['dati']['corrispondente']) ? $corrispondente = $elementi['dati']['corrispondente'] : $corrispondente = '0';
        /**
         * fine scelta corrispondente
         */
        isset($elementi['dati']['ufficio']) ? $ufficio = $elementi['dati']['ufficio'] : $ufficio = "";
        $documentoPrincipale = $elementi['dati']['Principale'];
        $allegati = $elementi['dati']['Allegati'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri per la protocollazione
         */
        $HWSClient = new itaHWSClient();
        $this->setClientConfig($HWSClient);
        $ProtocolloUscita = new itaProtocolloUscita();
        $ProtocolloUscita->setJDBC($this->JDBC);
        $ProtocolloUscita->setCodice('0');
        $ProtocolloUscita->setAoo($this->HWSaoo);
        $ProtocolloUscita->setAnno($anno);
        $ProtocolloUscita->setNumero('0');
        $ProtocolloUscita->setTipo('2');  //1=ingresso
        if (isset($elementi['dati']['dataRegistrazione'])) {
            $ProtocolloUscita->setDataRegistrazione(soapDateTime(strtotime($elementi['dati']['dataRegistrazione'])));
        } else {
            $ProtocolloUscita->setDataRegistrazione(soapDateTime(time())); //la dataRegistrazione la inserisco in ogni caso in questo modo
        }
        //$ProtocolloIngresso->setDataRegistrazione($_POST[$this->name_form . '_ProtocolloIngresso_dataRegistarzione']);
        $ProtocolloUscita->setOggetto($oggetto);
        $ProtocolloUscita->setClassificazione($classificazione);
        //settaggi Fascicoli
//        if ($fascicoli != "") {
//            if (!isset($fascicoli['idFascicolo']) && isset($fascicoli['idAOO'])) {
//                $Fascicoli = array(
//                    "nuovoFascicolo" => $fascicoli
//                );
//            } else {
//                $Fascicoli = array(
//                    "idFascicolo" => $_POST[$this->name_form . '_Fascicoli_idFascicolo']
//                );
//            }
//
//            $ProtocolloUscita->setFascicoli($Fascicoli);
//        }
        //settaggi Classificazione
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'] != '') {
            $CercaDocumentoProtocollo = new itaCercaDocumentoHWS();
            $CercaDocumentoProtocollo->setJDBC($this->JDBC);
            //$numeroProtocollo = substr($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'], 4);
            $numeroProtocollo = $elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'];
            $CercaDocumentoProtocollo->setNumeroDocumento($numeroProtocollo);
            //$CercaDocumentoProtocollo->setNumeroDocumento($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value']);
            $CercaDocumentoProtocollo->setAnnoCompetenza($elementi['dati']['MetaDati']['DatiProtocollazione']['Anno']['value']);
            $ret = $HWSClient->ws_CercaDocumentoProtocollo($CercaDocumentoProtocollo);
            if (!$ret) {
                if ($HWSClient->getFault()) {
                    $msg = $HWSClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il protocollo " . $numeroProtocollo . ": <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($HWSClient->getError()) {
                    $msg = $HWSClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il protocollo " . $numeroProtocollo . ": <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $HWSClient->getResult();
            $risultato = $this->objectToArray($risultato);
            $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
            $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il protocollo " . $numeroProtocollo . ": <br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca protocollo avvenuta con successo!";
                $ritorno["RetValue"] = $risultato['return']['fascicoli']['idFascicolo'];
            }
            if ($ritorno['Status'] == "0") {
                $idFascicolo = $ritorno["RetValue"];
            } else {
                return $ritorno;
            }
            //$CodiceFascicolo=$risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
        } else {
            if ($elementi['dati']['Classificazione'] != '') {
                $NuovoFascicolo['idClassificazione'] = $elementi['dati']['Classificazione'];
            } else {
                Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
                return;
                //$NuovoFascicolo['CodiceClassifica']="1.2"; //se non è settato ne metto uno di default
            }
            $NuovoFascicolo['idAOO'] = $this->HWSaoo;
            $NuovoFascicolo['annoRiferimento'] = '';
            $NuovoFascicolo['dataApertura'] = '';
            //$NuovoFascicolo['dataApertura'] = date("Y-m-d");
            $NuovoFascicolo['dataChiusura'] = '';
            //$NuovoFascicolo['descrizioneFascicolo'] = "Fascicolo Da WS PU";
            $NuovoFascicolo['descrizioneFascicolo'] = $oggetto;
            $NuovoFascicolo['note'] = "";
            $NuovoFascicolo['consultazionePubblica'] = "1";
            $NuovoFascicolo['statoProtocollo'] = "1";
            $NuovoFascicolo['responsabileProcedimento'] = $this->HWScodiceOperatore;
            $NuovoFascicolo['tempoConservazione'] = '';
            $idFascicolo = "";
        }
        if ($idFascicolo != '') {
            $ProtocolloUscita->setFascicoli(array('idFascicolo' => $idFascicolo));
        } else {
            $ProtocolloUscita->setFascicoli(array('nuovoFascicolo' => $NuovoFascicolo));
        }

        //$ProtocolloUscita->setCodiceSpedizione($codiceSpedizione);
        if ($this->HWScodiceSpedizione) {
            $ProtocolloUscita->setCodiceSpedizione($this->HWScodiceSpedizione);
        } else {
            $ProtocolloUscita->setCodiceSpedizione($codiceSpedizione);
        }

        $ProtocolloUscita->setStatoProtocollo($statoProtocollo);
        $ProtocolloUscita->setProtocolloRiscontro($protocolloRiscontro);
        if (isset($elementi['dati']['dataScadenza'])) {
            //$ProtocolloIngresso->setDataScadenza(soapDateTime(strtotime($elementi['dati']['dataScadenza'])));
            $ProtocolloUscita->setDataScadenza(date("Y-m-d", strtotime($elementi['dati']['DataScadenza'])));
        }
        $ProtocolloUscita->setStatoPratica('0'); //0=aperta, 1=chiusa
        $ProtocolloUscita->setCodiceOperatore($this->HWScodiceOperatore);
        $ProtocolloUscita->setProtocolloCollegato($protocolloCollegato);
        $ProtocolloUscita->setAccesso($accesso);
        $ProtocolloUscita->setNote($note);
        $ProtocolloUscita->setProtocolloEmergenza($protocolloEmergenza);
        $ProtocolloUscita->setSegnatura($segnatura);
        $ProtocolloUscita->setComunicazioneInterna($comunicazioneInterna);
        if (isset($elementi['dati']['dataDocumento'])) {
            $ProtocolloUscita->setDataDocumento(soapDateTime(strtotime($elementi['dati']['dataDocumento'])));
        }
        $ProtocolloUscita->setFlagCartaceo('0'); //per fascicoli elettronici è sempre 0
        $ProtocolloUscita->setFlagInArchivio('0'); //se sto protocollando una nuova pratica la considero sempre come nuovo documento e quindi non presente in archivio
        $ProtocolloUscita->setCorrispondente($corrispondente);
        $ProtocolloUscita->setUfficio($ufficio);
        if (isset($elementi['dati']['Principale'])) {
            //modifica per passare anche il parametro 'firmaDigitale'
            if (strtolower($elementi['dati']['Principale']['estensione']) == 'p7m') {
                $elementi['dati']['Principale']['firmaDigitale'] = '1'; //da verificare se va bene '1' oppure necessita di true
            } else {
                $elementi['dati']['Principale']['firmaDigitale'] = '0';
            }
            $ProtocolloUscita->setDocumentoPrincipale($elementi['dati']['Principale']);
        }
        if (isset($elementi['dati']['Allegati'])) {
            //modifica per passare anche il parametro 'firmaDigitale'
            foreach ($elementi['dati']['Allegati'] as $key => $Allegato) {
                if (strtolower($Allegato['estensione']) == 'p7m') {
                    $elementi['dati']['Allegati'][$key]['firmaDigitale'] = '1';
                } else {
                    $elementi['dati']['Allegati'][$key]['firmaDigitale'] = '0';
                }
            }
            $ProtocolloUscita->setAllegati($elementi['dati']['Allegati']);
        }

//        if (isset($elementi['dati']['Principale'])) {
//            $ProtocolloUscita->setDocumentoPrincipale($elementi['dati']['Principale']);
//        }
//        if (isset($elementi['dati']['Allegati'])) {
//            $ProtocolloUscita->setAllegati($elementi['dati']['Allegati']);
//        }
        /**
         * fine settaggio parametri
         */
        $ret = $HWSClient->ws_ProtocollazioneUscita($ProtocolloUscita);
        //if (!$ret) {
        if ($HWSClient->getFault()) {
            app::log('fault');
            $msg = $HWSClient->getFault();
            $ritorno["Status"] = "-1";
            //$ritorno["Message"]="(Fault) Rilevato un errore in fase di protocollazione: <br>".$msg['!']."";
            $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } elseif ($HWSClient->getError()) {
            app::log('error');
            $msg = $HWSClient->getError();
            $ritorno["Status"] = "-1";
            //$ritorno["Message"]="(Error) Rilevato un errore in fase di protocollazione: <br>".$msg['!']."";
            $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
//            break;
//        }

        $risultato = $HWSClient->getResult();
        $risultato = $this->objectToArray($risultato);
        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if (!$TipoRisultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $HWSClient->getError() . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
            $Data = soapDateTime(time());
            //$proNum = substr($Data, 0, 4) . $risultato['return']['numeroProtocollo'];
            $proNum = $risultato['return']['numeroProtocollo'];
            $codiceProtocollo = $risultato['return']['codiceProtocollo'];
            $Anno = substr($Data, 0, 4);
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'WSPU', 'status' => true, 'msg' => 'ProtocollazioneIngresso'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'codiceProtocollo' => array('value' => $codiceProtocollo, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br><pre>" . $risultato . "</pre>";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    function registraProtocollo($uffici) {
        return "OK";
    }

    function objectToArray($object) {
        $array = array();
        foreach ($object as $member => $data) {
            if (is_object($data) || is_array($data)) {
                $data = $this->objectToArray($data);
            }
            $array[$member] = $data;
        }
        return $array;
    }

    public function cercaRubrica($ricerca = array()) {
        /**
         * settaggio dei parametri per la ricerca
         */
        $HWSClient = new itaHWSClient();
        $this->setClientConfig($HWSClient);
        $CercaRubrica = new itaCercaRubrica();
        $CercaRubrica->setJDBC($this->JDBC);
        if ($ricerca['idfiscale']) {
            $CercaRubrica->setIdFiscale($ricerca['idfiscale']);
        } elseif ($ricerca['descrizione']) {
            $CercaRubrica->setDescrizione($ricerca['descrizione']);
        }
        /**
         * fine settaggio parametri
         */
        $ret = $HWSClient->ws_CercaRubrica($CercaRubrica);
        if ($HWSClient->getFault()) {
            app::log('fault');
            $msg = $HWSClient->getFault();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Fault) Rilevato un errore in fase di ricerca Rubrica: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } elseif ($HWSClient->getError()) {
            app::log('error');
            $msg = $HWSClient->getError();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Error) Rilevato un errore in fase di ricerca Rubrica: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $risultato = $HWSClient->getResult();
        $risultato = $this->objectToArray($risultato);
        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if (!$TipoRisultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $HWSClient->getError() . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
            if ($risultato['return']['items']) {
                //$items = $risultato['return']['items'];
                $items = array();
                $trovato = true;
                if ($risultato['return']['items'][0]) {
                    $items = $risultato['return']['items'];
                    $ritorno["Status"] = "0";
                    $ritorno["Message"] = "Ricerca avvenuta con successo!";
                    $ritorno["RetValue"] = $items;
                } else {
                    $items[0] = $risultato['return']['items'];
                }
                //$items = $risultato['return']['items'];
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca avvenuta con successo!";
                $ritorno["RetValue"] = $items;
            } else {
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
                $trovato = false;
            }
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di ricerca Rubrica: <br><pre>" . $risultato . "</pre>";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    public function salvaVoceRubrica($dati = array()) {
        /**
         * settaggio dei parametri per la ricerca
         */
        $HWSClient = new itaHWSClient();
        $this->setClientConfig($HWSClient);
        $Rubrica = new itaRubrica();
        $Rubrica->setJDBC($this->JDBC);
        $Rubrica->setCodice('0');
        $CognomeNome = explode(" ", $dati['ragioneSociale']);
        $Corrispondente['Cognome'] = "";
        $Corrispondente['Nome'] = "";
        //se il nominativo è solo di due parole
        if (sizeof($CognomeNome) == 2) {
            $Corrispondente['Cognome'] = $CognomeNome[0];
            $Corrispondente['Nome'] = $CognomeNome[1];
        }
        //nominativo formato da 3 parole
        if (sizeof($CognomeNome) == 3) {
            //..., di cui due del cognome es. De Medici Lorenzo
            if (sizeof($CognomeNome[0]) < 5) {
                $Corrispondente['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
                $Corrispondente['Nome'] = $CognomeNome[2];
            } else {
                //..., di cui due del nome es. ROSSI MARIA GIOVANNA
                $Corrispondente['Cognome'] = $CognomeNome[0];
                $Corrispondente['Nome'] = $CognomeNome[1] . " " . $CognomeNome[2];
            }
        }
        if (sizeof($CognomeNome) == 4) {
            $Corrispondente['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
            $Corrispondente['Nome'] = $CognomeNome[2] . " " . $CognomeNome[3];
        }
        if (sizeof($CognomeNome) > 4) {
            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
            $NomeLungo = "";
            for ($i = 2; $i <= sizeof($CognomeNome); $i++) {
                $NomeLungo .= $CognomeNome[$i] . " ";
            }
            $CorrispondenteOccasionale['Nome'] = $NomeLungo;
        }
        if (!$dati['nome'] || $dati['nome'] == '') {
            $dati['nome'] = $Corrispondente['Nome'];
        }
        if (!$dati['cognome'] || $dati['cognome'] == '') {
            $dati['cognome'] = $Corrispondente['Cognome'];
        }
        if ($dati['nome']) {
            $Rubrica->setNome($dati['nome']);
        }
        if ($dati['cognome']) {
            $Rubrica->setCognome($dati['cognome']);
        }
        if ($dati['ragioneSociale']) {
            $Rubrica->setRagioneSociale($dati['ragioneSociale']);
        }
        if ($dati['indirizzo']) {
            $Rubrica->setIndirizzo($dati['indirizzo']);
        }
        if ($dati['cap']) {
            $Rubrica->setCap($dati['cap']);
        }
        if ($dati['citta']) {
            $Rubrica->setCitta($dati['citta']);
        }
        if ($dati['prov']) {
            $Rubrica->setProv($dati['prov']);
        }
        if ($dati['codiceFiscale']) {
            $Rubrica->setCodiceFiscale($dati['codiceFiscale']);
        }
        if ($dati['partitaIva']) {
            $Rubrica->setPartitaIva($dati['partitaIva']);
        }
        if ($dati['telefono']) {
            $Rubrica->setTelefono($dati['telefono']);
        }
        if ($dati['fax']) {
            $Rubrica->setFax($dati['fax']);
        }
        if ($dati['email']) {
            $Rubrica->setEmail($dati['email']);
        }
        /**
         * fine settaggio parametri
         */
        $ret = $HWSClient->ws_SalvaVoceRubrica($Rubrica);
        if ($HWSClient->getFault()) {
            app::log('fault');
            $msg = $HWSClient->getFault();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Fault) Rilevato un errore in fase di salvataggio voce in Rubrica: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } elseif ($HWSClient->getError()) {
            app::log('error');
            $msg = $HWSClient->getError();
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Error) Rilevato un errore in fase di salvataggio voce in Rubrica: <br>" . $msg . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        $risultato = $HWSClient->getResult();
        $risultato = $this->objectToArray($risultato);
        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if (!$TipoRisultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di salvataggio voce in Rubrica: <br>" . $HWSClient->getError() . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di salvataggio voce in Rubrica: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
            if ($risultato['return']['items']) {
                //$items = $risultato['return']['items'];
                $items = array();
                $trovato = true;
                if ($risultato['return']['items'][0]) {
                    $items = $risultato['return']['items'];
                    $ritorno["Status"] = "0";
                    $ritorno["Message"] = "Inserimento voce in Rubrica avvenuto con successo!";
                    $ritorno["RetValue"] = $items;
                } else {
                    $items[0] = $risultato['return']['items'];
                }
                //$items = $risultato['return']['items'];
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Inserimento voce in rubrica avvenuto con successo!";
                $ritorno["RetValue"] = $items;
            } else {
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
                $trovato = false;
            }
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di inserimento voce in Rubrica: <br><pre>" . $risultato . "</pre>";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    function CercaDocumentoProtocollo($dati = array()) {
        $HWSClient = new itaHWSClient();
        $this->setClientConfig($HWSClient);
        $CercaDocumentoProtocollo = new itaCercaDocumentoHWS();
        $CercaDocumentoProtocollo->setJDBC($this->JDBC);
        if ($dati['segnatura']) {
            $CercaDocumentoProtocollo->setSegnatura($dati['segnatura']);
        }
        if ($dati['aoo']) {
            $CercaDocumentoProtocollo->setAoo($dati['aoo']);
        }
        if ($dati['annoCompetenza']) {
            $CercaDocumentoProtocollo->setAnnoCompetenza($dati['annoCompetenza']);
        }
        if ($dati['numeroDocumento']) {
            $CercaDocumentoProtocollo->setNumeroDocumento($dati['numeroDocumento']);
        }
        if ($dati['tipoProtocollo']) {
            $CercaDocumentoProtocollo->setTipoProtocollo($dati['tipoProtocollo']);
        }

        $ret = $HWSClient->ws_CercaDocumentoProtocollo($CercaDocumentoProtocollo);
        if (!$ret) {
            if ($HWSClient->getFault()) {
                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($HWSClient->getFault(), true) . '</pre>');
            } elseif ($HWSClient->getError()) {
                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($HWSClient->getError(), true) . '</pre>');
            }
            return;
        }
        $risultato = $HWSClient->getResult();
        $risultato = $this->objectToArray($risultato);
        $TipoRisultato = $risultato['return']['messageResult']['tipoRisultato'];
        $DescrizioneRisultato = $risultato['return']['messageResult']['descrizione'];
        //gestione del messaggio d'errore
        if (!$TipoRisultato) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $HWSClient->getError() . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } elseif ($TipoRisultato == "Info" && $DescrizioneRisultato == "Success") {
            $Data = $risultato['return']['dataRegistrazione']; //2013-01-07
            $Numero = $risultato['return']['numeroDocumento'];
            $Segnatura = $risultato['return']['segnatura'];
            $Classifica = $risultato['return']['classificazione'];
            $Fascicoli = $risultato['return']['fascicoli'];
            if ($Fascicoli) {
                $Classifica['fascicoli'] = $Fascicoli;
            }
            $Oggetto = $risultato['return']['oggetto'];
            $DocumentiAllegati = array();
            $DocumentiAllegati[] = $risultato['return']['documentoPrincipale']['nomeFile'];
            $Allegati = $risultato['return']['allegati'];
            if ($Allegati[0]) {
                foreach ($Allegati as $Allegato) {
                    $DocumentiAllegati[] = $Allegato['nomeFile'];
                }
            } else {
                $DocumentiAllegati[] = $Allegati['nomeFile'];
            }
//            $datiSegnatura = explode("|", $Segnatura);
//            $Tipo = $datiSegnatura[4];
            $Anno = substr($Data, 0, 4);
            //$ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            $datiProtocollo = array(
                'TipoProtocollo' => 'WSPU',
                'NumeroProtocollo' => $Numero,
                'Data' => $Data,
                'Segnatura' => $Segnatura,
                'Anno' => $Anno,
                'Classifica' => $Classifica,
                'Oggetto' => $Oggetto,
                'DocumentiAllegati' => $DocumentiAllegati
            );
            $ritorno["RetValue"] = array(
                'DatiProtocollo' => $datiProtocollo
            );
        } else {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br><pre>" . $risultato . "</pre>";
            $ritorno["RetValue"] = false;
        }
        return $ritorno;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_WSPU;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
         return $this->protocollazioneUscita($elementi);
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->InserisciProtocollo($elementi, "A");
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->InserisciDocumentoEAnagrafiche($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }
}

?>