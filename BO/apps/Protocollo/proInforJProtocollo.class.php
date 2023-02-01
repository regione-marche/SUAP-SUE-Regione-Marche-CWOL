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
 * @version    16.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
//include_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInforClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciArrivo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciPartenza.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaAllegaDocumento.class.php');
include_once(ITA_LIB_PATH . '/itaPHPInfor/itaLeggiProtocollo.class.php');
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proInforJProtocollo extends proWsClient{

    /**
     * Libreria di funzioni Generiche e Utility per Infor JProtocollo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private $param = array();
    public $praLib;
    public $accLib;
    private $utente;
    private $corrispondente = array();
    private $corrispondente_smistamento;
    private $fascicolo = array();

    function __construct() {
        $this->praLib = new praLib();
    }

    private function setClientConfig($inforClient) {
        $devLib = new devLib();
        //$envConfig_rec=$this->devLib->getEnv_confing('PALEOWSCONNECTION','codice','WSPALEOENDPOINT', false);
        $uri = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $inforClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOWSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $inforClient->setWebservices_wsdl($wsdl2);
        //settaggio parametri operatore che effettua la chiamata a WS
        $utente_tmp = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOUSER', false);
        $this->utente = $utente_tmp['CONFIG'];
        $corr_tmp = $devLib->getEnv_config('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOCORRISPONDENTE', false);
        $this->corrispondente_smistamento = $corr_tmp['CONFIG'];
//        $anno_tmp = $devLib->getEnv_confing('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLOANNOFAS', false);
//        $this->corrispondente_smistamento = $anno_tmp['CONFIG'];
//        $num_tmp = $devLib->getEnv_confing('INFORJPROTOCOLLOWS', 'codice', 'JPROTOCOLLONUMFAS', false);
//        $this->corrispondente_smistamento = $num_tmp['CONFIG'];
    }

    public function inserisciArrivo($elementi) {

        /*
         * acquisizione elementi
         */
        $DenominazioneSoggetto = utf8_encode($elementi['dati']['MittDest']['Denominazione']);
        $IndirizzoSoggetto = utf8_encode($elementi['dati']['MittDest']['Indirizzo'] .
                " " . $elementi['dati']['MittDest']['CAP'] .
                " " . $elementi['dati']['MittDest']['Citta'] .
                " " . $elementi['dati']['MittDest']['Provincia']);
        $DataRicezione = $elementi['dati']['DataArrivo'];
        $classificazione = array();
        $classificazione['titolario'] = $elementi['dati']['Classificazione'];
//        if ($this->fascicolo){
//            $classificazione['fascicolo']['anno'] = $this->fascicolo['anno'];
//            $classificazione['fascicolo']['numero'] = $this->fascicolo['numero'];
//        }

        $oggetto = utf8_encode($elementi['dati']['Oggetto']);
        $DocumentoPrincipale = array();
        $DocumentoPrincipale = $elementi['dati']['DocumentoPrincipale'];
        $DocumentiAllegati = array();
        $DocumentiAllegati = $elementi['dati']['DocumentiAllegati'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri per la protocollazione
         */
        $InforClient = new itaInforClient();
        $this->setClientConfig($InforClient);
        $InserisciArrivo = new itaInserisciArrivo();

        //username
        //questo è l'Operatore del sistema che esegue di fatto la protocollazione, deve essere un utente abilitato.
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $DatiInfor = $this->accLib->GetDatiInfor($UteCod);
        //se all'utente connesso è associato un operatore paleo lo seleziono, altrimenti lo prendo dai parametri
        //secondo disposizioni della regione questo utente è semrpe fittizio, quindi basta non associare agli utenti alcun operatore
        if ($DatiInfor['User'] != '' && $DatiInfor['Corrispondente'] != '') {
            $this->utente = $DatiInfor['User'];
            $this->corrispondente_smistamento = $DatiInfor['Corrispondente'];
        }

        $InserisciArrivo->setUsername($this->utente);

        //soggetti (forma ridotta)
        $InserisciArrivo->setSoggetti(array(
            'denominazione' => $DenominazioneSoggetto,
            'indirizzo' => $IndirizzoSoggetto
        ));
        //smistamento (definire dove inserire il corrispondente per lo smistamento: praDipe, praPar, ParametriGenerali...)
        $smistamenti = array();
        $smistamenti[] = array(
            'codice' => $this->corrispondente_smistamento
        );
        $InserisciArrivo->setSmistamenti($smistamenti);
        //oggetto
        $InserisciArrivo->setOggetto($oggetto);
        //classificazione
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'] != '') { //controllo esistenza di protocollo collegato
            $LeggiProtocollo = new itaLeggiProtocollo();
            $LeggiProtocollo->setUsername($this->utente);
            $LeggiProtocollo->setNumero($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value']);
            $LeggiProtocollo->setAnno($elementi['dati']['MetaDati']['DatiProtocollazione']['Anno']['value']);
            $ret = $InforClient->ws_leggiProtocollo($LeggiProtocollo);
            if (!$ret) {
                if ($InforClient->getFault()) {
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il protocollo: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($InforClient->getError()) {
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il protocollo: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $InforClient->getResult();
            $TipoRisultato = $risultato['esito'];
            $DescrizioneRisultato = $risultato['messaggio'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "KO") {
                app::log('errore');
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il registro: <br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca Fascicolo avvenuta con successo!";
                if ($risultato['classificazione']) {
                    $ritorno["RetValue"] = $risultato['classificazione'];
                }
            }
            if ($ritorno['Status'] == "0") {
                if ($ritorno['RetValue']['fascicolo']) {
                    $classificazione['fascicolo'] = array();
                    $classificazione['fascicolo']['anno'] = $ritorno['RetValue']['fascicolo']['anno'];
                    $classificazione['fascicolo']['numero'] = $ritorno["RetValue"]['fascicolo']['numero'];
                }
            } else {
                return $ritorno;
            }
            /*
             * se si protocolla la comunicazione relativa a un passo si può scegliere di inserire il parametro anteatto
             * per inserirlo scommentare il codice sottostante
             */
//            //se ci sono i metadati viene valorizzato anteatto, per definire il protocollo collegato
//            $anteatto = array();
//            $anteatto['anno'] = $risultato['protocollo']['anno'];
//            $anteatto['numero'] = $risultato['protocollo']['numero'];
//            $InserisciArrivo->setAnteatto($anteatto);
        } else {
            if ($classificazione) {
                $InserisciArrivo->setClassificazione($classificazione);
            } else {
                Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
                return;
            }
        }
        //dataRicezione
        if ($DataRicezione) {
            $InserisciArrivo->setDataRicezione($DataRicezione);
        }
        //documento
        if ($DocumentoPrincipale) {
            $documento['titolo'] = substr(utf8_encode($DocumentoPrincipale['Descrizione']), 0, 99);
            $documento['nomeFile'] = utf8_encode($DocumentoPrincipale['Nome']);
            $documento['file'] = $DocumentoPrincipale['Stream'];
        }
        $InserisciArrivo->setDocumento($documento);
        //segnatura (impostata per confermare SEMPRE la segnatura
        $InserisciArrivo->setConfermaSegnatura(true);
        /**
         * fine settaggio parametri
         */
        $ret = $InforClient->ws_inserisciArrivo($InserisciArrivo);
        if (!$ret) {
            if ($InforClient->getFault()) {
                app::log('fault');
                $msg = $InforClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($InforClient->getError()) {
                app::log('error');
                $msg = $InforClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
            return;
            ;
        }

        $risultato = $InforClient->getResult();
        $TipoRisultato = $risultato['esito'];
        if ($risultato['messaggio']) {
            $DescrizioneRisultato = $risultato['messaggio'];
        } else {
            $DescrizioneRisultato = '';
        }
        //gestione del messaggio d'errore
        if ($TipoRisultato == "KO") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            //$ProtocollazioneEntrataResult=$risultato['ProtocollazioneEntrataResult'];
            $Data = (string) $risultato['segnatura']['data']; //è nel formato 21/11/2012
            /*
             * Sostituiti slash con i trattini per metterla nel formato europeo
             */
            $Data = str_replace("/", "-", $Data);
            //$proNum = substr($Data, 6, 4) . $risultato['segnatura']['numero'];
            $proNum = $risultato['segnatura']['numero'];
            $DocNumber = $risultato['segnatura']['numero'];
            $Segnatura = $risultato['segnatura'];
            $Anno = substr($Data, 6, 4);
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Infor', 'status' => true, 'msg' => 'inserisciArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
            //se la protocollazione è andata a buon fine si esegue l'aggiunta dei documenti allegati
            $ret_allegati = $this->AllegaDocumenti($Anno, $risultato['segnatura']['numero'], $DocumentiAllegati);
            if (!$ret_allegati) {
                
            }
        }

        return $ritorno;
    }
    
    // TODO mettere privato
    public function inserisciPartenza($elementi) {
        $DataInvio = $elementi['dati']['DataArrivo'];
        $classificazione = array();
        $classificazione['titolario'] = $elementi['dati']['Classificazione'];
        $oggetto = utf8_encode($elementi['dati']['Oggetto']);
        $DocumentoPrincipale = array();
        $DocumentoPrincipale = $elementi['dati']['DocumentoPrincipale'];
        $DocumentiAllegati = array();
        $DocumentiAllegati = $elementi['dati']['DocumentiAllegati'];

        $InforClient = new itaInforClient();
        $this->setClientConfig($InforClient);
        $InserisciPartenza = new itaInserisciPartenza();
        //        
        //username
        //questo è l'Operatore del sistema che esegue di fatto la protocollazione, deve essere un utente abilitato.
        //
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $DatiInfor = $this->accLib->GetDatiInfor($UteCod);
        //se all'utente connesso sono associati i dati infor infor lo seleziono, altrimenti lo prendo dai parametri
        //secondo disposizioni della regione questo utente è semrpe fittizio, quindi basta non associare agli utenti alcun operatore
        if ($DatiInfor['User'] != '' && $DatiInfor['Corrispondente'] != '') {
            app::log('operatore da login');
            $this->utente = $DatiInfor['User'];
            $this->corrispondente_smistamento = $DatiInfor['Corrispondente'];
        } else {
            app::log('operatore da parametri');
        }

        $InserisciPartenza->setUsername($this->utente);
        //soggetti (forma ridotta)
//        $DenominazioneSoggetto = utf8_encode($elementi['dati']['MittDest']['Denominazione']);
//        $IndirizzoSoggetto = utf8_encode($elementi['dati']['MittDest']['Indirizzo'] .
//                " " . $elementi['dati']['MittDest']['CAP'] .
//                " " . $elementi['dati']['MittDest']['Citta'] .
//                " " . $elementi['dati']['MittDest']['Provincia']);
//        $InserisciPartenza->setSoggetti(array(
//            'denominazione' => $DenominazioneSoggetto,
//            'indirizzo' => $IndirizzoSoggetto
//        ));
        //
        // Soggetti Nuova Forma Estesa
        //
        $destinatari = array();
        foreach ($elementi['dati']['destinatari'] as $destinatario) {
            $DenominazioneSoggetto = utf8_encode($destinatario['Denominazione']);
            $IndirizzoSoggetto = utf8_encode($destinatario['Indirizzo'] . " " . $destinatario['CAP'] . " " . $destinatario['Citta'] . " " . $destinatario['Provincia']);
            $destinatari[] = array('denominazione' => $DenominazioneSoggetto, 'indirizzo' => $IndirizzoSoggetto);
        }
        $InserisciPartenza->setSoggetti($destinatari);


        //smistamento (definire dove inserire il corrispondente per lo smistamento: praDipe, praPar, ParametriGenerali...)
        $smistamenti = array();
        $smistamenti[] = array(
            'codice' => $this->corrispondente_smistamento
        );
        $InserisciPartenza->setSmistamenti($smistamenti);
        //oggetto
        $InserisciPartenza->setOggetto($oggetto);
        //classificazione
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['proNum']['value'] != '') { //controllo esistenza di protocollo collegato
            $LeggiProtocollo = new itaLeggiProtocollo();
            $LeggiProtocollo->setUsername($this->utente);
            $LeggiProtocollo->setNumero($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value']);
            $LeggiProtocollo->setAnno($elementi['dati']['MetaDati']['DatiProtocollazione']['Anno']['value']);
            $ret = $InforClient->ws_leggiProtocollo($LeggiProtocollo);
            if (!$ret) {
                if ($InforClient->getFault()) {
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($InforClient->getError()) {
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $InforClient->getResult();
            $TipoRisultato = $risultato['esito'];
            $DescrizioneRisultato = $risultato['messaggio'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "KO") {
                app::log('errore');
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il protocollo: <br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca Fascicolo avvenuta con successo!";
                if ($risultato['classificazione']) {
                    $ritorno["RetValue"] = $risultato['classificazione'];
                }
            }
            if ($ritorno['Status'] == "0") {
                if ($ritorno['RetValue']['fascicolo']) {
                    $classificazione['fascicolo'] = array();
                    $classificazione['fascicolo']['anno'] = $ritorno['RetValue']['fascicolo']['anno'];
                    $classificazione['fascicolo']['numero'] = $ritorno["RetValue"]['fascicolo']['numero'];
                }
            } else {
                return $ritorno;
            }
            /*
             * se si protocolla la comunicazione relativa a un passo si può scegliere di inserire il parametro anteatto
             * per inserirlo scommentare il codice sottostante
             */
//            //se ci sono i metadati viene valorizzato anteatto, per definire il protocollo collegato
//            $anteatto = array();
//            $anteatto['anno'] = $risultato['protocollo']['anno'];
//            $anteatto['numero'] = $risultato['protocollo']['numero'];
//            $InserisciPartenza->setAnteatto($anteatto);
        } else {
            if ($classificazione) {
                $InserisciPartenza->setClassificazione($classificazione);
            } else {
                Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
                return;
                //$NuovoFascicolo['CodiceClassifica']="1.2"; //se non è settato ne metto uno di default
            }
        }

        //dataRicezione
        if ($DataInvio) {
            $InserisciPartenza->setDataInvio($DataInvio);
        }
        //documento
        if ($DocumentoPrincipale) {
            $documento['titolo'] = substr(utf8_encode($DocumentoPrincipale['Descrizione']), 0, 99);//utf8_encode($DocumentoPrincipale['Descrizione']);
            $documento['nomeFile'] = utf8_encode($DocumentoPrincipale['Nome']);
            $documento['file'] = $DocumentoPrincipale['Stream'];
        }
        $InserisciPartenza->setDocumento($documento);
        //segnatura (impostata per confermare SEMPRE la segnatura
        $InserisciPartenza->setConfermaSegnatura(true);
        /**
         * fine settaggio parametri
         */
        $ret = $InforClient->ws_inserisciPartenza($InserisciPartenza);
        if (!$ret) {
            if ($InforClient->getFault()) {
                app::log('fault');
                $msg = $InforClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($InforClient->getError()) {
                app::log('error');
                $msg = $InforClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
            return;
            ;
        }

        $risultato = $InforClient->getResult();
        $TipoRisultato = $risultato['esito'];
        if ($risultato['messaggio']) {
            $DescrizioneRisultato = $risultato['messaggio'];
        } else {
            $DescrizioneRisultato = '';
        }
        //gestione del messaggio d'errore
        if ($TipoRisultato == "KO") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            //$ProtocollazioneEntrataResult=$risultato['ProtocollazioneEntrataResult'];
            $Data = (string) $risultato['segnatura']['data']; //è nel formato 21/11/2012
            //$proNum = substr($Data, 6, 4) . $risultato['segnatura']['numero'];
            $proNum = $risultato['segnatura']['numero'];
            $DocNumber = $risultato['segnatura']['numero'];
            $Segnatura = $risultato['segnatura'];
            $Anno = substr($Data, 6, 4);
            //$ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Infor', 'status' => true, 'msg' => 'inserisciArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''),
                )
            );
            //se la protocollazione è andata a buon fine si esegue l'aggiunta dei documenti allegati
            $ret_allegati = $this->AllegaDocumenti($Anno, $risultato['segnatura']['numero'], $DocumentiAllegati);
            if (!$ret_allegati) {
                
            }
        }

        return $ritorno;
    }

    function AllegaDocumenti($anno, $numero, $DocumentiAllegati = array()) {
        App::log('DocumentiAllegati dentro Infor');
        App::log($DocumentiAllegati);
        if (!$DocumentiAllegati) {
            return true;
        }
        $err_n = 0;
        $err_allegati = array();
        foreach ($DocumentiAllegati as $Allegato) {
            if ($Allegato['Descrizione']) {
                $titolo = utf8_encode(substr($Allegato['Descrizione'], 0, 99));
            } else {
                $titolo = '';
            }
            if ($Allegato['Documento']['Nome']) {
                $nomeFile = utf8_encode(substr($Allegato['Documento']['Nome'], 0, 99));
            } else {
                $nomeFile = '';
            }
            if ($Allegato['Documento']['Stream']) {
                $file = $Allegato['Documento']['Stream'];
            } else {
                $file = '';
            }
            $InforClient = new itaInforClient();
            $this->setClientConfig($InforClient);
            $AllegaDocumento = new itaAllegaDocumento();
            //username
            $AllegaDocumento->setUsername($this->utente);
            //riferimento
            $riferimento = array();
            $riferimento['anno'] = $anno;
            $riferimento['numero'] = $numero;
//        if ($_POST[$this->name_form . '_RegistroRif_codice'] != '') {
//            $riferimento['registro'] = array();
//            $riferimento['registro']['codice'] = $_POST[$this->name_form . '_RegistroRif_codice'];
//            $riferimento['registro']['descrizione'] = $_POST[$this->name_form . '_RegistroRif_descrizione'];
//        }
            $AllegaDocumento->setRiferimento($riferimento);
            //titolo
            $AllegaDocumento->setTitolo($titolo);
            //volume
//        if ($_POST[$this->name_form . '_Volume_codice'] != '') {
//            $volume = array(
//                'codice' => $_POST[$this->name_form . '_Volume_codice'],
//                'descrizione' => $_POST[$this->name_form . '_Volume_descrizione']
//            );
//            $AllegaDocumento->setVolume($volume);
//        }
            //formato
//        if ($_POST[$this->name_form . '_Formato_codice'] != '') {
//            $formato = array(
//                'codice' => $_POST[$this->name_form . '_Formato_codice'],
//                'descrizione' => $_POST[$this->name_form . '_Formato_descrizione']
//            );
//            $AllegaDocumento->setFormato($formato);
//        }
            //nomeFile
            $AllegaDocumento->setNomeFile($nomeFile);
            //file
            $AllegaDocumento->setFile($file);

            $ret = $InforClient->ws_allegaDocumento($AllegaDocumento);
            if (!$ret) {
                if ($InforClient->getFault()) {
                    app::log('fault allegaDocumento');
                    //Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                } elseif ($InforClient->getError()) {
                    app::log('error allegaDocumento');
                    //Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($InforClient->getError(), true) . '</pre>');
                }
                $err_allegati[$err_n] = $nomeFile;
                $err_n++;
                return false;
            }
        }
        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '\n';
            }
            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n°" . $numero . " del " . $anno . "\n
                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
        }
    }

    function leggiProtocollo($dati) {
        /**
         * settaggio dei parametri per la connessione
         */
        $InforClient = new itaInforClient();
        $this->setClientConfig($InforClient);
        //username
        //questo è l'Operatore del sistema che esegue di fatto la protocollazione, deve essere un utente abilitato.
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $DatiInfor = $this->accLib->GetDatiInfor($UteCod);
        //se all'utente connesso sono associati i dati infor infor lo seleziono, altrimenti lo prendo dai parametri
        //secondo disposizioni della regione questo utente è semrpe fittizio, quindi basta non associare agli utenti alcun operatore
        if ($DatiInfor['User'] != '' && $DatiInfor['Corrispondente'] != '') {
            $this->utente = $DatiInfor['User'];
            $this->corrispondente_smistamento = $DatiInfor['Corrispondente'];
        }

        if ($dati['DocNumber'] != '' && $dati['Anno'] != '') { //controllo esistenza di protocollo collegato
            $LeggiProtocollo = new itaLeggiProtocollo();
            $LeggiProtocollo->setUsername($this->utente);
            $LeggiProtocollo->setNumero($dati['DocNumber']);
            $LeggiProtocollo->setAnno($dati['Anno']);
            $ret = $InforClient->ws_leggiProtocollo($LeggiProtocollo);
            if (!$ret) {
                if ($InforClient->getFault()) {
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($InforClient->getError()) {
                    app::log('error');
                    $msg = $InforClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $InforClient->getResult();
            $TipoRisultato = $risultato['esito'];
            $DescrizioneRisultato = $risultato['messaggio'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "KO") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il protocollo: <br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca Protocollo eseguita con successo!";
                $Data = $risultato['protocollo']['dataRegistrazione']; //26/11/2012
                $Numero = $risultato['protocollo']['numero'];
                $Segnatura = ''; //nel protocollo Infor non c'è un dato identificato come Segnatura
                $Classifica = $risultato['protocollo']['classificazione'];
                $Fascicoli = $risultato['protocollo']['classificazione']['fascicolo'];
                $Oggetto = $risultato['protocollo']['oggetto'];
                $DocumentiAllegati = array();
                $Allegati = $risultato['protocollo']['documenti']['documento'];
                //if (count($risultato['protocollo']['documenti']['documento']) == 1) {
                if (isset($risultato['protocollo']['documenti']['documento']['titolo'])) {
                    $DocumentiAllegati[] = $Allegati['titolo'];
                } else {
                    //if (count($risultato['protocollo']['documenti']['documento']) > 1) {
                    foreach ($Allegati as $Allegato) {
                        $DocumentiAllegati[] = $Allegato['titolo'];
                    }
                }
                $Anno = $risultato['protocollo']['anno'];
                $datiProtocollo = array(
                    'TipoProtocollo' => 'Infor',
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
            }
            return $ritorno;
        }
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_INFOR;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->inserisciPartenza($elementi);
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->inserisciArrivo($elementi);
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->InserisciDocumentoEAnagrafiche($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }
}

?>