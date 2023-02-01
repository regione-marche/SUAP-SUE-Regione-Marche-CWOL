<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2016 Italsoft srl
 * @license
 * @version    14.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPInfor/itaInforClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciArrivo.class.php');
require_once(ITA_LIB_PATH . '/itaPHPInfor/itaInserisciPartenza.class.php');
require_once(ITA_LIB_PATH . '/itaPHPInfor/itaAllegaDocumento.class.php');
require_once(ITA_LIB_PATH . '/itaPHPInfor/itaLeggiProtocollo.class.php');

class itaInforManager {

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaInforManager();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    private function setClientConfig($inforClient) {
        $inforClient->setWebservices_uri($this->clientParam['WSINFORENDPOINT']);
        $inforClient->setWebservices_wsdl($this->clientParam['WSINFORWSDL']);
        //settaggio parametri operatore che effettua la chiamata a WS
        $this->utente = $this->clientParam['WSINFORUTENTE'];
        $this->corrispondente_smistamento = $this->clientParam['WSINFORUTENTEINTERNO'];
    }

    //public function inserisciArrivo($elementi) {
    public function inserisciProtocollo($elementi) {

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

//        print_r("<pre>");
//        print_r($this->clientParam);
//        print_r($this->utente);
//        print_r("</pre>");
//        exit();
        //username
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
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Classificazione non trovata. La procedura sarà interrotta";
                $ritorno["RetValue"] = false;
                return $ritorno;
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
                $msg = $InforClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($InforClient->getError()) {
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

    function AllegaDocumenti($anno, $numero, $DocumentiAllegati = array()) {
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
                    //Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($InforClient->getFault(), true) . '</pre>');
                } elseif ($InforClient->getError()) {
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
//            Out::msgStop("Attenzione", "\tSono stati rilevati errori allegando i documenti al protocollo n°" . $numero . " del " . $anno . "\n
//                \tProcedere manualmente per allegare i seguenti documenti:\n" . $err_str);
        }
    }

}

?>