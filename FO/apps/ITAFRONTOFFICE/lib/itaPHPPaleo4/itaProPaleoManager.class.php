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
//require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaPaleoClient.class.php');
require_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloArrivo.class.php');
require_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloPartenza.class.php');
require_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaCercaDocumentoProtocollo.class.php');
require_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaOperatorePaleo.class.php');

class itaProPaleoManager {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Paleo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    //private $param = array();
    private $clientParam;
    public $praLib;
    public $accLib;
    private $WsOperatorePaleoUO;
    private $WsOperatorePaleoCognome;
    private $WsOperatorePaleoNome;
    private $WsOperatorePaleoRuolo;

//    function __construct() {
//        $this->praLib = new praLib();
//    }

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaProPaleoManager();
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

    private function setClientConfig($paleoClient) {
        $CodAmm = $this->clientParam['WSPALEO4CODAMM'];
        $paleoClient->setWebservices_uri($this->clientParam['WSPALEO4ENDPOINT']);
        $paleoClient->setWebservices_wsdl($this->clientParam['WSPALEO4WSDL']);
        $paleoClient->setUsername($CodAmm . "\\" . $this->clientParam['WSPALEO4USERNAME']);
        $paleoClient->setpassword($this->clientParam['WSPALEO4PASSWORD']);
        $paleoClient->setTimeout($this->clientParam['WSPALEO4TIMEOUT']);
        $paleoClient->setCurl_ssl_cipher($this->clientParam['WSPALEO4CURLCIPHER']);
        $this->WsOperatorePaleoUO = $this->clientParam['WSPALEO4UNITAOPE'];
        $this->WsOperatorePaleoCognome = $this->clientParam['WSPALEO4COGNOME'];
        $this->WsOperatorePaleoNome = $this->clientParam['WSPALEO4NOME'];
        $this->WsOperatorePaleoRuolo = $this->clientParam['WSPALEO4RUOLO'];
    }

    //public function protocollazioneEntrata($elementi) {
    public function InserisciProtocollo($elementi) {

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

        //$Classifica = $elementi['dati']['Classificazione'];
        /*
         * fine acquisizione parametri
         */

        /**
         * settaggio dei parametri per la protocollazione
         */
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        $reqProtocolloArrivo = new itareqProtocolloArrivo();


//        //questo è l'Operatore del sistema che esegue di fatto la protocollazione, deve essere un utente abilitato.
//        $UteCod = App::$utente->getKey('idUtente');
//        $this->accLib = new accLib();
//        $OperatoreLogin = $this->accLib->GetOperatorePaleo($UteCod);
//        //se all'utente connesso è associato un operatore paleo lo seleziono, altrimenti lo prendo dai parametri
//        //secondo disposizioni della regione questo utente è semrpe fittizio, quindi basta non associare agli utenti alcun operatore
//        if ($OperatoreLogin['CodiceUO'] != '' && $OperatoreLogin['Cognome'] != '' && $OperatoreLogin['Ruolo'] != '') {
//            $reqProtocolloArrivo->setOperatore(array(
//                "CodiceUO" => $OperatoreLogin['CodiceUO'],
//                "Cognome" => $OperatoreLogin['Cognome'],
//                "Nome" => $OperatoreLogin['Nome'],
//                "Ruolo" => $OperatoreLogin['Ruolo'],
//                    )
//            );
//        } else {
//            $reqProtocolloArrivo->setOperatore(array(
//                "CodiceUO" => $this->WsOperatorePaleoUO['CONFIG'],
//                "Cognome" => $this->WsOperatorePaleoCognome['CONFIG'],
//                "Nome" => $this->WsOperatorePaleoNome['CONFIG'],
//                "Ruolo" => $this->WsOperatorePaleoRuolo['CONFIG']
//                    )
//            );
//        }


        $reqProtocolloArrivo->setOperatore(array(
            "CodiceUO" => $this->WsOperatorePaleoUO,
            "Cognome" => $this->WsOperatorePaleoCognome,
            "Nome" => $this->WsOperatorePaleoNome,
            "Ruolo" => $this->WsOperatorePaleoRuolo
                )
        );

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
                $ritorno["Message"] = "(Fault) Rilevato un errore cercando il registro: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore cercando il registro: <br>" . $msg . "";
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
        //if ($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value'] != '') {
        if ($elementi['dati']['DocNumberProtocolloAntecedente'] != '') {
            $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
            //$CercaDocumentoProtocollo->setDocNumber($elementi['dati']['MetaDati']['DatiProtocollazione']['DocNumber']['value']);
            $CercaDocumentoProtocollo->setDocNumber($elementi['dati']['DocNumberProtocolloAntecedente']);
            $ret = $paleoClient->ws_CercaDocumentoProtocollo($OperatorePaleo, $CercaDocumentoProtocollo);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il documento num : " . $elementi['dati']['DocNumberProtocolloAntecedente'] . "<br>" . $msg . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il documento num : " . $elementi['dati']['DocNumberProtocolloAntecedente'] . "<br>" . $msg . "";
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
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il il documento num : " . $elementi['dati']['DocNumberProtocolloAntecedente'] . "<br>" . $DescrizioneRisultato . "";
                $ritorno["RetValue"] = false;
            } else {
                $ritorno = array();
                $ritorno["Status"] = "0";
                $ritorno["Message"] = "Ricerca Registro avvenuta con successo!";
                $ritorno["RetValue"] = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
            }
            if ($ritorno['Status'] == "0") {
                $annoDocumentoPrec = substr($risultato['CercaDocumentoProtocolloResult']['DataDocumento'], 0, 4);
                if (is_array($ritorno["RetValue"])) {
                    foreach ($ritorno["RetValue"] as $fascicolo) {
                        if (strpos($fascicolo, "/$annoDocumentoPrec/") !== false) {
                            $CodiceFascicolo = $fascicolo;
                            break;
                        }
                    }
                } else {
                    $CodiceFascicolo = $ritorno["RetValue"];
                }
                if ($CodiceFascicolo == "") {
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "Codice Fascicolo Padre non trovato.";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                }
            } else {
                return $ritorno;
            }
            //$CodiceFascicolo=$risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
        } else {
            if ($elementi['dati']['Classificazione'] != '') {
                $NuovoFascicolo['CodiceClassifica'] = $elementi['dati']['Classificazione'];
            } else {
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Classificazione non trovata. La procedura sarà interrotta";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
//            $NuovoFascicolo['Custode']['CodiceUO']=$elementi['dati']['CustodePaleo']->GetCodiceUO();
//            $NuovoFascicolo['Custode']['Cognome']=  utf8_encode($elementi['dati']['CustodePaleo']->GetCognome());
//            $NuovoFascicolo['Custode']['Nome']=  utf8_encode($elementi['dati']['CustodePaleo']->GetNome());
//            $NuovoFascicolo['Custode']['Ruolo']=$elementi['dati']['CustodePaleo']->GetRuolo();
            //modifica per prendere lo stesso operatore dei parametri
            $NuovoFascicolo['Custode']['CodiceUO'] = $this->WsOperatorePaleoUO;
            $NuovoFascicolo['Custode']['Cognome'] = $this->WsOperatorePaleoCognome;
            $NuovoFascicolo['Custode']['Nome'] = $this->WsOperatorePaleoNome;
            $NuovoFascicolo['Custode']['Ruolo'] = $this->WsOperatorePaleoRuolo;
//            $NuovoFascicolo['Custode']['CodiceUO'] = $this->WsOperatorePaleoUO['CONFIG'];
//            $NuovoFascicolo['Custode']['Cognome'] = $this->WsOperatorePaleoCognome['CONFIG'];
//            $NuovoFascicolo['Custode']['Nome'] = $this->WsOperatorePaleoNome['CONFIG'];
//            $NuovoFascicolo['Custode']['Ruolo'] = $this->WsOperatorePaleoRuolo['CONFIG'];
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
                "Stream" => $elementi['dati']['DocumentoPrincipale']['Stream'],
                "Descrizione" => $elementi['dati']['DocumentoPrincipale']['Descrizione'],
                    )
            );
        }

//        if ($elementi['dati']['DocumentiAllegati']) {
//            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
//            foreach ($DocAllegati as $key => $record) {
//                $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
//                $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
//            }
//            $Allegati = array(
//                "Allegato" => $DocAllegati
//            );
//            $reqProtocolloArrivo->setDocumentiAllegati($Allegati);
//        }

        /**
         * fine settaggio parametri
         */
        $ret = $paleoClient->ws_ProtocollazioneEntrata($reqProtocolloArrivo);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }

        $risultato = $paleoClient->getResult();

//        Array
//(
//    [ProtocollazioneEntrataResult] => Array
//        (
//            [MessaggioRisultato] => Array
//                (
//                    [Descrizione] => startIndex cannot be larger than length of string.
//Parameter name: startIndex
//                    [TipoRisultato] => Error
//                )
//
//            [Classificazioni] => 
//            [DataDocumento] => 0001-01-01T00:00:00
//            [DatiProcedimento] => 
//            [DocNumber] => 0
//            [Oggetto] => 
//            [SegnaturaDocumento] => 
//            [Annullato] => false
//            [Data] => 0001-01-01T00:00:00
//            [DataProtocollazione] => 0001-01-01T00:00:00
//            [Numero] => 
//            [Registro] => 
//            [Segnatura] => 
//            [DataArrivo] => 0001-01-01T00:00:00
//            [Mittente] => 
//            [ProtocolloMittente] => 
//        )
//
//)


        $TipoRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            $Data = (string) $risultato['ProtocollazioneEntrataResult']['DataProtocollazione']; //è nel formato 2012-05-10T11:23:53.377
            $proNum = $risultato['ProtocollazioneEntrataResult']['Numero'];
            $DocNumber = $risultato['ProtocollazioneEntrataResult']['DocNumber'];
            $Segnatura = $risultato['ProtocollazioneEntrataResult']['Segnatura'];
            $Anno = substr($Data, 0, 4);
            //gestione return false
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Paleo4', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
                    'proNum' => array('value' => $proNum, 'status' => true, 'msg' => ''),
                    'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
                )
            );
        }


        //
        //Aggiungo gli allegati uno ad uno con il nuovo metodo
        //
        $DocAllegati = $elementi['dati']['DocumentiAllegati'];
        $err_allegati = array();
        $err_n = 0;
        foreach ($DocAllegati as $key => $record) {
            $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
            $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $DocNumber, $Segnatura, $DocAllegati[$key]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->$msg</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->$msg</div>";
                }
                $err_allegati[$err_n] = $errString;
                $err_n++;
            }
        }

        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '<br>';
            }
        }

        $ritorno['errString'] = $err_str;
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
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un errore durante l'aggiunta dell'allegato: " . $msg['!'];
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore durante l'aggiunta dell'allegato: " . $msg['!'];
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
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

}

?>