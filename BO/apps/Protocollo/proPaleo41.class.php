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
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaPaleoClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloArrivo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloPartenza.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqArchiviaDocInterno.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaCercaDocumentoProtocollo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaGetFile.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaOperatorePaleo4.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClientHelper.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proWsClient.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class proPaleo41 extends proWsClient {

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
        $uri = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPALEO4ENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $paleoClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPALEO4WSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $paleoClient->setWebservices_wsdl($wsdl2);
        $CodAmm = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'CODAMMINISTRAZIONEPALEO4', false);
        $CodAmm2 = $CodAmm['CONFIG'];
        $username = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSUTENTEPALEO4', false);
        $username2 = $username['CONFIG'];
        $password = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPASSWORDPALEO4', false);
        $password2 = $password['CONFIG'];
        $paleoClient->setUsername($CodAmm2 . "\\" . $username2);
        $paleoClient->setpassword($password2);
        $timeout = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSEXECUTIONTIMEOUT4', false);
        $paleoClient->setTimeout($timeout['CONFIG']);
        $curl_ssl_cipher = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSSSLCIPHERPALEO4', false);
        $paleoClient->setCurl_ssl_cipher($curl_ssl_cipher['CONFIG']);
        //settaggio parametri operatore che effettua la chiamata a WS
        $this->WsOperatorePaleoUO = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4UO', false);
        $this->WsOperatorePaleoCognome = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4COGNOME', false);
        $this->WsOperatorePaleoNome = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4NOME', false);
        $this->WsOperatorePaleoRuolo = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4RUOLO', false);
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
        $OperatorePaleo = new itaOperatorePaleo4();
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
                    $ritorno["Message"] = "(Fault) Rilevato un errore cercando il Protocollo: <br>" . $msg['!'] . "";
                    $ritorno["RetValue"] = false;
                    return $ritorno;
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $ritorno["Status"] = "-1";
                    $ritorno["Message"] = "(Error) Rilevato un errore cercando il Protocollo: <br>" . $msg['!'] . "";
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
                $ritorno["Message"] = "(Errore) Rilevato un errore cercando il Protocollo: <br>" . $DescrizioneRisultato . "";
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
        if ($elementi['dati']['DocumentoPrincipale']) {
            $ext = pathinfo($elementi['dati']['DocumentoPrincipale']['Nome'], PATHINFO_EXTENSION);
            $reqProtocolloArrivo->setDocumentoPrincipale(array(
                "Estensione" => $ext,
                "Impronta" => hash('sha256', base64_decode($elementi['dati']['DocumentoPrincipale']['Stream'])),
                "MimeType" => itaMimeTypeUtils::getMimeTypes($ext, true),
                "Nome" => utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']),
                "Stream" => $elementi['dati']['DocumentoPrincipale']['Stream'],
                    )
            );
        }

        /*
         * Da fare sempre. vecchie versioni paleo no
         * 
         */
        if ($elementi['dati']['DocumentiAllegati']) {
            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
            foreach ($DocAllegati as $key => $record) {
                $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
                $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
                $DocAllegati[$key]['Documento']['Estensione'] = $ext;
                $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
                $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
                $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
                unset($DocAllegati[$key]['Documento']['Stream']);
            }
            $Allegati = array(
                "Allegato" => $DocAllegati
            );
            $reqProtocolloArrivo->setDocumentiAllegati($Allegati);
        }

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
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore in fase di protocollazione: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
        }

        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ProtocollazioneEntrataResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "(Errore) Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $Data = (string) $risultato['ProtocollazioneEntrataResult']['DataProtocollazione']; //è nel formato 2012-05-10T11:23:53.377
            $proNum = $risultato['ProtocollazioneEntrataResult']['Numero'];
            $DocNumber = $risultato['ProtocollazioneEntrataResult']['DocNumber'];
            $Segnatura = $risultato['ProtocollazioneEntrataResult']['Segnatura'];
            $Anno = substr($Data, 0, 4);
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Protocollazione avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Paleo41', 'status' => true, 'msg' => 'ProtocollazioneArrivo'),
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
            $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
            $DocAllegati[$key]['Descrizione'] = utf8_decode($record['Descrizione']);
            $DocAllegati[$key]['Documento']['Estensione'] = $ext;
            $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
            $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $DocNumber, $Segnatura, $DocAllegati[$key]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg["!"] . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante la protocollazione dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg["!"] . "</div>";
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
            Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al protocollo n. $proNum dell'anno $Anno<br>
                Procedere manualmente per allegare i seguenti documenti:<br>" . $err_str);
        }
        return $ritorno;
    }

    public function ArchiviaDocumentoInterno($elementi) {
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

        /**
         * settaggio dei parametri
         */
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        $reqArchiviaDocInterno = new itareqArchiviaDocInterno();
//        $reqArchiviaDocInterno->setOperatore(array(
//                "CodiceUO" => "PROT",
//                "Cognome" => "Cognome1",
//                "Nome" => "Nome1",
//                "Ruolo" => "RESPONSABILE PROTOCOLLISTA"
//                )
//        );
        $reqArchiviaDocInterno->setOperatore(array(
            "CodiceUO" => $this->WsOperatorePaleoUO['CONFIG'],
            "Cognome" => $this->WsOperatorePaleoCognome['CONFIG'],
            "Nome" => $this->WsOperatorePaleoNome['CONFIG'],
            "Ruolo" => $this->WsOperatorePaleoRuolo['CONFIG']
                )
        );
        $Operatore = $reqArchiviaDocInterno->getOperatore();

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

        $reqArchiviaDocInterno->setCodiceRegistro($CodiceRegistro);   //recuperabile dal metodo GetRegistri
        $reqArchiviaDocInterno->setOggetto(utf8_encode($elementi['dati']['Oggetto']));
        //$reqArchiviaDocInterno->setPrivato(true);
        $reqArchiviaDocInterno->setPrivato(false);
        $reqArchiviaDocInterno->setDPAI(false);

        //pova per array multiplo
        $CorrispondenteOccasionale2 = array();
        $CorrispondenteOccasionale2['MessaggioRisultato'] = array(
            'Descrizione' => '',
            'TipoRisultato' => 'Info'
        );
        $CorrispondenteOccasionale2['Cognome'] = "";
        $CorrispondenteOccasionale2['Nome'] = "";
        //fine prova array multiplo
        //$reqArchiviaDocInterno = $reqArchiviaDocInterno->setDestinatari(
//        $reqArchiviaDocInterno->setDestinatari(
//                array(
//                    "Corrispondente" => $destinatari
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
        $reqArchiviaDocInterno->setClassificazioni(array(
            "Classificazione" => $Classificazione
                )
        );

        //settaggio documenti allegati
        /*
         * Se non c'è il documento principale, prendo il primo dagli altri allegati
         */
        $docPrinc = $elementi['dati']['DocumentoPrincipale'];
        if (!$docPrinc) {
            $docPrinc = $elementi['dati']['DocumentiAllegati'][0]['Documento'];
            unset($elementi['dati']['DocumentiAllegati'][0]);
        }

//        if ($docPrinc) {
//            $reqArchiviaDocInterno->setDocumentoPrincipale(array(
//                "Nome" => utf8_encode($docPrinc['Nome']),
//                "Stream" => $docPrinc['Stream']
//                    )
//            );
//        }


        if ($docPrinc) {
            $ext = pathinfo($docPrinc['Nome'], PATHINFO_EXTENSION);
            $reqArchiviaDocInterno->setDocumentoPrincipale(array(
                "Estensione" => $ext,
                "Impronta" => hash('sha256', base64_decode($docPrinc['Stream'])),
                "MimeType" => itaMimeTypeUtils::getMimeTypes($ext, true),
                "Nome" => utf8_encode($docPrinc['Nome']),
                "Stream" => $docPrinc['Stream'],
                    )
            );
        }



        if ($elementi['dati']['DocumentiAllegati']) {
            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
            foreach ($DocAllegati as $key => $record) {
                $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
                $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
                $DocAllegati[$key]['Documento']['Estensione'] = $ext;
                $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
                $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
                $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
                unset($DocAllegati[$key]['Documento']['Stream']);
            }
            $Allegati = array(
                "Allegato" => $DocAllegati
            );
            $reqArchiviaDocInterno->setDocumentiAllegati($Allegati);
        }

        $ret = $paleoClient->ws_ArchiviaDocumentoInterno($reqArchiviaDocInterno);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di archiviazione documento interno: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di archiviazione documento interno: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
        }

        $risultato = $paleoClient->getResult();



//        Out::msgInfo("", print_r($risultato, true));
//        $ritorno["Status"] = "-1";
//        $ritorno["Message"] = "errore";
//        $ritorno["RetValue"] = false;
//        return $ritorno;


        $TipoRisultato = $risultato['ArchiviaDocumentoInternoResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ArchiviaDocumentoInternoResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di archiviazione documento interno: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $Data = (string) $risultato['ArchiviaDocumentoInternoResult']['DataDocumento']; //è nel formato 2012-05-10T11:23:53.377 - formattabile in maniera diversa all'occorrenza
            $DocNumber = $risultato['ArchiviaDocumentoInternoResult']['DocNumber'];
            $Segnatura = $risultato['ArchiviaDocumentoInternoResult']['SegnaturaDocumento'];
            $Anno = substr($Data, 0, 4);
            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Archiviazione Documento Interno avvenuta con successo!";
            $ritorno["RetValue"] = array(
                'DatiProtocollazione' => array(
                    'TipoProtocollo' => array('value' => 'Paleo41', 'status' => true, 'msg' => 'ArchiviaDocumentoInterno'),
                    'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                    'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                    'Anno' => array('value' => $Anno, 'status' => true, 'msg' => ''),
                    'DataDoc' => array('value' => date("Y-m-d"), 'status' => true, 'msg' => ''),
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
//            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
//            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
//            $DocAllegati[$key]['Descrizione'] = $record['Descrizione'];
            $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
            $DocAllegati[$key]['Descrizione'] = utf8_decode($record['Descrizione']);
            $DocAllegati[$key]['Documento']['Estensione'] = $ext;
            $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
            $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $DocNumber, $Segnatura, $DocAllegati[$key]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta dell'allegato: " . $record['Documento']['Nome'] . " ---> " . $msg . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta dell'allegato: " . $record['Documento']['Nome'] . " ---> " . $msg . "</div>";
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
            Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al documento interno n. $DocNumber dell'anno $Anno<br>
                Procedere manualmente per allegare i seguenti documenti:<br>" . $err_str);
        }

        return $ritorno;
    }

    public function AggiungiAllegati($param) {
        $ritorno = array();
        $ritorno["Status"] = "0";
        $ritorno["Message"] = "Documenti allegati con successo!";
        $ritorno["RetValue"] = true;
        if (!$param['arrayDoc']['Principale'] && !$param['arrayDoc']['Allegati'] && !$param['arrayDocRicevute']['Ricevute']) {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Nessun documento da allegare";
            $ritorno["RetValue"] = false;
            return $ritorno;
        }
        //
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        //definizione OperatorePaleo per altri metodi
        $OperatorePaleo = new itaOperatorePaleo4();
        $OperatorePaleo->setCodiceUO($this->WsOperatorePaleoUO['CONFIG']);
        $OperatorePaleo->setCognome($this->WsOperatorePaleoCognome['CONFIG']);
        $OperatorePaleo->setNome($this->WsOperatorePaleoNome['CONFIG']);
        $OperatorePaleo->setRuolo($this->WsOperatorePaleoRuolo['CONFIG']);
        //
        $errString = "";
        $Principale = $param['arrayDoc']['Principale'];
        $DocAllegati = $param['arrayDoc']['Allegati'];
        $DocAllegatiRicevute = $param['arrayDocRicevute']['Ricevute'];
        $err_allegati = array();
        $err_n = 0;
        $ret_allegati = array();
        $ret_n = 0;

        /*
         * Aggiungo i principale
         */
        if ($Principale) {
            $princ = array();
            $princ['Documento']['Nome'] = $Principale['Nome'];
            $princ['Documento']['Stream'] = $Principale['Stream'];
            $princ['Descrizione'] = utf8_decode($Principale['Descrizione']);
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $param['DocNumber'], $param['Segnatura'], $princ);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta dell'allegato: <b>" . $Principale['Nome'] . "</b>--->" . $msg . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $Principale['Nome'] . "</b>--->" . $msg . "</div>";
                }
                $err_allegati[$err_n] = $errString;
                $err_n++;
            } else {
                $result = $paleoClient->getResult();
                if ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Error") {
                    $err_allegati[$err_n] = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $Principale['Nome'] . "</b>--->" . $result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'] . "</div>";
                    $err_n++;
                } elseif ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Info") {
                    $ret_allegati[$ret_n] = "<div>- Aggiunto correttamente l'allegato: <b>" . $Principale['Nome'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "</div>";
                    $ret_n++;
                }
            }
        }

        /*
         * Mi scorro gli allegati selezionati
         */
        foreach ($DocAllegati as $key => $record) {
            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
            $DocAllegati[$key]['Descrizione'] = utf8_decode($record['Descrizione']);
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $param['DocNumber'], $param['Segnatura'], $DocAllegati[$key]);

            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg . "</div>";
                }
                $err_allegati[$err_n] = $errString;
                $err_n++;
            } else {
                $result = $paleoClient->getResult();
                if ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Error") {
                    $err_allegati[$err_n] = "<div>- Errore durante l'aggiunta dell'allegato: <b>" . $record['Documento']['Nome'] . "</b>--->" . $result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'] . "</div>";
                    $err_n++;
                } elseif ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Info") {
                    $ret_allegati[$ret_n] = "<div>- Aggiunto correttamente l'allegato: <b>" . $record['Documento']['Nome'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "</div>";
                    $ret_n++;
                }
            }
        }

        /*
         * Mi scorro le ricevute di accettazione e consegna
         */
        foreach ($DocAllegatiRicevute as $keyRic => $record) {
            $DocAllegati[$keyRic]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$keyRic]['Documento']['Stream'] = $record['Documento']['Stream'];
            $DocAllegati[$keyRic]['Descrizione'] = utf8_decode($record['Descrizione']);
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $param['DocNumber'], $param['Segnatura'], $DocAllegati[$keyRic]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta della ricevuta <b>" . $record['Documento']['Nome'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "---><span style=\"color:red;\"><b>$msg</b></span></div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta della ricevuta: <b>" . $record['Documento']['Nome'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "---><span style=\"color:red;\"><b>$msg</b></span></div>";
                }
                $err_allegati[$err_n] = $errString;
                $err_n++;
            } else {
                $result = $paleoClient->getResult();
                if ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Error") {
                    $err_allegati[$err_n] = "<div>- Errore durante l'aggiunta della ricevuta: <b>" . $record['Documento']['Nome'] . "</b>--->" . $result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'] . "</div>";
                    $err_n++;
                } elseif ($result['AddAllegatiDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'] == "Info") {
                    $ret_allegati[$ret_n] = "<div>- Aggiunta correttamente la ricevuta: <b>" . $record['Documento']['Nome'] . "</b> al prot. num. " . $param['NumeroProtocollo'] . " anno " . $param['AnnoProtocollo'] . "</div>";
                    $ret_n++;
                }
            }
        }

        //gestione messaggio in caso di errori
        if ($err_n > 0) {
            $ritorno["Status"] = "-1";
            $err_str = '';
            foreach ($err_allegati as $err_nome) {
                $err_str .= $err_nome . '<br>';
            }
            $ritorno["Message"] = $err_str;
            $ritorno["ErrDetails"] = $err_allegati;
            $ritorno["RetValue"] = false;
//            Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al protocollo n. " . $param['proNum'] . " dell'anno " . $param['Anno'] . "<br>
//                Procedere manualmente per allegare i seguenti documenti:<br>" . $err_str);
        }
        $ritorno["RetDetails"] = $ret_allegati;
        return $ritorno;
    }

    public function protocollazionePartenza($elementi) {
//        Out::msgInfo("", print_r($elementi['dati'], true));
//        return false;

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
        //$Classifica = $elementi['dati']['Classificazione'];
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
        $OperatorePaleo = new itaOperatorePaleo4();
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
            $OperatorePaleo = new itaOperatorePaleo4();
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
            //$CodiceFascicolo = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
            $arrFascicoli = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
            $annoDocumentoPrec = substr($risultato['CercaDocumentoProtocolloResult']['DataDocumento'], 0, 4);
            if (is_array($risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'])) {
                foreach ($arrFascicoli as $fascicolo) {
                    if (strpos($fascicolo, "/$annoDocumentoPrec/") !== false) {
                        $CodiceFascicolo = $fascicolo;
                        break;
                    }
                }
            } else {
                $CodiceFascicolo = $risultato['CercaDocumentoProtocolloResult']['Classificazioni']['string'];
            }
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
//        if ($elementi['dati']['DocumentoPrincipale']) {
//            $reqProtocolloPartenza->setDocumentoPrincipale(array(
//                "Nome" => utf8_encode($elementi['dati']['DocumentoPrincipale']['Nome']),
//                "Stream" => $elementi['dati']['DocumentoPrincipale']['Stream']
//                    )
//            );
//        }

        /*
         * Se non c'è il documento principale, prendo il primo dagli altri allegati
         */
        $docPrinc = $elementi['dati']['DocumentoPrincipale'];
        if (!$docPrinc) {
            $docPrinc = $elementi['dati']['DocumentiAllegati'][0]['Documento'];
            unset($elementi['dati']['DocumentiAllegati'][0]);
        }

        if ($docPrinc) {
//            $reqProtocolloPartenza->setDocumentoPrincipale(array(
//                "Nome" => utf8_encode($docPrinc['Nome']),
//                "Stream" => $docPrinc['Stream']
//                    )
//            );
            $ext = pathinfo($docPrinc['Nome'], PATHINFO_EXTENSION);
            $reqProtocolloPartenza->setDocumentoPrincipale(array(
                "Estensione" => $ext,
                "Impronta" => hash('sha256', base64_decode($docPrinc['Stream'])),
                "MimeType" => itaMimeTypeUtils::getMimeTypes($ext, true),
                "Nome" => utf8_encode($docPrinc['Nome']),
                "Stream" => $docPrinc['Stream'],
                    )
            );
        }



        if ($elementi['dati']['DocumentiAllegati']) {
            $DocAllegati = $elementi['dati']['DocumentiAllegati'];
            foreach ($DocAllegati as $key => $record) {
                $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
                $DocAllegati[$key]['Descrizione'] = utf8_encode($record['Descrizione']);
                $DocAllegati[$key]['Documento']['Estensione'] = $ext;
                $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
                $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
                $DocAllegati[$key]['Documento']['Nome'] = utf8_encode($record['Documento']['Nome']);
                unset($DocAllegati[$key]['Documento']['Stream']);
            }
            $Allegati = array(
                "Allegato" => $DocAllegati
            );
            $reqProtocolloPartenza->setDocumentiAllegati($Allegati);
        }

        $ret = $paleoClient->ws_ProtocollazionePartenza($reqProtocolloPartenza);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione partenza: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getError();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "Rilevato un errore in fase di protocollazione arrivo: <br>" . $msg . "";
                $ritorno["RetValue"] = false;
                //$ritorno=array('value'=>'','status'=>false,'msg'=>$msg['!']);
                return $ritorno;
            }
        }

        $risultato = $paleoClient->getResult();
        $TipoRisultato = $risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['ProtocollazionePartenzaResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di protocollazione: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
            return $ritorno;
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
                    'TipoProtocollo' => array('value' => 'Paleo41', 'status' => true, 'msg' => 'ProtocollazionePartenza'),
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
//            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
//            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
//            $DocAllegati[$key]['Descrizione'] = $record['Descrizione'];
            $ext = pathinfo($record['Documento']['Nome'], PATHINFO_EXTENSION);
            $DocAllegati[$key]['Descrizione'] = utf8_decode($record['Descrizione']);
            $DocAllegati[$key]['Documento']['Estensione'] = $ext;
            $DocAllegati[$key]['Documento']['Impronta'] = hash('sha256', base64_decode($record['Documento']['Stream']));
            $DocAllegati[$key]['Documento']['MimeType'] = itaMimeTypeUtils::getMimeTypes($ext, true);
            $DocAllegati[$key]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$key]['Documento']['Stream'] = $record['Documento']['Stream'];
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $DocNumber, $Segnatura, $DocAllegati[$key]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta dell'allegato: " . $record['Documento']['Nome'] . " ---> " . $msg . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta dell'allegato: " . $record['Documento']['Nome'] . " ---> " . $msg . "</div>";
                }
                $err_allegati[$err_n] = $errString;
                $err_n++;
            }
        }

        /*
         * Mi scorro le ricevute di accettazione e consegna
         */
        foreach ($DocAllegatiRicevute as $keyRic => $record) {
            $DocAllegati[$keyRic]['Documento']['Nome'] = $record['Documento']['Nome'];
            $DocAllegati[$keyRic]['Documento']['Stream'] = $record['Documento']['Stream'];
            $DocAllegati[$keyRic]['Descrizione'] = utf8_decode($record['Descrizione']);
            $ret = $paleoClient->ws_AddAllegatiDocumentoProtocollo($OperatorePaleo, $param['DocNumber'], $param['Segnatura'], $DocAllegati[$keyRic]);
            if (!$ret) {
                if ($paleoClient->getFault()) {
                    $msg = $paleoClient->getFault();
                    $errString = "<div>- Fault durante l'aggiunta della ricevuta: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg["!"] . "</div>";
                } elseif ($paleoClient->getError()) {
                    $msg = $paleoClient->getError();
                    $errString = "<div>- Errore durante l'aggiunta della ricevuta: <b>" . $record['Documento']['Nome'] . "</b>--->" . $msg["!"] . "</div>";
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
            Out::msgStop("Attenzione", "Sono stati rilevati errori allegando i documenti al protocollo n. $proNum dell'anno $Anno<br>
                Procedere manualmente per allegare i seguenti documenti:<br>" . $err_str);
        }

        return $ritorno;
    }

    //function LeggiProtocollo($Docnumber) {
    function LeggiProtocollo($param) {
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);

        $OperatorePaleo = new itaOperatorePaleo4();
        $OperatorePaleo->setCodiceUO($this->WsOperatorePaleoUO['CONFIG']);
        $OperatorePaleo->setCognome($this->WsOperatorePaleoCognome['CONFIG']);
        $OperatorePaleo->setNome($this->WsOperatorePaleoNome['CONFIG']);
        $OperatorePaleo->setRuolo($this->WsOperatorePaleoRuolo['CONFIG']);

        $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
        if ($param['Docnumber']) {
            $CercaDocumentoProtocollo->setDocNumber($param['Docnumber']);
        }
        if ($param['Segnatura']) {
            $CercaDocumentoProtocollo->setSegnatura($param['Segnatura']);
        }

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

        if (isset($risultato['CercaDocumentoProtocolloResult']['DataArrivo'])) {
            $origine = "A";
            $msg = "ProtocollazioneArrivo";
        } else {
            $origine = "P";
            $msg = "ProtocollazionePartenza";
        }
        //
        $TipoRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['TipoRisultato'];
        $DescrizioneRisultato = $risultato['CercaDocumentoProtocolloResult']['MessaggioRisultato']['Descrizione'];
        //gestione del messaggio d'errore
        if ($TipoRisultato == "Error") {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = "Rilevato un errore in fase di ricerca protocollo: <br>" . $DescrizioneRisultato . "";
            $ritorno["RetValue"] = false;
        } else {
            if (isset($risultato['CercaDocumentoProtocolloResult']['SegnaturaDocumento']) && $risultato['CercaDocumentoProtocolloResult']['SegnaturaDocumento']) {
                $msg = "ArchiviaDocInterno";
                $Segnatura = $risultato['CercaDocumentoProtocolloResult']['SegnaturaDocumento'];
                $DataDoc = (string) $risultato['CercaDocumentoProtocolloResult']['DataDocumento']; //è nel formato 2012-05-10T11:23:53.377 - formattabile in maniera diversa all'occorrenza
            } else {
                $Segnatura = $risultato['CercaDocumentoProtocolloResult']['Segnatura'];
                $Data = (string) $risultato['CercaDocumentoProtocolloResult']['Data']; //è nel formato 2012-05-10T11:23:53.377 - formattabile in maniera diversa all'occorrenza
                $Numero = $risultato['CercaDocumentoProtocolloResult']['Numero'];
            }

            $DocNumber = $risultato['CercaDocumentoProtocolloResult']['DocNumber'];

            $Classifica = $risultato['CercaDocumentoProtocolloResult']['Classificazioni'];
            $Oggetto = $risultato['CercaDocumentoProtocolloResult']['Oggetto'];
            $DocumentiAllegati = array();
            if ($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']) {
                $DocumentiAllegati[] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Nome'] . "." . $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Estensione'];
            }

            if ($risultato['CercaDocumentoProtocolloResult']['Allegati']) {
                $Allegati = $risultato['CercaDocumentoProtocolloResult']['Allegati']['Allegato'];
                if ($Allegati) {
                    if (!$Allegati[0]) {
                        $Allegati = array($Allegati);
                    }
                }
                foreach ($Allegati as $Allegato) {
                    $DocumentiAllegati[] = $Allegato['Documento']['Nome'] . "." . $Allegato['Documento']['Estensione'];
                }
            }

            /*
             * Array Allegati per importazione da protocollo
             */
            $arrayDoc = array();
            if ($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']) {
//                if (isset($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Stream'])) {
//                    $arrayDoc[0]['Stream'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Stream'];
//                } else if (isset($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['IdFIle'])) {
//                    $retGetFile = $this->GetFile($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['IdFile']);
//                    if ($retGetFile['Status'] == "-1") {
//                        return $retGetFile;
//                    }
//                    $arrayDoc[0]['Stream'] = $retGetFile['Stream'];
//                }
                $stream = $this->GetStream($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']);
                if (is_array($stream)) {
                    return $stream;
                }
                $arrayDoc[0]['Stream'] = $stream;
                $arrayDoc[0]['Estensione'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Estensione'];
                $arrayDoc[0]['NomeFile'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Nome'] . "." . $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Estensione'];
                $arrayDoc[0]['IdFile'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['IdFile'];
                $arrayDoc[0]['Impronta'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Impronta'];
                $arrayDoc[0]['MimeType'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['MimeType'];
                $ext = pathinfo($risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Nome'], PATHINFO_EXTENSION);
                if ($ext == "") {
                    $arrayDoc[0]['NomeFile'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Nome'] . "." . $arrayDoc[0]['NomeFile'] = $risultato['CercaDocumentoProtocolloResult']['DocumentoPrincipale']['Estensione'];
                }
                $arrayDoc[0]['Note'] = "Documento Principale";
            }
            $i = 1;
            foreach ($Allegati as $Allegato) {
                //$arrayDoc[$i]['Stream'] = $Allegato['Documento']['Stream'];
                $stream = $this->GetStream($Allegato['Documento']);
                if (is_array($stream)) {
                    return $stream;
                }
                $arrayDoc[$i]['Stream'] = $stream;
                $arrayDoc[$i]['Estensione'] = $Allegato['Documento']['Estensione'];
                $arrayDoc[$i]['NomeFile'] = $Allegato['Documento']['Nome'] . "." . $Allegato['Documento']['Estensione'];
                $arrayDoc[$i]['Note'] = $Allegato['Descrizione'];
                $i++;
            }



            $datiSegnatura = explode("|", $Segnatura);
            $Tipo = $datiSegnatura[4];
            $Anno = substr($Data, 0, 4);
            if ($DataDoc) {
                $Anno = substr($DataDoc, 0, 4);
            }

            $mittDest = array();

            /*
             * Soggetti per protocolli in arrivo 
             */
            if (isset($risultato['CercaDocumentoProtocolloResult']['Mittente'])) {
                $soggetto_rec = array();
                $soggetto_rec['Denominazione'] = $risultato['CercaDocumentoProtocolloResult']['Mittente'];
                $mittDest[] = $soggetto_rec;
            }

            /*
             * Soggetti per protocolli in partenza
             */
            $destinatari = $risultato['CercaDocumentoProtocolloResult']['Destinatari']['string'];
            if ($destinatari) {
                if (!is_array($destinatari)) {
                    $destinatari = array($destinatari);
                }
                foreach ($destinatari as $dest) {
                    $soggetto_rec = array();
                    $soggetto_rec['Denominazione'] = $dest;
                    $mittDest[] = $soggetto_rec;
                }
            }

            $ritorno = array();
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "Ricerca avvenuta con successo!";


            $datiProtocollazione = array(
                'TipoProtocollo' => array('value' => 'Paleo41', 'status' => true, 'msg' => $msg),
                'proNum' => array('value' => $Numero, 'status' => true, 'msg' => ''),
                'Data' => array('value' => $Data, 'status' => true, 'msg' => ''),
                'DataDoc' => array('value' => $DataDoc, 'status' => true, 'msg' => ''),
                'DocNumber' => array('value' => $DocNumber, 'status' => true, 'msg' => ''),
                'Segnatura' => array('value' => $Segnatura, 'status' => true, 'msg' => ''),
                'Anno' => array('value' => $Anno, 'status' => true, 'msg' => '')
            );




            $datiProtocollo = array(
                'TipoProtocollo' => 'Paleo41',
                'NumeroProtocollo' => $Numero,
                'Data' => $Data,
                'DataDoc' => $DataDoc,
                'DocNumber' => $DocNumber,
                'Segnatura' => $Segnatura,
                'Anno' => $Anno,
                'Classifica' => $Classifica,
                'Oggetto' => $Oggetto,
                'Origine' => $origine,
                'DocumentiAllegati' => $DocumentiAllegati,
                'MittentiDestinatari' => $mittDest,
                'Allegati' => $arrayDoc,
            );

            if ($Tipo == 'P') {
                $dati['Destinatari'] = $risultato['CercaDocumentoProtocolloResult']['Destinatari'];
            } else {
                $dati['Mittente'] = $risultato['CercaDocumentoProtocolloResult']['Mittente'];
            }
            $ritorno["RetValue"] = array(
                'DatiProtocollo' => $datiProtocollo,
                'DatiProtocollazione' => $datiProtocollazione,
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

    function GetStream($allegato) {
        if (isset($allegato['Stream']) && $allegato['Stream']) {
            $stream = $allegato['Stream'];
        } else if (isset($allegato['IdFile'])) {
            $retGetFile = $this->GetFile($allegato['IdFile']);
            if ($retGetFile['Status'] == "-1") {
                return $retGetFile;
            }
            $stream = $retGetFile['Stream'];
        }
        return $stream;
    }

    function GetFile($idfile) {
        $ritorno = array();
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        //
        $OperatorePaleo = new itaOperatorePaleo4();
        $OperatorePaleo->setCodiceUO($this->WsOperatorePaleoUO['CONFIG']);
        $OperatorePaleo->setCognome($this->WsOperatorePaleoCognome['CONFIG']);
        $OperatorePaleo->setNome($this->WsOperatorePaleoNome['CONFIG']);
        $OperatorePaleo->setRuolo($this->WsOperatorePaleoRuolo['CONFIG']);
        //
        $GetFile = new itaGetFile();
        $GetFile->setIdFile($idfile);
        $ret = $paleoClient->ws_GetFile($OperatorePaleo, $GetFile);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un fault nel get file con id $idfile: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore get file con id $idfile: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $paleoClient->getResult();
        if ($risultato['GetFileResult']['MessaggioRisultato']['TipoRisultato'] != 'Info') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['GetFileResult']['MessaggioRisultato']['Descrizione'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $nome = $risultato['GetFileResult']['Oggetto']['Nome'] . "." . $risultato['GetFileResult']['Oggetto']['Estensione'];
            $attachments = $paleoClient->getAttachments();
            $binFile = $attachments[0]['data'];
            $base64File = base64_encode($binFile);
            //
            $ritorno["Status"] = "0";
            $ritorno["Message"] = "base 64 estratto con successo per il file $nome";
            $ritorno["RetValue"] = true;
            $ritorno["Stream"] = $base64File;
            //
            return $ritorno;
        }
    }

    function InvioMail($elementi) {
        $ritorno = arraY();
        $paleoClient = new itaPaleoClient();
        $this->setClientConfig($paleoClient);
        //
        $OperatorePaleo = new itaOperatorePaleo4();
        $OperatorePaleo->setCodiceUO($this->WsOperatorePaleoUO['CONFIG']);
        $OperatorePaleo->setCognome($this->WsOperatorePaleoCognome['CONFIG']);
        $OperatorePaleo->setNome($this->WsOperatorePaleoNome['CONFIG']);
        $OperatorePaleo->setRuolo($this->WsOperatorePaleoRuolo['CONFIG']);
        //
        $ret = $paleoClient->ws_SpedisciProtocollo($OperatorePaleo, $elementi["Segnatura"]);
        if (!$ret) {
            if ($paleoClient->getFault()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Fault) Rilevato un fault inviando la mail: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            } elseif ($paleoClient->getError()) {
                $msg = $paleoClient->getFault();
                $ritorno["Status"] = "-1";
                $ritorno["Message"] = "(Error) Rilevato un errore inviando la mail: <br>" . $msg['!'] . "";
                $ritorno["RetValue"] = false;
                return $ritorno;
            }
            return;
        }
        $risultato = $paleoClient->getResult();
        //file_put_contents("C:/Works/PhpDev/data/itaEngine/tmp/risultato2.log", print_r($risultato, true));
        if ($risultato['SpedisciProtocolloResult']['MessaggioRisultato']['TipoRisultato'] != 'Info') {
            $ritorno["Status"] = "-1";
            $ritorno["Message"] = $risultato['SpedisciProtocolloResult']['MessaggioRisultato']['Descrizione'];
            $ritorno["RetValue"] = false;
            return $ritorno;
        } else {
            $arrayDest = array();
            $message = "La mail è stata inviata correttamente ai seguenti destinatari:<br><br>";
            if ($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop'][0]) {
                //$idMail = $risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop'][0]['MessaggiInterop']['MessaggioInteropInfo2']['MessaggiPosta']['MessaggioPostaInfo2']['MessageId'];
                foreach ($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop'] as $key => $destinatario) {
                    if ($destinatario['Email'] == "") {
                        continue;
                    }
                    $idMail = $destinatario['MessaggiInterop']['MessaggioInteropInfo2']['MessaggiPosta']['MessaggioPostaInfo2']['MessageId'];
                    $arrayDest[$key]['Email'] = $destinatario['Email'];
                    if ($destinatario['MessaggiInterop']['MessaggioInteropInfo2'][0]) {
                        foreach ($destinatario['MessaggiInterop']['MessaggioInteropInfo2'] as $key2 => $msgInterop) {
                            $arrayDest[$key]['Stato'][$key2] = $msgInterop['StatoSpedizione'];
                        }
                    } else {
                        if (isset($destinatario['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione']) && $destinatario['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione']) {
                            $arrayDest[$key]['Stato'][0] = $destinatario['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione'];
                        }
                    }
                }
            } else {
                if ($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['Email']) {
                    $idMail = $risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2']['MessaggiPosta']['MessaggioPostaInfo2']['MessageId'];
                    $arrayDest[0]['Email'] = $risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['Email'];
                    if ($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2'][0]) {
                        foreach ($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2'] as $key2 => $msgInterop) {
                            $arrayDest[0]['Stato'][$key2] = $msgInterop['StatoSpedizione'];
                        }
                    } else {
                        if (isset($risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione']) && $risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione']) {
                            $arrayDest[0]['Stato'][0] = $risultato['SpedisciProtocolloResult']['Destinatari']['DestinatarioInfoInterop']['MessaggiInterop']['MessaggioInteropInfo2']['StatoSpedizione'];
                        }
                    }
                }
            }

            if (!$arrayDest) {
                $ritorno["Status"] = "-1";
                $ritorno["RetValue"] = false;
                $ritorno["Message"] = "Destinatari non trovati";
                return $ritorno;
            }

            foreach ($arrayDest as $dest) {
                if ($dest['Stato'] == "Spedito") {
                    $message .= "<b>" . $dest['Email'] . "</b><br>";
                }
            }


            $ritorno["Status"] = "0";
            $ritorno["RetValue"] = true;
            $ritorno["Message"] = $message;
            $ritorno["idMail"] = $idMail;
            $ritorno["Destinatari"] = $arrayDest;
            return $ritorno;
        }
    }

    public function GetHtmlVerificaInvio($valore) {
        $html = '<table id="tableVerificaInvio">';
        $html .= "<tr>";
        $html .= '<th>Destinatario</th>';
        $html .= '<th>Spedito</th>';
        $html .= '<th>Accettato</th>';
        $html .= '<th>Consegnato</th>';
        $html .= '<th>NonAccettato</th>';
        $html .= '<th>NonConsegnato</th>';
        $html .= "</tr>";
        $html .= "<tbody>";
        foreach ($valore['Destinatari'] as $destinatario) {
            $html .= "<tr>";
            $html .= "<td>" . $destinatario['Email'] . "</td>";
            $classSpedito = $classAccettato = $classConsegnato = $classNonAccettato = $classNonConsegnato = "class=\"ui-icon ui-icon-closethick\"";
            foreach ($destinatario['Stato'] as $stato) {
                switch ($stato) {
                    case "Spedito":
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "Accettato":
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "Consegnato":
                        $classConsegnato = "class=\"ui-icon ui-icon-check\"";
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classSpedito = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "NonAccettato":
                        $classNonAccettato = "class=\"ui-icon ui-icon-check\"";
                        break;
                    case "NonConsegnato":
                        $classAccettato = "class=\"ui-icon ui-icon-check\"";
                        $classNonConsegnato = "class=\"ui-icon ui-icon-check\"";
                        break;
                }
            }
            $html .= "<td><span $classSpedito></span></td>";
            $html .= "<td><span $classAccettato></span></td>";
            $html .= "<td><span $classConsegnato></span></td>";
            $html .= "<td><span $classNonAccettato></span></td>";
            $html .= "<td><span $classNonConsegnato></span></td>";
            $html .= "</tr>";
        }
        $html .= '</tbody>';
        $html .= '</table>';
        return $html;
    }

    public function VerificaInvio($params) {
        return $this->InvioMail($params);
    }

    public function getClientType() {
        return proWsClientHelper::CLIENT_PALEO41;
    }

    public function leggiProtocollazione($params) {
        return $this->LeggiProtocollo($params);
    }

    public function inserisciProtocollazionePartenza($elementi) {
        return $this->protocollazionePartenza($elementi, "P");
    }

    public function inserisciProtocollazioneArrivo($elementi) {
        return $this->protocollazioneEntrata($elementi, "A");
    }

    public function inserisciDocumentoInterno($elementi) {
        return $this->ArchiviaDocumentoInterno($elementi, "A");
    }

    public function leggiDocumentoInterno($params) {
        return $this->LeggiProtocollo($params);
    }

}

?>