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
include_once(ITA_LIB_PATH . '/itaPHPPaleo/itaPaleoClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo/itareqProtocolloArrivo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo/itareqProtocolloPartenza.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo/itaCercaDocumentoProtocollo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo/itaOperatorePaleo.class.php');
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';

class proPaleo extends proWsClient {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Paleo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    private $param = array();
    public $praLib;
    public $accLib;
    private $WsOperatorePaleoUO;
    private $WsOperatorePaleoCognome;
    private $WsOperatorePaleoNome;
    private $WsOperatorePaleoRuolo;

    function __construct() {
        $this->praLib = new praLib();
    }

    private function setClientConfig($paleoClient) {
        $devLib = new devLib();
        //$envConfig_rec=$this->devLib->getEnv_confing('PALEOWSCONNECTION','codice','WSPALEOENDPOINT', false);
        $uri = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSPALEOENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $paleoClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSPALEOWSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $paleoClient->setWebservices_wsdl($wsdl2);
        $CodAmm = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'CODAMMINISTRAZIONEPALEO', false);
        $CodAmm2 = $CodAmm['CONFIG'];
        $username = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSUTENTEPALEO', false);
        $username2 = $username['CONFIG'];
        $password = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSPASSWORDPALEO', false);
        $password2 = $password['CONFIG'];
        $paleoClient->setUsername($CodAmm2 . "\\" . $username2);
        $paleoClient->setpassword($password2);
        $timeout = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSEXECUTIONTIMEOUT', false);
        $paleoClient->setTimeout($timeout['CONFIG']);
        //settaggio parametri operatore che effettua la chiamata a WS
        $this->WsOperatorePaleoUO = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOUO', false);
        $this->WsOperatorePaleoCognome = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEOCOGNOME', false);
        $this->WsOperatorePaleoNome = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEONOME', false);
        $this->WsOperatorePaleoRuolo = $devLib->getEnv_config('PALEOWSCONNECTION', 'codice', 'WSOPERATOREPALEORUOLO', false);
    }

    public function protocollazioneEntrata($elementi) {

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
        $CognomeNome = array();
        $DataArrivo = date("Y-m-d", strtotime($elementi['dati']['DataArrivo']));
        $CorrispondenteOccasionale = array();
        $CorrispondenteOccasionale['MessaggioRisultato'] = array(
            'Descrizione' => '',
            'TipoRisultato' => 'Info'
        );
        $CognomeNome = explode(" ", utf8_encode($elementi['dati']['MittDest']['Denominazione']));
        $CorrispondenteOccasionale['Cognome'] = "";
        $CorrispondenteOccasionale['Nome'] = "";
        //se il nominativo è solo di due parole
        if (sizeof($CognomeNome) == 1) {
            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
            $CorrispondenteOccasionale['Nome'] = " ";
        }
        if (sizeof($CognomeNome) == 2) {
            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
            $CorrispondenteOccasionale['Nome'] = $CognomeNome[1];
        }
        //nominativo formato da 3 parole
        if (sizeof($CognomeNome) == 3) {
            //..., di cui due del cognome es. De Medici Lorenzo
            if (sizeof($CognomeNome[0]) < 5) {
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
                $CorrispondenteOccasionale['Nome'] = $CognomeNome[2];
            } else {
                //..., di cui due del nome es. ROSSI MARIA GIOVANNA
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
                $CorrispondenteOccasionale['Nome'] = $CognomeNome[1] . " " . $CognomeNome[2];
            }
        }
        if (sizeof($CognomeNome) == 4) {
            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
            $CorrispondenteOccasionale['Nome'] = $CognomeNome[2] . " " . $CognomeNome[3];
        }
        if (sizeof($CognomeNome) > 4) {
            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
            $NomeLungo = "";
            for ($i = 2; $i <= sizeof($CognomeNome); $i++) {
                $NomeLungo .= $CognomeNome[$i] . " ";
            }
            $CorrispondenteOccasionale['Nome'] = $NomeLungo;
        }
        $CorrispondenteOccasionale['Email'] = utf8_encode($elementi['dati']['MittDest']['Email']);
        $CorrispondenteOccasionale['Tipo'] = "Persona";

        $Classifica = $elementi['dati']['Classificazione'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri per la protocollazione
         */
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        $reqProtocolloArrivo = new itareqProtocolloArrivo();

        //questo è l'Operatore del sistema che esegue di fatto la protocollazione, deve essere un utente abilitato.
        $UteCod = App::$utente->getKey('idUtente');
        $this->accLib = new accLib();
        $OperatoreLogin = $this->accLib->GetOperatorePaleo($UteCod);
        //se all'utente connesso è associato un operatore paleo lo seleziono, altrimenti lo prendo dai parametri
        //secondo disposizioni della regione questo utente è semrpe fittizio, quindi basta non associare agli utenti alcun operatore
        if ($OperatoreLogin['CodiceUO'] != '' && $OperatoreLogin['Cognome'] != '' && $OperatoreLogin['Ruolo'] != '') {
            $reqProtocolloArrivo->setOperatore(array(
                "CodiceUO" => $OperatoreLogin['CodiceUO'],
                "Cognome" => $OperatoreLogin['Cognome'],
                "Nome" => $OperatoreLogin['Nome'],
                "Ruolo" => $OperatoreLogin['Ruolo'],
                    )
            );
        } else {
            $reqProtocolloArrivo->setOperatore(array(
                "CodiceUO" => $this->WsOperatorePaleoUO['CONFIG'],
                "Cognome" => $this->WsOperatorePaleoCognome['CONFIG'],
                "Nome" => $this->WsOperatorePaleoNome['CONFIG'],
                "Ruolo" => $this->WsOperatorePaleoRuolo['CONFIG']
                    )
            );
        }
        $Operatore = $reqProtocolloArrivo->getOperatore();

        //definizione OperatorePaleo per altri metodi
        $OperatorePaleo = new itaOperatorePaleo();
        $OperatorePaleo->setCodiceUO($Operatore['CodiceUO']);
        $OperatorePaleo->setCognome($Operatore['Cognome']);
        $OperatorePaleo->setNome($Operatore['Nome']);
        $OperatorePaleo->setRuolo($Operatore['Ruolo']);

        //acquisizione CodiceRegistro
        $ret = $paleoClient->ws_GetRegistri($OperatorePaleo);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['GetRegistriResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['GetRegistriResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore cercando il registro: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca Registro avvenuta con successo!";
            $ritorno["RetValue"] = $risultato['GetRegistriResult']['Lista']['RegistroInfo']['Codice'];
        }
        if ($ritorno['Status'] == "0") {
            $CodiceRegistro = $ritorno["RetValue"];
        } else {
            return $ritorno;
        }

        $reqProtocolloArrivo->setCodiceRegistro($CodiceRegistro);

        //$reqProtocolloArrivo->setOggetto(utf8_encode("<![CDATA[".$elementi['dati']['Oggetto']."]]>"));
        $reqProtocolloArrivo->setOggetto(utf8_encode($elementi['dati']['Oggetto']));
        $ogg = $reqProtocolloArrivo->getOggetto();
        $reqProtocolloArrivo->setPrivato(false);
        $reqProtocolloArrivo->setDPAI(false);
        $reqProtocolloArrivo->setDataArrivo($DataArrivo);
        $reqProtocolloArrivo->setMittente(array(
            "CodiceRubrica" => "", //CodiceRubrica="ZT1"
            "CorrispondenteOccasionale" => $CorrispondenteOccasionale
                )
        );
        //settaggi Classificazione
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value'] != '') {
            $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
            $CercaDocumentoProtocollo->setDocNumber($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value']);
            $ret = $paleoClient->ws_CercaDocumentoProtocollo($OperatorePaleo, $CercaDocumentoProtocollo);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il registro: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
                return;
            }
            $risultato = $paleoClient->getResult();
            $TipoRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'];
            $DescrizioneRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'];
            //gestione del messaggio d'errore
            if ($TipoRisultato == "Error") {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il registro: <br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca Registro avvenuta con successo!";
                $ritorno["RetValue"] = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
            }
            if ($ritorno['Status'] == "0") {
                $CodiceFascicolo = $ritorno["RetValue"];
            } else {
                return $ritorno;
            }
            //$CodiceFascicolo=$risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
        } else {
            if ($elementi['dati']['Classificazione'] != '') {
                $NuovoFascicolo['CodiceClassifica'] = $elementi['dati']['Classificazione'];
            } else {
                Out::msgStop("Errore", "Classificazione non trovata. La procedura sarà interrotta");
                return;
                //$NuovoFascicolo['CodiceClassifica']="1.2"; //se non è settato ne metto uno di default
            }
//            $NuovoFascicolo['Custode']['CodiceUO']=$elementi['dati']['CustodePaleo']->GetCodiceUO();
//            $NuovoFascicolo['Custode']['Cognome']=  utf8_encode($elementi['dati']['CustodePaleo']->GetCognome());
//            $NuovoFascicolo['Custode']['Nome']=  utf8_encode($elementi['dati']['CustodePaleo']->GetNome());
//            $NuovoFascicolo['Custode']['Ruolo']=$elementi['dati']['CustodePaleo']->GetRuolo();
            //modifica per prendere lo stesso operatore dei parametri
            $NuovoFascicolo['Custode']['CodiceUO'] = $this->WsOperatorePaleoUO['CONFIG'];
            $NuovoFascicolo['Custode']['Cognome'] = $this->WsOperatorePaleoCognome['CONFIG'];
            $NuovoFascicolo['Custode']['Nome'] = $this->WsOperatorePaleoNome['CONFIG'];
            $NuovoFascicolo['Custode']['Ruolo'] = $this->WsOperatorePaleoRuolo['CONFIG'];

            //$NuovoFascicolo['Descrizione']="SUAP - Pratica n° " . $elementi['dati']['NumeroPratica'] . " - " . $elementi['dati']['MittDest']['Denominazione'];
            $NuovoFascicolo['Descrizione'] = utf8_encode($elementi['dati']['Oggetto']); //inserisco per la descrizione del fascicolo lo stesso oggetto della pratica
            $CodiceFascicolo = "";
        }

        if (!empty($NuovoFascicolo)) {
            $Classificazione = array(
                "CodiceFascicolo" => "",
                "NuovoFascicolo" => $NuovoFascicolo
            );
        } else {
            $Classificazione = array(
                "CodiceFascicolo" => $CodiceFascicolo
            );
        }
        $reqProtocolloArrivo->setClassificazioni(array(
            "Classificazione" => $Classificazione
                )
        );

        //settaggio Trasmissioni
//        $Trasmissione=array(
//                //"InvioOriginaleCartaceo" => "",
//                //"NoteGenerali" => "",
//                "SegueCartaceo" => 'false', //valore di default
//                /*
//                            "TrasmissioniRuolo" => array(
//                                        "TrasmissioneRuolo" => $TrasmissioneRuolo
//                                ),
//                             *
//                */
//
//                "TrasmissioniUtente" => $elementi['dati']['TrasmissioniPaleo']
//            /*
//            array(
//                        "TrasmissioneUtente" => array(
//                        //"DataScadenza" => $DataScadenzaUtente,
//                                "Note" => "",
//                                "OperatoreDestinatario" => array(
//                                        "CodiceUO" => "",
//                                        "Cognome" => "",
//                                        "Nome" => "",
//                                        "Ruolo" => ""
//                                ),
//                                "Ragione" => ""
//                        )
//                )
//             *
//             */
//        );
//        $reqProtocolloArrivo->setTrasmissione($Trasmissione);

        if ($elementi['dati']['DocumentoPrincipale']) {
            //$reqProtocolloArrivo->setDocumentoPrincipale($elementi['dati']['DocumentoPrincipale']);
            $reqProtocolloArrivo->setDocumentoPrincipale(array(
                "Nome" => utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']),
                "Stream" => $elementi['dati']['DocumentoPrincipale']['Stream']
                    )
            );
            //$reqProtocolloArrivo->setDPAI(true);
        }

        if ($elementi['dati']['DocumentiAllegati']) {
            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
            foreach ($DocAllegati as $key => $record) {
                $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
                $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
            }
            $Allegati = array(
                "Allegato" => $DocAllegati
            );

            $reqProtocolloArrivo->setDocumentiAllegati($Allegati);
            //$reqProtocolloArrivo->setDocumentiAllegati($DocAllegati);
        }

        /**
         * fine settaggio parametri
         */
        $ret = $paleoClient->ws_ProtocollazioneEntrata($reqProtocolloArrivo);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
            return;
            ;
        }

        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            //$ProtocollazioneEntrataResult=$risultato['ProtocollazioneEntrataResult'];
            $Data = (string) $risultato['ProtocollazioneEntrataResult']['DataProtocollazione']; //è nel formato 2012-05-10T11:23:53.377
            //$proNum = substr($Data, 0, 4) . $risultato['ProtocollazioneEntrataResult']['Numero'];
            $proNum = $risultato['ProtocollazioneEntrataResult']['Numero'];
            $DocNumber = $risultato['ProtocollazioneEntrataResult']['DocNumber'];
            $Segnatura = $risultato['ProtocollazioneEntrataResult']['Segnatura'];
            $Anno = substr($Data, 0, 4);
            //$ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Paleo', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        }

        return $ritorno;
    }

    public function protocollazionePartenza($elementi) {

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
         * acquisizione parametri da form
         */
        $CognomeNome = array();
        $CorrispondenteOccasionale = array();
        $CorrispondenteOccasionale['MessaggioRisultato'] = array(
            'Descrizione' => '',
            'TipoRisultato' => 'Info'
        );




        // DESTINATARI ???

        $destinatari = array();
        foreach ($elementi['dati']['destinatari'] as $destinatario) {
            $CorrispondenteOccasionale = array();

            /*
             * Elaboro Cognome e Nome
             */
            $CognomeNome = explode(" ", utf8_encode($destinatario['Denominazione']));
            $CorrispondenteOccasionale['Cognome'] = "";
            $CorrispondenteOccasionale['Nome'] = "";
            //se il nominativo è solo di due parole
            if (sizeof($CognomeNome) == 1) {
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
                $CorrispondenteOccasionale['Nome'] = " ";
            }
            if (sizeof($CognomeNome) == 2) {
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
                $CorrispondenteOccasionale['Nome'] = $CognomeNome[1];
            }
            //nominativo formato da 3 parole
            if (sizeof($CognomeNome) == 3) {
                //..., di cui due del cognome es. De Medici Lorenzo
                if (sizeof($CognomeNome[0]) < 5) {
                    $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
                    $CorrispondenteOccasionale['Nome'] = $CognomeNome[2];
                } else {
                    //..., di cui due del nome es. ROSSI MARIA GIOVANNA
                    $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
                    $CorrispondenteOccasionale['Nome'] = $CognomeNome[1] . " " . $CognomeNome[2];
                }
            }
            if (sizeof($CognomeNome) == 4) {
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
                $CorrispondenteOccasionale['Nome'] = $CognomeNome[2] . " " . $CognomeNome[3];
            }
            if (sizeof($CognomeNome) > 4) {
                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
                $NomeLungo = "";
                for ($i = 2; $i <= sizeof($CognomeNome); $i++) {
                    $NomeLungo .= $CognomeNome[$i] . " ";
                }
                $CorrispondenteOccasionale['Nome'] = $NomeLungo;
            }
            /*
             * Altri dati
             */
            $CorrispondenteOccasionale['Email'] = utf8_encode($destinatario['Email']);
            $CorrispondenteOccasionale['Tipo'] = "Persona";

            $destinatari[] = array("CodiceRubrica" => "", "CorrispondenteOccasionale" => $CorrispondenteOccasionale); //$CorrispondenteOccasionale;
        }




//        $CognomeNome = explode(" ", utf8_encode($elementi['dati']['MittDest']['Denominazione']));
//        $CorrispondenteOccasionale['Cognome'] = "";
//        $CorrispondenteOccasionale['Nome'] = "";
//        //se il nominativo è solo di due parole
//        if (sizeof($CognomeNome) == 1) {
//            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
//            $CorrispondenteOccasionale['Nome'] = " ";
//        }
//        if (sizeof($CognomeNome) == 2) {
//            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
//            $CorrispondenteOccasionale['Nome'] = $CognomeNome[1];
//        }
//        //nominativo formato da 3 parole
//        if (sizeof($CognomeNome) == 3) {
//            //..., di cui due del cognome es. De Medici Lorenzo
//            if (sizeof($CognomeNome[0]) < 5) {
//                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
//                $CorrispondenteOccasionale['Nome'] = $CognomeNome[2];
//            } else {
//                //..., di cui due del nome es. ROSSI MARIA GIOVANNA
//                $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0];
//                $CorrispondenteOccasionale['Nome'] = $CognomeNome[1] . " " . $CognomeNome[2];
//            }
//        }
//        if (sizeof($CognomeNome) == 4) {
//            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
//            $CorrispondenteOccasionale['Nome'] = $CognomeNome[2] . " " . $CognomeNome[3];
//        }
//        if (sizeof($CognomeNome) > 4) {
//            $CorrispondenteOccasionale['Cognome'] = $CognomeNome[0] . " " . $CognomeNome[1];
//            $NomeLungo = "";
//            for ($i = 2; $i <= sizeof($CognomeNome); $i++) {
//                $NomeLungo .= $CognomeNome[$i] . " ";
//            }
//            $CorrispondenteOccasionale['Nome'] = $NomeLungo;
//        }
//        $CorrispondenteOccasionale['Email'] = utf8_encode($elementi['dati']['MittDest']['Email']);
//        $CorrispondenteOccasionale['Tipo'] = "Persona";




        $Classifica = $elementi['dati']['Classificazione'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri
         */
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        $reqProtocolloPartenza = new itareqProtocolloPartenza();
//        $reqProtocolloPartenza->setOperatore(array(
//                "CodiceUO" => "PROT",
//                "Cognome" => "Cognome1",
//                "Nome" => "Nome1",
//                "Ruolo" => "RESPONSABILE PROTOCOLLISTA"
//                )
//        );
        $reqProtocolloPartenza->setOperatore(array(
            "CodiceUO" => $this->WsOperatorePaleoUO['CONFIG'],
            "Cognome" => $this->WsOperatorePaleoCognome['CONFIG'],
            "Nome" => $this->WsOperatorePaleoNome['CONFIG'],
            "Ruolo" => $this->WsOperatorePaleoRuolo['CONFIG']
                )
        );
        $Operatore = $reqProtocolloPartenza->getOperatore();

        //definizione OperatorePaleo per altri metodi
        $OperatorePaleo = new itaOperatorePaleo();
        $OperatorePaleo->setCodiceUO($Operatore['CodiceUO']);
        $OperatorePaleo->setCognome($Operatore['Cognome']);
        $OperatorePaleo->setNome($Operatore['Nome']);
        $OperatorePaleo->setRuolo($Operatore['Ruolo']);

        //acquisizione CodiceRegistro
        $ret = $paleoClient->ws_GetRegistri($OperatorePaleo);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno = array('value' => '', 'status' => false, 'msg' => $msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno = array('value' => '', 'status' => false, 'msg' => $msg['!']);
                return $ritorno;
            }
            return;
        }
        $risultato = $paleoClient->getResult();
        //$CodiceRegistro="RC_C524"; //lo inizializzo
        $CodiceRegistro = ""; //lo inizializzo
        $CodiceRegistro = $risultato['GetRegistriResult']['Lista']['RegistroInfo']['Codice'];

        $reqProtocolloPartenza->setCodiceRegistro($CodiceRegistro);   //recuperabile dal metodo GetRegistri
        //$reqProtocolloArrivo->setOggetto(utf8_encode("<![CDATA[".$elementi['dati']['Oggetto']."]]>"));
        $reqProtocolloPartenza->setOggetto(utf8_encode($elementi['dati']['Oggetto']));
        $ogg = $reqProtocolloPartenza->getOggetto();
        //$reqProtocolloPartenza->setPrivato(true);
        $reqProtocolloPartenza->setPrivato(false);
        $reqProtocolloPartenza->setDPAI(false);

        //pova per array multiplo
        $CorrispondenteOccasionale2 = array();
        $CorrispondenteOccasionale2['MessaggioRisultato'] = array(
            'Descrizione' => '',
            'TipoRisultato' => 'Info'
        );
        $CorrispondenteOccasionale2['Cognome'] = "";
        $CorrispondenteOccasionale2['Nome'] = "";
        //fine prova array multiplo
        //$reqProtocolloPartenza = $reqProtocolloPartenza->setDestinatari(
        $reqProtocolloPartenza->setDestinatari(
                array(
                    "Corrispondente" => $destinatari
                )
        );


//        $reqProtocolloPartenza->setDestinatari(array(
//            //"CodiceRubrica" => "", //CodiceRubrica="ZT1"
//            //"CorrispondenteOccasionale" => $CorrispondenteOccasionale
//            "Corrispondente" => array(
//                array(
//                    "CodiceRubrica" => "",
//                    "CorrispondenteOccasionale" => $CorrispondenteOccasionale
//                )/* ,
//              array(
//              "CodiceRubrica" => "",
//              "CorrispondenteOccasionale" => $CorrispondenteOccasionale2
//              ) */
//            )
//                )
//        );
        //settaggi Classificazione
        //if ($elementi['dati']['NumProt']!='') {
        if ($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value'] != '') {
            $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
            $CercaDocumentoProtocollo->setDocNumber($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value']);
            $OperatorePaleo = new itaOperatorePaleo();
            $OperatorePaleo->setCodiceUO($Operatore['CodiceUO']);
            $OperatorePaleo->setCognome($Operatore['Cognome']);
            $OperatorePaleo->setNome($Operatore['Nome']);
            $OperatorePaleo->setRuolo($Operatore['Ruolo']);
            $ret = $paleoClient->ws_CercaDocumentoProtocollo($OperatorePaleo, $CercaDocumentoProtocollo);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $ritorno = array('value' => '', 'status' => false, 'msg' => $msg['!']);
                    return $ritorno;
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getFault();
                    $ritorno = array('value' => '', 'status' => false, 'msg' => $msg['!']);
                    return $ritorno;
                }
                return;
            }
            $risultato = $paleoClient->getResult();
            $CodiceFascicolo = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
        } else {
            $CodiceFascicolo = "";
        }

        $Classificazione = array(
            "CodiceFascicolo" => $CodiceFascicolo
        );
        $reqProtocolloPartenza->setClassificazioni(array(
            "Classificazione" => $Classificazione
                )
        );

        //settaggio documenti allegati
        if ($elementi['dati']['DocumentoPrincipale']) {
            //$reqProtocolloArrivo->setDocumentoPrincipale($elementi['dati']['DocumentoPrincipale']);
            $reqProtocolloPartenza->setDocumentoPrincipale(array(
                "Nome" => utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']),
                "Stream" => $elementi['dati']['DocumentoPrincipale']['Stream']
                    )
            );
            //$reqProtocolloArrivo->setDPAI(true);
        }
        if ($elementi['dati']['DocumentiAllegati']) {
            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
            $Allegati = array(
                "Allegato" => $DocAllegati
            );

            $reqProtocolloPartenza->setDocumentiAllegati($Allegati);
            //$reqProtocolloArrivo->setDocumentiAllegati($DocAllegati);
        }

        /**
         * fine settaggio parametri
         */
        /*
          $ret = $paleoClient->ws_ProtocollazionePartenza($reqProtocolloPartenza);
          if (!$ret) {
          if ($paleoClient->getFault()) {
          $msg=$paleoClient->getFault();
          $ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
          return $ritorno;
          } elseif ($paleoClient->getError()) {
          $msg=$paleoClient->getFault();
          $ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
          return $ritorno;
          }
          break;
          }

          $risultato=$paleoClient->getResult();
          $ProtocollazionePartenzaResult=$risultato['ProtocollazionePartenzaResult'];
          $data=(string)$ProtocollazionePartenzaResult['DataProtocollazione'];
          $proNum=substr($data, 0,4).$risultato['ProtocollazionePartenzaResult']['DocNumber'];
          if ($risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['TipoRisultato']=="Error") {
          $ritorno=array('value'=>$proNum,'status'=>false,'msg'=>$risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['Descrizione']);
          } else {
          $ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
          }
          return $ritorno;
         *
         */
        $ret = $paleoClient->ws_ProtocollazionePartenza($reqProtocolloPartenza);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione partenza: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione arrivo: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
            return;
        }

        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            //$ProtocollazioneEntrataResult=$risultato['ProtocollazioneEntrataResult'];
            $Data = (string) $risultato['ProtocollazionePartenzaResult']['DataProtocollazione']; //è nel formato 2012-05-10T11:23:53.377 - formattabile in maniera diversa all'occorrenza
            //$proNum = substr($Data, 0, 4) . $risultato['ProtocollazionePartenzaResult']['Numero'];
            $proNum = $risultato['ProtocollazionePartenzaResult']['Numero'];
            $DocNumber = $risultato['ProtocollazionePartenzaResult']['DocNumber'];
            $Segnatura = $risultato['ProtocollazionePartenzaResult']['Segnatura'];
            $Anno = substr($Data, 0, 4);
            //$ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Paleo', 'status' => true, 'msg' => 'ProtocollazionePartenza'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        }

        return $ritorno;
    }

    function CercaDocumentoProtocollo($Docnumber) {
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);

        $OperatorePaleo = new itaOperatorePaleo();
        $OperatorePaleo->setCodiceUO($this->WsOperatorePaleoUO['CONFIG']);
        $OperatorePaleo->setCognome($this->WsOperatorePaleoCognome['CONFIG']);
        $OperatorePaleo->setNome($this->WsOperatorePaleoNome['CONFIG']);
        $OperatorePaleo->setRuolo($this->WsOperatorePaleoRuolo['CONFIG']);

        $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
        $CercaDocumentoProtocollo->setDocNumber($Docnumber);

//        if ($_POST[$this->name_form . '_CercaDocumentoProtocollo_Segnatura'] != '') {
//            $CercaDocumentoProtocollo->setSegnatura($_POST[$this->name_form . '_CercaDocumentoProtocollo_Segnatura']);
//        }

        $ret = $paleoClient->ws_CercaDocumentoProtocollo($OperatorePaleo, $CercaDocumentoProtocollo);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
            } elseif ($paleoClient->getError()) {
                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
            }
            return;
        }

        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca protocollo: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            $Data = (string) $risultato['CercaDocumentoProtocolloResult']['Data']; //è nel formato 2012-05-10T11:23:53.377 - formattabile in maniera diversa all'occorrenza
            $Numero = $risultato['CercaDocumentoProtocolloResult']['Numero'];
            $DocNumber = $risultato['CercaDocumentoProtocolloResult']['DocNumber'];
            $Segnatura = $risultato['CercaDocumentoProtocolloResult']['Segnatura'];
            $Classifica = $risultato['CercaDocumentoProtocolloResult']['Classificazioni'];
            $Oggetto = $risultato['CercaDocumentoProtocolloResult']['Oggetto'];
            $DocumentiAllegati = array();
            $DocumentiAllegati[] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Nome'];
            $Allegati = $risultato['CercaDocumentoProtocolloResult']['Allegati']['Allegato'];
            if ($Allegati[0]) {
                foreach ($Allegati as $Allegato) {
                    $DocumentiAllegati[] = $Allegato['Documento']['Nome'];
                }
            } else {
                if ($Allegati) {
                    $DocumentiAllegati[] = $Allegati['Documento']['Nome'];
                }
            }
            $datiSegnatura = explode("|", $Segnatura);
            $Tipo = $datiSegnatura[4];
            $Anno = substr($Data, 0, 4);
            //$ritorno=array('value'=>$proNum,'status'=>true,'msg'=>'');
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";
            $datiProtocollo = array(
                'TipoProtocollo' => 'Paleo',
                'NumeroProtocollo' => $Numero,
                'Data' => $Data,
                'DocNumber' => $DocNumber,
                'Segnatura' => $Segnatura,
                'Anno' => $Anno,
                'Classifica' => $Classifica,
                'Oggetto' => $Oggetto,
                'DocumentiAllegati' => $DocumentiAllegati
            );
            if ($Tipo == 'P') {
                $dati['Destinatari'] = $risultato['CercaDocumentoProtocolloResult']['Destinatari'];
            } else {
                $dati['Mittente'] = $risultato['CercaDocumentoProtocolloResult']['Mittente'];
            }
            $ritorno["RetValue"] = array(
                'DatiProtocollo' => $datiProtocollo
            );
        }
        return $ritorno;
    }

    function registraProtocollo($uffici) {
        return "OK";
    }

    public function CodificaStringa($string) {
        $string = str_replace("à", "a", $string);
        $string = str_replace("è", "e", $string);
        $string = str_replace("é", "e", $string);
        $string = str_replace("ì", "i", $string);
        $string = str_replace("ò", "o", $string);
        $string = str_replace("ù", "u", $string);
        $string = str_replace("?", "Euro", $string);

        $encoded_string = utf8_encode($string);
        return $encoded_string;
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_PALEO;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->protocollazionePartenza($elementi, "P");
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->protocollazioneEntrata($elementi);
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->InserisciDocumentoEAnagrafiche($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiDocumento($params);
    }

}

?>