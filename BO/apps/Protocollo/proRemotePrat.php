<?php

/**
 *
 * PROTOCOLLA PRATICHE DA REMOTO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    23.01.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once(ITA_LIB_PATH . '/nusoap/nusoap.php');

function proRemotePrat() {
    $proRemotePrat = new proRemotePrat();
    $proRemotePrat->parseEvent();
    return;
}

class proRemotePrat extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proRemotePrat";
    public $divMessaggio = "proRemotePrat_divMessaggio";
    private $message;
    private $title;
    private $timeout = 2400;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = ItaDB::DBOpen('PROT');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    function getmessage() {
        return $this->message;
    }

    function getTitle() {
        return $this->title;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($_POST['event']) {
            case 'openform':
                switch ($_POST['azione']) {
                    case "PA":
                        $numeroPratica = $_POST['pratica'];
                        $rowidSoggetto = $_POST['rowidSogg'];
                        $elementi = $this->getWSElementiPratica($numeroPratica, $rowidSoggetto);
                        if (!$elementi) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
//
// Imposta dati da protocollare da elementi remoti
//
                        $model = 'proDatiProtocollo.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proDatiProtocollo = new proDatiProtocollo();
                        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);



                        if ($ret_id === false) {
                            Out::msgStop($proDatiProtocollo->getTitle(), $proDatiProtocollo->getmessage());
                            break;
                        }
                        if ($_POST['utenteWs']) {
                            $accLib = new accLib();
                            $utente_rec = $accLib->GetUtenti($_POST['utenteWs'], 'utelog');
                            if (!$utente_rec) {
                                Out::msgStop("Protocollazione da remoto", "Utente non valido!");
                                break;
                            } else {
                                $proDatiProtocollo->setUtenteWs($_POST['utenteWs']);
                            }
                        }
//
// Lancia il protocollatore con i dati impostati
//
                        $model = 'proProtocolla.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proProtocolla = new proProtocolla();
                        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
                        if ($ret_id === false) {
                            Out::msgStop($proProtocolla->getTitle(), $proProtocolla->getmessage());
                            break;
                        }
                        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
                        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//
// Blocca pratica come protocollata
//               
                        $ret_abbina = $this->setWSProtolloPratica($numeroPratica, $Anapro_rec, $Anaogg_rec);
                        if ($ret_abbina === false) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
                        Out::html($this->divMessaggio, "Protocolla pratica da remoto:<br>Protocollo N.: " . substr($Anapro_rec["PRONUM"], 4) . "/" . substr($Anapro_rec["PRONUM"], 0, 4));
                        break;
                    case "CPALL":
                    case "CP":
                        $idComunicazione = $_POST['passo'];
                        if ($_POST['azione'] == "CPALL") {
                            $arrAllegati = array();
                            if ($_POST['idall']) {
                                $arrAllegati = explode("|", $_POST['idall']);
                            }
                        } else {
                            $arrAllegati = false;
                        }
                        $elementi = $this->getWSElementiComunicazione($idComunicazione, "P", $arrAllegati);
                        if (!$elementi) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }

//
// Imposta dati da protocollare da elementi remoti
//
                        $model = 'proDatiProtocollo.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proDatiProtocollo = new proDatiProtocollo();
                        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
                        if ($ret_id === false) {
                            Out::msgStop($proDatiProtocollo->getTitle(), $proDatiProtocollo->getmessage());
                        }
                        if ($_POST['utenteWs']) {
                            $accLib = new accLib();
                            $utente_rec = $accLib->GetUtenti($_POST['utenteWs'], 'utelog');
                            if (!$utente_rec) {
                                Out::msgStop("Protocollazione da remoto", "Utente non valido!");
                                break;
                            } else {
                                $proDatiProtocollo->setUtenteWs($_POST['utenteWs']);
                            }
                        }

//
// Lancia il protocollatore con i dati impostati
//
                        $model = 'proProtocolla.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proProtocolla = new proProtocolla();

                        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
                        if ($ret_id === false) {
                            Out::msgStop($proProtocolla->getTitle(), $proProtocolla->getmessage());
                            break;
                        }
                        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
                        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//
// Blocca pratica come protocollata
//               
                        $ret_abbina = $this->setWSProtolloComunicazione($idComunicazione, $Anapro_rec, $Anaogg_rec);
                        if ($ret_abbina === false) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
                        Out::html($this->divMessaggio, "Protocolla comunicazione in partenza da remoto:<br>Protocollo N.: " . substr($Anapro_rec["PRONUM"], 4) . "/" . substr($Anapro_rec["PRONUM"], 0, 4));
                        break;
                    case "CA":
                        $idComunicazione = $_POST['passo'];
                        $elementi = $this->getWSElementiComunicazione($idComunicazione, "A");
                        if (!$elementi) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
//
// Imposta dati da protocollare da elementi remoti
//
                        $model = 'proDatiProtocollo.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proDatiProtocollo = new proDatiProtocollo();
                        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
                        if ($ret_id === false) {
                            Out::msgStop($proDatiProtocollo->getTitle(), $proDatiProtocollo->getmessage());
                        }
                        if ($_POST['utenteWs']) {
                            $accLib = new accLib();
                            $utente_rec = $accLib->GetUtenti($_POST['utenteWs'], 'utelog');
                            if (!$utente_rec) {
                                Out::msgStop("Protocollazione da remoto", "Utente non valido!");
                                break;
                            } else {
                                $proDatiProtocollo->setUtenteWs($_POST['utenteWs']);
                            }
                        }

//
// Lancia il protocollatore con i dati impostati
//
                        $model = 'proProtocolla.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proProtocolla = new proProtocolla();
                        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
                        if ($ret_id === false) {
                            Out::msgStop($proProtocolla->getTitle(), $proProtocolla->getmessage());
                            break;
                        }
                        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
                        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//
// Blocca pratica come protocollata
//               
                        $ret_abbina = $this->setWSProtolloComunicazione($idComunicazione, $Anapro_rec, $Anaogg_rec);
                        if ($ret_abbina === false) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
                        Out::html($this->divMessaggio, "Protocolla comunicazione in arrivo da remoto:<br>Protocollo N.: " . substr($Anapro_rec["PRONUM"], 4) . "/" . substr($Anapro_rec["PRONUM"], 0, 4));
                        break;
                    CASE "ADDALLP":
                        $idComunicazione = $_POST['passo'];
                        $idPronum = $_POST['numPro'];
                        $arrAllegati = array();
                        if ($_POST['idall']) {
                            $arrAllegati = explode("|", $_POST['idall']);
                        }
                        //file_put_contents("/users/pc/dos2ux/miki.log", print_r($arrAllegati,true));
                        $elementi = $this->getWSElementiComunicazione($idComunicazione, "P", $arrAllegati);
                        if (!$elementi) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
                        //file_put_contents("/users/pc/dos2ux/mikiXXX.log", print_r($elementi,true));
//
// Imposta dati da protocollare da elementi remoti
//
                        $model = 'proDatiProtocollo.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proDatiProtocollo = new proDatiProtocollo();
                        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
                        if ($ret_id === false) {
                            Out::msgStop($proDatiProtocollo->getTitle(), $proDatiProtocollo->getmessage());
                        }
                        if ($_POST['utenteWs']) {
                            $accLib = new accLib();
                            $utente_rec = $accLib->GetUtenti($_POST['utenteWs'], 'utelog');
                            if (!$utente_rec) {
                                Out::msgStop("Protocollazione da remoto", "Utente non valido!");
                                break;
                            } else {
                                $proDatiProtocollo->setUtenteWs($_POST['utenteWs']);
                            }
                        }

//
// Lancia il protocollatore con i dati impostati
//
                        $model = 'proProtocolla.class';
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $proProtocolla = new proProtocolla();

                        $ret_id = $proProtocolla->aggiungiAllegati('Aggiungi', '', $proDatiProtocollo, $idPronum);
                        if ($ret_id === false) {
                            Out::msgStop($proProtocolla->getTitle(), $proProtocolla->getmessage());
                            break;
                        }

                        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');
                        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//
// Blocca pratica come protocollata
//
                        $ret_abbina = $this->setWSProtolloComunicazione($idComunicazione, $Anapro_rec, $Anaogg_rec);
                        if ($ret_abbina === false) {
                            Out::msgStop($this->getTitle(), $this->getmessage());
                            break;
                        }
                        Out::html($this->divMessaggio, "Aggiunti Allegati comunicazione in partenza da remoto:<br>Protocollo N.: " . substr($Anapro_rec["PRONUM"], 4) . "/" . substr($Anapro_rec["PRONUM"], 0, 4));



                        break;
                }
                break;
        }
    }

    public function close() {
        parent::close();
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    /**
     * Chiede l'elenco dei dati Pratica in arrivo da passare al protocollatore
     * @param string $numeroPratica numero pratica AAAANNNNNN
     * @return array
     */
    private function getWSElementiPratica($numeroPratica, $rowidSoggetto = 0) {
        $anaent_31 = $this->proLib->GetAnaent('31');
        $userName = $anaent_31['ENTDE1'];
        $userPassword = $anaent_31['ENTDE2'];
        $domainCode = $anaent_31['ENTDE3'];
        $urlSuap = $anaent_31['ENTVAL'];

        $client = new nusoap_client($urlSuap, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $client->debugLevel = 0;
        $client->timeout = $this->timeout;
        $client->response_timeout = $this->timeout;
        $params = array(
            "userName" => $userName,
            "userPassword" => $userPassword,
            "domainCode" => $domainCode
        );
        $token = $client->call('GetItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "getWSElementiPratica: Fault Autenticazione";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetItaEngineContextToken";
            $this->message = $err;
            return false;
        }

        $params = array(
            "itaEngineContextToken" => $token,
            "domainCode" => $domainCode,
            "numeroPratica" => substr($numeroPratica, 4),
            "annoPratica" => substr($numeroPratica, 0, 4),
            "rowidSoggetto" => $rowidSoggetto
        );
        $response = $client->call('GetElementiProtocollaPratica', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Pratica: fault lettura dati pratica.";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetElementiProtocollaPratica";
            $this->message = $err;
            return false;
        }


        $elementi = unserialize(base64_decode($response));
        // fix pasdoc_rec ****
        foreach ($elementi['allegati']['pasdoc_rec_allegati'] as $key => $allegato) {
            $rowid = $allegato['ROWID'];
            if ($rowid) {
                $b64Response = '';
                $partIdx = 1;
                while (true) {
                    $params = array(
                        "itaEngineContextToken" => $token,
                        "domainCode" => $domainCode,
                        "rowid" => $rowid,
                        "part" => $partIdx);
                    $response = $client->call('GetPraticaAllegatoForRowidSplit', $params, '');
                    if ($client->fault) {
                        $this->title = "Protocollazione Pratica: fault download allegato pratica part=$partIdx.";
                        $this->message = "id:$rowid  " . $client->faultstring;
                        return false;
                    }
                    $err = $client->getError();
                    if ($err) {
                        $this->title = "Prot. Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowidSplit part=$partIdx";
                        $this->message = $err;
                        return false;
                    }
                    if ($response == '') {
                        break;
                    }
                    if ($partIdx > 30) {
                        break;
                    }

                    $b64Response .= $response;
                    $partIdx +=1;
                    usleep(300000);
                }
//                $params = array(
//                    "itaEngineContextToken" => $token,
//                    "domainCode" => $domainCode,
//                    "rowid" => $rowid);
//
//                $response = $client->call('GetPraticaAllegatoForRowid', $params, '');
//                if ($client->fault) {
//                    $this->title = "Protocollazione Pratica: fault download allegato pratica.";
//                    $this->message = "id:$rowid  " . $client->faultstring;
//                    return false;
//                }
//                $err = $client->getError();
//                if ($err) {
//                    $this->title = "Prot. Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid1";
//                    $this->message = $err;
//                    return false;
//                }

                $elementi['allegati']['Allegati'][$key]['Documento']['Stream'] = $b64Response;
            }
        }


        //
        // IF per controllo errore assenza allegato principale
        //
        $rowid = $elementi['allegati']['Principale']['ROWID'];
        if ($rowid) {
            $b64Response = '';
            $partIdx = 1;
            while (true) {
                $params = array(
                    "itaEngineContextToken" => $token,
                    "domainCode" => $domainCode,
                    "rowid" => $rowid,
                    "part" => $partIdx);
                $response = $client->call('GetPraticaAllegatoForRowidSplit', $params, '');
                if ($client->fault) {
                    $this->title = "Protocollazione Pratica: fault download allegato principale pratica part=$partIdx.";
                    $this->message = "id:$rowid  " . $client->faultstring;
                    return false;
                }
                $err = $client->getError();
                if ($err) {
                    $this->title = "Prot. Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid part=$partIdx";
                    $this->message = $err;
                    return false;
                }
                if ($response == '') {
                    break;
                }
                if ($partIdx > 30) {
                    break;
                }

                $b64Response .= $response;
                $partIdx +=1;
            }


//            $params = array(
//                "itaEngineContextToken" => $token,
//                "domainCode" => $domainCode,
//                "rowid" => $rowid,);
//            $response = $client->call('GetPraticaAllegatoForRowid', $params, '');
//            if ($client->fault) {
//                $this->title = "Protocollazione Pratica: fault download allegato principale pratica.";
//                $this->message = "id:$rowid  " . $client->faultstring;
//                return false;
//            }
//            $err = $client->getError();
//            if ($err) {
//                $this->title = "Prot. Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid2";
//                $this->message = $err;
//                return false;
//            }
        }

        $elementi['allegati']['Principale']['Stream'] = $b64Response;
        $params = array(
            "token" => $token,
            "domainCode" => $domainCode);
        $response_destroy = $client->call('DestroyItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Pratica: Fault chiusura collegamento remoto1";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo DestroyItaEngineContextToken";
            $this->message = $err;
            return false;
        }
        //file_put_contents("/users/pc/dos2ux/miki.log", time() . "\n\n", FILE_APPEND);
        return $elementi;
    }

    private function setWSProtolloPratica($numeroPratica, $Anapro_rec, $Anaogg_rec) {
        $anaent_31 = $this->proLib->GetAnaent('31');
        $userName = $anaent_31['ENTDE1'];
        $userPassword = $anaent_31['ENTDE2'];
        $domainCode = $anaent_31['ENTDE3'];
        $urlSuap = $anaent_31['ENTVAL'];

        $datiProtocollo = array(
            "tipoProtocollo" => $Anapro_rec['PROPAR'],
            "numeroProtocollo" => substr($Anapro_rec['PRONUM'], 4),
            "annoProtocollo" => substr($Anapro_rec['PRONUM'], 0, 4),
            "dataProtocollo" => $Anapro_rec['PRODAR'],
            "Oggetto" => $Anaogg_rec['OGGOGG'],
            "Segnatura" => $Anapro_rec['PROSEG']
        );


        $client = new nusoap_client($urlSuap, true);

        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $client->debugLevel = 0;

        $params = array(
            "userName" => $userName,
            "userPassword" => $userPassword,
            "domainCode" => $domainCode
        );

        $token = $client->call('GetItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "setWSProtolloPratica: Fault Autenticazione";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "setWSProtolloPratica: Errore Autenticazione";
            $this->message = $err;
            return false;
        }

        $params = array(
            "itaEngineContextToken" => $token,
            "domainCode" => $domainCode,
            "numeroPratica" => substr($numeroPratica, 4),
            "annoPratica" => substr($numeroPratica, 0, 4),
            "numeroProtocollo" => substr($Anapro_rec['PRONUM'], 4),
            "annoProtocollo" => substr($Anapro_rec['PRONUM'], 0, 4),
            "datiProtocollazione" => base64_encode(serialize($datiProtocollo))
        );
        $response = $client->call('SetProtocolloPratica', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Pratica: Fault Aggiornamento Pratica con N.Protocollo";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo";
            $this->message = $err;
            return false;
        }

        $params = array(
            "token" => $token,
            "domainCode" => $domainCode);
        $response_destroy = $client->call('DestroyItaEngineContextToken', $params, '');
        if (!$response_destroy) {
            $this->title = "Protocollazione Pratica: Fault chiusura collegamento remoto2";
            $this->message = $client->faultstring;
            ;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo";
            $this->message = $err;
            return false;
        }

        return true;
    }

    private function getWSElementiComunicazione($idComunicazione, $tipo, $arrAllegati = false) {
        $anaent_31 = $this->proLib->GetAnaent('31');
        $userName = $anaent_31['ENTDE1'];
        $userPassword = $anaent_31['ENTDE2'];
        $domainCode = $anaent_31['ENTDE3'];
        $urlSuap = $anaent_31['ENTVAL'];
        $client = new nusoap_client($urlSuap, true);

        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $client->debugLevel = 0;

        $params = array(
            "userName" => $userName,
            "userPassword" => $userPassword,
            "domainCode" => $domainCode
        );
        $token = $client->call('GetItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Comunicazione: Fault Autenticazione";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetItaEngineContextToken";
            $this->message = $err;
            return false;
        }

        $params = array(
            "itaEngineContextToken" => $token,
            "domainCode" => $domainCode,
            "idComunicazione" => $idComunicazione
        );
        $response = $client->call('GetElementiProtocollaComunicazione', $params, '');
        if ($client->fault) {
            $this->title = ($tipo == "P" ) ? "Protocollazione Comunicazione Partenza: Fault lettura dati comunicazione" : "Protocollazione Comunicazione Arrivo: Fault lettura dati comunicazione";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetElementiProtocollaComunicazione";
            $this->message = $err;
            return false;
        }

        $elementi = unserialize(base64_decode($response));
        if ($arrAllegati === false) {
            foreach ($elementi['allegati']['pasdoc_rec'] as $key => $allegato) {
// -- NUOVO SCARICO SPLIT
                $rowid = $allegato['ROWID'];
                $b64Response = '';
                $partIdx = 1;
                while (true) {
                    $params = array(
                        "itaEngineContextToken" => $token,
                        "domainCode" => $domainCode,
                        "rowid" => $rowid,
                        "part" => $partIdx);
                    $response = $client->call('GetPraticaAllegatoForRowidSplit', $params, '');
                    if ($client->fault) {
                        $this->title = "Protocollazione Comunicazione: fault download allegato comunicazione split part=$partIdx.";
                        $this->message = "id:$rowid  " . $client->faultstring;
                        return false;
                    }
                    $err = $client->getError();
                    if ($err) {
                        $this->title = "Protocollazione Comunicazione: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowidSplit part=$partIdx";
                        $this->message = $err;
                        return false;
                    }
                    if ($response == '') {
                        break;
                    }
                    if ($partIdx > 30) {
                        break;
                    }

                    $b64Response .= $response;
                    $partIdx +=1;
                    usleep(300000);
                }
                $elementi['allegati']['Allegati'][$key]['Documento']['Stream'] = $b64Response;
// --
// -- SCARICO IN UNICA SOLUZIONE                
//                $rowid = $allegato['ROWID'];
//                $params = array(
//                        "itaEngineContextToken" => $token,
//                        "domainCode" => $domainCode,
//                        "rowid" => $rowid);
//                $response = $client->call('GetPraticaAllegatoForRowid', $params, '');
//                if ($client->fault) {
//                    $this->title = "Protocollazione Comunicazione: Fault download allegati alla comunicazione";
//                    $this->message = $client->faultstring;
//                    return false;
//                }
//                $err = $client->getError();
//                if ($err) {
//                    $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid3";
//                    $this->message = $err;
//                    return false;
//                }
//
//                $elementi['allegati']['Allegati'][$key]['Documento']['Stream'] = $response;
//--
            }




            //
            // IF per controllo errore assenza allegato principale
            //
            $rowid = $elementi['allegati']['Principale']['ROWID'];
            if ($rowid != 0) {
// -- NUOVO SCARICO SPLIT
                $b64Response = '';
                $partIdx = 1;
                while (true) {
                    $params = array(
                        "itaEngineContextToken" => $token,
                        "domainCode" => $domainCode,
                        "rowid" => $rowid,
                        "part" => $partIdx);
                    $response = $client->call('GetPraticaAllegatoForRowidSplit', $params, '');
                    if ($client->fault) {
                        $this->title = "Protocollazione Comunicazione: fault download allegato comunicazione split part=$partIdx.";
                        $this->message = "id:$rowid  " . $client->faultstring;
                        return false;
                    }
                    $err = $client->getError();
                    if ($err) {
                        $this->title = "Protocollazione Comunicazione: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowidSplit part=$partIdx";
                        $this->message = $err;
                        return false;
                    }
                    if ($response == '') {
                        break;
                    }
                    if ($partIdx > 30) {
                        break;
                    }

                    $b64Response .= $response;
                    $partIdx +=1;
                    usleep(300000);
                }
                $elementi['allegati']['Principale']['Stream'] = $b64Response;
// --

                
// -- SCARICO IN UNICA SOLUZIONE                  
//                $params = array(
//                    "itaEngineContextToken" => $token,
//                    "domainCode" => $domainCode,
//                    "rowid" => $rowid);
//
//                $response = $client->call('GetPraticaAllegatoForRowid', $params, '');
//                if ($client->fault) {
//                    $this->title = "Protocollazione Comunicazione: Fault download allegato Principale alla comunicazione";
//                    $this->message = $client->faultstring;
//                    return false;
//                }
//                $err = $client->getError();
//                if ($err) {
//                    $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid4";
//                    $this->message = $err;
//                    return false;
//                }
//                $elementi['allegati']['Principale']['Stream'] = $response;
// --                
            }
        } else {
            if (count($elementi['allegati']['pasdoc_rec']) != count($arrAllegati)) {
                $arrUnset = array();
                foreach ($elementi['allegati']['pasdoc_rec'] as $key1 => $allegato) {
                    if ($arrAllegati) {
                        $trovato = false;
                        foreach ($arrAllegati as $rowid) {
                            if ($rowid == $allegato['ROWID']) {
                                $trovato = true;
                                break;
                            }
                        }
                    } else {
                        $trovato = false;
                    }
                    if (!$trovato) {
                        $arrUnset[] = $key1;
                    }
                }
                foreach ($arrUnset as $key1) {
                    unset($elementi['allegati']['pasdoc_rec'][$key1]);
                    unset($elementi['allegati']['Allegati'][$key1]);
                }


//                $arrUnset=array();
//                if($arrAllegati) {
//                    foreach ($arrAllegati as $rowid) {
//                        foreach ($elementi['allegati']['pasdoc_rec'] as $key1 => $allegato) {
//                            if (!in_array($rowid, $allegato)) {
//                                $arrUnset[] = $key1;
//                            }
//                        }
//                    }
//                    file_put_contents("/users/pc/dos2ux/mikiset.log", print_r($arrUnset,true));
//                }else {
//                    foreach ($elementi['allegati']['pasdoc_rec'] as $key1 => $allegato) {
//                        $arrUnset[] = $key1;
//                    }
//                }
//
//                foreach ($arrUnset as $key1 ) {
//                    unset($elementi['allegati']['pasdoc_rec'][$key1]);
//                    unset($elementi['allegati']['Allegati'][$key1]);
//                }
//                foreach ($elementi['allegati']['pasdoc_rec'] as $key1 => $allegato) {
//                    if (!$arrAllegati) {
//                        unset($elementi['allegati']['pasdoc_rec'][$key1]);
//                        unset($elementi['allegati']['Allegati'][$key1]);
//                    }else {
//                        foreach ($arrAllegati as $rowid) {
//                            if (!in_array($rowid, $allegato)) {
//                                unset($elementi['allegati']['pasdoc_rec'][$key1]);
//                                unset($elementi['allegati']['Allegati'][$key1]);
//                            }
//                        }
//                    }
//                }
            }

            foreach ($elementi['allegati']['pasdoc_rec'] as $key => $allegato) {
                $rowid = $allegato['ROWID'];
                $params = array(
                    "itaEngineContextToken" => $token,
                    "domainCode" => $domainCode,
                    "rowid" => $rowid);
                $response = $client->call('GetPraticaAllegatoForRowid', $params, '');
                if ($client->fault) {
                    $this->title = "Protocollazione Comunicazione: Fault download allegati alla comunicazione";
                    $this->message = $client->faultstring;
                    return false;
                }
                $err = $client->getError();
                if ($err) {
                    $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo GetPraticaAllegatoForRowid3";
                    $this->message = $err;
                    return false;
                }
                $elementi['allegati']['Allegati'][$key]['Documento']['Stream'] = $response;
            }
        }

        $params = array(
            "token" => $token,
            "domainCode" => $domainCode);
        $response_destroy = $client->call('DestroyItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Comunicazione: Fault chiusura collegamento remoto3";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "Protocollazione Pratica: Errore Chiamata ws fascicolo DestroyItaEngineContextToken";
            $this->message = $err;
            return false;
        }

        return $elementi;
    }

    private function setWSProtolloComunicazione($idComunicazione, $Anapro_rec, $Anaogg_rec) {
        $anaent_31 = $this->proLib->GetAnaent('31');
        $userName = $anaent_31['ENTDE1'];
        $userPassword = $anaent_31['ENTDE2'];
        $domainCode = $anaent_31['ENTDE3'];
        $urlSuap = $anaent_31['ENTVAL'];
        $datiProtocollo = array(
            "tipoProtocollo" => $Anapro_rec['PROPAR'],
            "numeroProtocollo" => substr($Anapro_rec['PRONUM'], 4),
            "annoProtocollo" => substr($Anapro_rec['PRONUM'], 0, 4),
            "dataProtocollo" => $Anapro_rec['PRODAR'],
            "Oggetto" => $Anaogg_rec['OGGOGG'],
            "Segnatura" => $Anapro_rec['PROSEG']
        );


        $client = new nusoap_client($urlSuap, true);

        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = true;
        $client->debugLevel = 0;

        $params = array(
            "userName" => $userName,
            "userPassword" => $userPassword,
            "domainCode" => $domainCode
        );

        $token = $client->call('GetItaEngineContextToken', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Comunicazione: Fault Autenticazione";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "1 - Protocollazione Pratica: Errore Chiamata ws fascicolo";
            $this->message = $err;
            return false;
        }

        $params = array(
            "itaEngineContextToken" => $token,
            "domainCode" => $domainCode,
            "idComunicazione" => $idComunicazione,
            "datiProtocollazione" => base64_encode(serialize($datiProtocollo))
        );
        $response = $client->call('SetProtocolloComunicazione', $params, '');
        if ($client->fault) {
            $this->title = "Protocollazione Comunicazione: Fault Aggiornamento Comunicazione con N.Protocollo";
            $this->message = $client->faultstring;
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "2 - Protocollazione Pratica: Errore Chiamata ws fascicolo";
            $this->message = $err;
            return false;
        }

        $params = array(
            "token" => $token,
            "domainCode" => $domainCode);
        $response_destroy = $client->call('DestroyItaEngineContextToken', $params, '');
        if (!$response_destroy) {
            $this->title = "Protocollazione Comunicazione: Fault chiusura collegamento remoto4";
            $this->message = "Errore chiamata DestroyItaEngineContextToken in faul.";
            return false;
        }
        $err = $client->getError();
        if ($err) {
            $this->title = "3 - Protocollazione Pratica: Errore Chiamata ws fascicolo";
            $this->message = $err;
            return false;
        }
        return true;
    }

}

?>
