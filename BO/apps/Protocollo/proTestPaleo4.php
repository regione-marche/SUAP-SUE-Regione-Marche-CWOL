<?php

/**
 *
 * TEST PALESO WS-CLIENT
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    21.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaPaleoClient.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaUtentePaleo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaOperatorePaleo4.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqFindRubrica.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaRubrica.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaGetFile.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloArrivo.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itareqProtocolloPartenza.class.php');
include_once(ITA_LIB_PATH . '/itaPHPPaleo4/itaCercaDocumentoProtocollo.class.php');
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

function proTestPaleo4() {
    $proTestPaleo4 = new proTestPaleo4();
    $proTestPaleo4->parseEvent();
    return;
}

class proTestPaleo4 extends itaModel {

    public $name_form = "proTestPaleo4";
    public $StreamDocumentoPrincipale;
    public $StreamDocumentoAllegato;

    function __construct() {
        app::log('construct');
        parent::__construct();
        $this->StreamDocumentoPrincipale = App::$utente->getKey($this->nameForm . '_StreamDocumentoPrincipale');
        $this->StreamDocumentoAllegato = App::$utente->getKey($this->nameForm . '_StreamDocumentoAllegato');
        app::log('fine construct');
    }

    function __destruct() {
        app::log('destruct');
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_StreamDocumentoPrincipale', $this->StreamDocumentoPrincipale);
            App::$utente->setKey($this->nameForm . '_StreamDocumentoAllegato', $this->StreamDocumentoAllegato);
        }
        app::log('fine destruct');
    }

    private function setClientConfig($paleoClient) {
        $config = $_POST[$this->name_form . '_CONFIG'];
        $paleoClient->setWebservices_uri($config['wsEndpoint']);
        $paleoClient->setWebservices_wsdl($config['wsWsdl']);
        $paleoClient->setUsername($config['wsCodamm'] . "\\" . $config['wsUser']);
        $paleoClient->setpassword($config['wsPassword']);
        app::log('configurazioni chiamata');
        app::log($config);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->name_form, "", true, "desktopBody");
                Out::show($this->name_form);
                //inizializzo i valori di configurazione della chiamata

                $devLib = new devLib();
                //
                $uri = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPALEO4ENDPOINT', false);
                $uri2 = $uri['CONFIG'];
                $wsdl = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPALEO4WSDL', false);
                $wsdl2 = $wsdl['CONFIG'];
                $CodAmm = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'CODAMMINISTRAZIONEPALEO4', false);
                $CodAmm2 = $CodAmm['CONFIG'];
                $username = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSUTENTEPALEO4', false);
                $username2 = $username['CONFIG'];
                $password = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSPASSWORDPALEO4', false);
                $password2 = $password['CONFIG'];

                $WsOperatorePaleoUO = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4UO', false);
                $WsOperatorePaleoUO2 = $WsOperatorePaleoUO['CONFIG'];
                $WsOperatorePaleoCognome = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4COGNOME', false);
                $WsOperatorePaleoCognome2 = $WsOperatorePaleoCognome['CONFIG'];
                $WsOperatorePaleoNome = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4NOME', false);
                $WsOperatorePaleoNome2 = $WsOperatorePaleoNome['CONFIG'];
                $WsOperatorePaleoRuolo = $devLib->getEnv_config('PALEO4WSCONNECTION', 'codice', 'WSOPERATOREPALEO4RUOLO', false);
                $WsOperatorePaleoRuolo2 = $WsOperatorePaleoRuolo['CONFIG'];

                Out::valore($this->name_form . "_CONFIG[wsEndpoint]", $uri2);
                Out::valore($this->name_form . "_CONFIG[wsWsdl]", $wsdl2);
                Out::valore($this->name_form . "_CONFIG[wsCodamm]", $CodAmm2);
                Out::valore($this->name_form . "_CONFIG[wsUser]", $username2);
                Out::valore($this->name_form . "_CONFIG[wsPassword]", $password2);
                Out::setFocus('', $this->name_form . "_CONFIG[wsEndpoint]");

                //inizializzo i valori dell'OperatorePaleo
                Out::valore($this->name_form . "_OperatorePaleo_CodiceUO", $WsOperatorePaleoUO2);
                Out::valore($this->name_form . "_OperatorePaleo_Cognome", $WsOperatorePaleoCognome2);
                Out::valore($this->name_form . "_OperatorePaleo_Nome", $WsOperatorePaleoNome2);
                Out::valore($this->name_form . "_OperatorePaleo_Ruolo", $WsOperatorePaleoRuolo2);

                //inizializzo i valori del Corrispondente
                Out::valore($this->name_form . "_DatiCorrispondente_Cognome", "Rossi");
                Out::valore($this->name_form . "_DatiCorrispondente_Nome", "Mario");
                Out::valore($this->name_form . "_DatiCorrispondente_Email", "email@em.it");
                Out::valore($this->name_form . "_DatiCorrispondente_IdFiscale", "idFiscale0123");
                Out::valore($this->name_form . "_DatiCorrispondente_IstatComune", "123456");

                //inizializzo alcuni valori per la ProtocollazioneEntrata
                Out::valore($this->name_form . "_reqProtocolloArrivo_CodiceRegistro", "RC_C524");
                Out::valore($this->name_form . "_reqProtocolloArrivo_Oggetto", "Oggetto Prova Protocollo tramite WS");
                Out::valore($this->name_form . "_Mittente_CodiceRubrica", "ZT1");

                //opzioni della select Tipo nel box Dati corrispondente
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'Indefinito', 0, 'Indefinito');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'Amministrazione', 0, 'Amministrazione');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'AOO', 0, 'AOO');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'UO', 0, 'Unità Organizzativa');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'Persona', 1, 'Persona');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'Altro', 0, 'Altro');
                Out::select($this->name_form . '_DatiCorrispondente_Tipo', '1', 'Impresa', 0, 'Impresa');
                //opzioni della select TipoRisultato nel box Dati corrispondente->Messaggio Risultato
                Out::select($this->name_form . '_BEBase_TipoRisultato', '1', 'Info', 1, 'Info');
                Out::select($this->name_form . '_BEBase_TipoRisultato', '1', 'Warning', 0, 'Warning');
                Out::select($this->name_form . '_BEBase_TipoRisultato', '1', 'Error', 0, 'Error');
                //opzioni della select DocumentoPrincipaleOriginale nel box DatiProtocollazione
                Out::select($this->name_form . '_reqProtocolloArrivo_DPO', '1', 'NonDefinito', 1, 'Non Definito');
                Out::select($this->name_form . '_reqProtocolloArrivo_DPO', '1', 'Digitale', 0, 'Digitale');
                Out::select($this->name_form . '_reqProtocolloArrivo_DPO', '1', 'Cartaceo', 0, 'Cartaceo');
                //opzioni della select nella tabpane reqFindRubrica
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'Indefinito', 1, 'Indefinito');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'Amministrazione', 0, 'Amministrazione');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'AOO', 0, 'AOO');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'UO', 0, 'Unità Organizzativa');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'Persona', 0, 'Persona');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'Altro', 0, 'Altro');
                Out::select($this->name_form . '_reqFindRubrica_Tipo', '1', 'Impresa', 0, 'Impresa');

                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->name_form . '_callGetScadenzaPassword':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);
                        $utentePaleo = new itaUtentePaleo();
                        $utentePaleo->setUserId($_POST[$this->name_form . '_utentePaleo_userid']);
                        $utentePaleo->setCodAmm($_POST[$this->name_form . '_utentePaleo_CodAmm']);

                        $ret = $paleoClient->ws_GetScadenzaPassword($utentePaleo);

                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetScadenzaPassword Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;

                    case $this->name_form . '_callGetOperatori':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);
                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);

                        $ret = $paleoClient->ws_GetOperatori($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetOperatori Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetRagioniTrasmissione':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);
                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);

                        $ret = $paleoClient->ws_GetRagioniTrasmissione($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetRagioniTrasmissione Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetRegistri':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);
                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);

                        $ret = $paleoClient->ws_GetRegistri($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetRegistri Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetTitolarioClassificazione':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);
                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);

                        $ret = $paleoClient->ws_GetTitolarioClassificazione($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetTitolarioClassificazione Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetTipiDatiFascicoli':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);
                        $ret = $paleoClient->ws_GetTipiDatiFascicoli($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetTipiDatiFascicoli Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callFindRubricaExt':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $reqFindRubrica = new itareqFindRubrica();
                        $reqFindRubrica->setCodice($_POST[$this->name_form . '_reqFindRubrica_Codice']);
                        $reqFindRubrica->setDescrizione($_POST[$this->name_form . '_reqFindRubrica_Descrizione']);
                        $reqFindRubrica->setIdFiscale($_POST[$this->name_form . '_reqFindRubrica_IdFiscale']);
                        $reqFindRubrica->setIstatComune($_POST[$this->name_form . '_reqFindRubrica_IstatComune']);
                        $reqFindRubrica->setTipo($_POST[$this->name_form . '_reqFindRubrica_Tipo']);
                        //App::log($reqFindRubrica);

                        $ret = $paleoClient->ws_FindRubricaExt($OperatorePaleo, $reqFindRubrica);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);

//                        //estrazione della rubrica in file csv
//                        $ris=$paleoClient->getResult();
//                        $ris2=$ris['FindRubricaExtResult']['Lista']['Rubrica'];
//                        app::log('ris2');
//                        app::log($ris2);
//                        $fp = fopen(ITA_LIB_PATH . '/itaPHPPaleo/rubrica2.csv', 'w');
////                        foreach ($ris2 as $key => $value) {
////                            $ret2[$key]['Cognome'] = $value['Cognome'];
////                            $ret2[$key]['Email'] = $ris2['Email'];
////                            $ret2[$key]['IdFiscale'] = $ris2['IdFiscale'];
////                        }
//
//                        app::log('ris2');
//                        app::log($ret2);
//                        foreach ($ris2 as $fields) {
//                            fputcsv($fp, $fields);
//                        }
//                        fclose($fp);

                        Out::msgInfo("FindRubricaExt Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callSaveVoceRubrica':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $Rubrica = new itaRubrica();
                        $paramMessaggio = array(
                            'Descrizione' => $_POST[$this->name_form . '_BEBase_Descrizione'],
                            'TipoRisultato' => $_POST[$this->name_form . '_BEBase_TipoRisultato']
                        );
//                        App::log($paramMessaggio);
                        $Rubrica->setMessaggioRisultato($paramMessaggio);
                        //parametri DatiCorrispondente
                        $Rubrica->setCognome($_POST[$this->name_form . '_DatiCorrispondente_Cognome']);
                        $Rubrica->setEmail($_POST[$this->name_form . '_DatiCorrispondente_Email']);
                        $Rubrica->setIdFiscale($_POST[$this->name_form . '_DatiCorrispondente_IdFiscale']);
                        $Rubrica->setIstatComune($_POST[$this->name_form . '_DatiCorrispondente_IstatComune']);
                        $Rubrica->setNome($_POST[$this->name_form . '_DatiCorrispondente_Nome']);
                        $Rubrica->setTipo($_POST[$this->name_form . '_DatiCorrispondente_Tipo']);
                        //parametri Rubrica
                        $Rubrica->setCodice($_POST[$this->name_form . '_Rubrica_Codice']);

                        $ret = $paleoClient->ws_SaveVoceRubrica($OperatorePaleo, $Rubrica);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("FindRubricaExt Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    //gestione dell'upload del Documento Principale
                    case $this->name_form . '_DocumentoPrincipale_Stream':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            //$randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
                            //$randName = pathinfo($uplFile, PATHINFO_EXTENSION);
                            $randName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        //Out::valore($this->name_form . "_DocumentoPrincipale_Nome", App::$utente->getKey('TOKEN') . "-" . $_POST['file']);
                        Out::valore($this->name_form . "_DocumentoPrincipale_Nome", $randName);

                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoPrincipale = $base64File;
                        //fine upload del file principale

                        break;

                    //gestione dell'upload del Documento Allegato
                    case $this->name_form . '_DocumentoAllegato_Stream':
                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            //$randName = md5(rand() * time()) . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
                            //$randName = pathinfo($uplFile, PATHINFO_EXTENSION);
                            $randName = $_POST['file'];
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            App::log($uplFile);
                            App::log($destFile);
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                            } else {
                                Out::msgInfo("Upload File:", "File salvato in: " . $destFile);
                            }
                        } else {
                            Out::msgStop("Upload File", "Errore in Upload");
                        }
                        //Out::valore($this->name_form . "_DocumentoPrincipale_Nome", App::$utente->getKey('TOKEN') . "-" . $_POST['file']);
                        Out::valore($this->name_form . "_DocumentoAllegato_Nome", $randName);

                        $fp = @fopen($destFile, "rb", 0);
                        if ($fp) {
                            $binFile = fread($fp, filesize($destFile));
                            //fclose($fp);
                            $base64File = base64_encode($binFile);
                        } else {
                            break;
                        }
                        fclose($fp);
                        $this->StreamDocumentoAllegato = $base64File;
                        //fine upload del file allegato

                        break;

                    case $this->name_form . '_callProtocollazioneEntrata':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

//                        $OperatorePaleo = new itaOperatorePaleo();
//                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
//                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
//                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
//                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $reqProtocolloArrivo = new itareqProtocolloArrivo();
                        $reqProtocolloArrivo->setOperatore(array(
                            "CodiceUO" => $_POST[$this->name_form . '_OperatorePaleo_CodiceUO'],
                            "Cognome" => $_POST[$this->name_form . '_OperatorePaleo_Cognome'],
                            "Nome" => $_POST[$this->name_form . '_OperatorePaleo_Nome'],
                            "Ruolo" => $_POST[$this->name_form . '_OperatorePaleo_Ruolo']
                                )
                        );
                        //gestione dell'upload del Documento Principale
                        $reqProtocolloArrivo->setDocumentoPrincipale(array(
                            "Nome" => $_POST[$this->name_form . '_DocumentoPrincipale_Nome'],
                            "Stream" => $this->StreamDocumentoPrincipale
                                )
                        );
                        //gestione dell'upload dei Documenti Allegati (al momento gestito un solo allegato)
                        if ($_POST[$this->name_form . '_DocumentoAllegato_NumeroPagine'] != '') {
                            $DocAllegato = array(
                                "Allegato" => array(
                                    "Descrizione" => $_POST[$this->name_form . '_DocumentoAllegato_Descrizione'],
                                    "NumeroPagine" => $_POST[$this->name_form . '_DocumentoAllegato_NumeroPagine'],
                                    "Documento" => array(
                                        "Nome" => $_POST[$this->name_form . '_DocumentoAllegato_Nome'],
                                        "Stream" => $this->StreamDocumentoAllegato
                                    )
                                )
                            );
                        } else {
                            $DocAllegato = array(
                                "Allegato" => array(
                                    "Descrizione" => $_POST[$this->name_form . '_DocumentoAllegato_Descrizione'],
                                    "Documento" => array(
                                        "Nome" => $_POST[$this->name_form . '_DocumentoAllegato_Nome'],
                                        "Stream" => $this->StreamDocumentoAllegato
                                    )
                                )
                            );
                        }
                        $reqProtocolloArrivo->setDocumentiAllegati($DocAllegato);

                        //App::log($this->StreamDocumentoPrincipale);
                        //fine upload del file principale


                        $reqProtocolloArrivo->setCodiceRegistro($_POST[$this->name_form . '_reqProtocolloArrivo_CodiceRegistro']);
                        $reqProtocolloArrivo->setOggetto($_POST[$this->name_form . '_reqProtocolloArrivo_Oggetto']);
                        $reqProtocolloArrivo->setPrivato($_POST[$this->name_form . '_reqProtocolloArrivo_Privato']);
                        $reqProtocolloArrivo->setDPAI($_POST[$this->name_form . '_reqProtocolloArrivo_DPAI']);
                        if ($_POST[$this->name_form . '_reqProtocolloArrivo_DataArrivo'] != '') {
                            $DataArrivo = $_POST[$this->name_form . '_reqProtocolloArrivo_DataArrivo'];
                            App::log($DataArrivo);
                        } else {
                            //lasciare in bianco la data è come mettere la data corrente
                            $DataArrivo = date('Ymd');
                            App::log($DataArrivo);
                        }

                        $anno = substr($DataArrivo, 0, 4);
                        App::log($anno);
                        $mese = substr($DataArrivo, 4, 2);
                        App::log($mese);
                        $giorno = substr($DataArrivo, 6, 2);
                        App::log($giorno);
                        $DataArrivo = $anno . "-" . $mese . "-" . $giorno;
                        //App::log($DataArrivo);
                        $reqProtocolloArrivo->setDataArrivo($DataArrivo);
                        $reqProtocolloArrivo->setMittente(array(
                            "CodiceRubrica" => $_POST[$this->name_form . '_Mittente_CodiceRubrica'],
                            "CorrispondenteOccasionale" => array(
                                "MessaggioRisultato" => array(
                                    "Descrizione" => $_POST[$this->name_form . '_BEBase_Descrizione'],
                                    "TipoRisultato" => $_POST[$this->name_form . '_BEBase_TipoRisultato']
                                ),
                                "Cognome" => $_POST[$this->name_form . '_DatiCorrispondente_Cognome'],
                                "Email" => $_POST[$this->name_form . '_DatiCorrispondente_Email'],
                                "IdFiscale" => $_POST[$this->name_form . '_DatiCorrispondente_IdFiscale'],
                                "IstatComune" => $_POST[$this->name_form . '_DatiCorrispondente_IstatComune'],
                                "Nome" => $_POST[$this->name_form . '_DatiCorrispondente_Nome'],
                                "Tipo" => $_POST[$this->name_form . '_DatiCorrispondente_Tipo'],
                            )
                                )
                        );

                        //settaggi Classificazione
                        $NuovoFascicolo = array();
                        $NuovoFascicolo['CodiceClassifica'] = $_POST[$this->name_form . '_Classificazione_CodiceClassifica'];
                        $NuovoFascicolo['CodiceFaldone'] = $_POST[$this->name_form . '_Classificazione_CodiceFaldone'];
                        $NuovoFascicolo['Custode'] = array(
                            "CodiceUO" => $_POST[$this->name_form . '_Custode_CodiceUO'],
                            "Cognome" => $_POST[$this->name_form . '_Custode_Cognome'],
                            "Nome" => $_POST[$this->name_form . '_Custode_Nome'],
                            "Ruolo" => $_POST[$this->name_form . '_Custode_Ruolo']
                        );
                        $NuovoFascicolo['Descrizione'] = $_POST[$this->name_form . '_Classificazione_Descrizione'];
                        if ($_POST[$this->name_form . '_Classificazione_IdSerieArchivistica'] != '') {
                            $NuovoFascicolo['IdSerieArchivistica'] = $_POST[$this->name_form . '_Classificazione_IdSerieArchivistica'];
                        }
                        if ($_POST[$this->name_form . '_Classificazione_IdTipoDati'] != '') {
                            $NuovoFascicolo['IdTipoDati'] = $_POST[$this->name_form . '_Classificazione_IdTipoDati'];
                        }
                        $NuovoFascicolo['Note'] = $_POST[$this->name_form . '_Classificazione_Note'];
                        if ($_POST[$this->name_form . '_Classificazione_AnniConservazione'] != '') {
                            $NuovoFascicolo['AnniConservazione'] = $_POST[$this->name_form . '_Classificazione_AnniConservazione'];
                        }

                        if (!empty($NuovoFascicolo['CodiceClassifica']) && !empty($NuovoFascicolo['Custode']) && !empty($NuovoFascicolo['Descrizione'])) {
                            $Classificazione = array(
                                "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo'],
                                "NuovoFascicolo" => $NuovoFascicolo
                            );
                        } else {
                            $Classificazione = array(
                                "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo']
                            );
                        }
//                        $Classificazione = array(
//                                "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo']
//                        );
                        $reqProtocolloArrivo->setClassificazioni(array(
                            "Classificazione" => $Classificazione
                                )
                        );
                        App::log('fascicolo');
                        App::log($NuovoFascicolo);

                        App::log('classificazione');
                        App::log($Classificazione);

                        //settagio DocumentoPrincipale
                        $DocumentoPrincipale = $_POST[$this->name_form . '_DocumentoPrincipale_Stream'];

                        //settaggio Trasmissione
                        if (($_POST[$this->name_form . '_TrasmissioneRuolo_DataScadenza']) != '') {
                            $TrasmissioneRuolo = array(
                                "CodiceUODestinataria" => $_POST[$this->name_form . '_TrasmissioneRuolo_CodiceUODestinataria'],
                                "DataScadenza" => $_POST[$this->name_form . '_TrasmissioneRuolo_DataScadenza'],
                                "Note" => $_POST[$this->name_form . '_TrasmissioneRuolo_Note'],
                                "Ragione" => $_POST[$this->name_form . '_TrasmissioneRuolo_Ragione'],
                                "RuoloDestinatario" => $_POST[$this->name_form . '_TrasmissioneRuolo_RuoloDestinatario']
                            );
                        } else {
                            $TrasmissioneRuolo = array(
                                "CodiceUODestinataria" => $_POST[$this->name_form . '_TrasmissioneRuolo_CodiceUODestinataria'],
                                "Note" => $_POST[$this->name_form . '_TrasmissioneRuolo_Note'],
                                "Ragione" => $_POST[$this->name_form . '_TrasmissioneRuolo_Ragione'],
                                "RuoloDestinatario" => $_POST[$this->name_form . '_TrasmissioneRuolo_RuoloDestinatario']
                            );
                        }
                        //App::log($TrasmissioneRuolo);
                        //$DataScadenzaUtente=new DateTime ($_POST[$this->name_form . '_TrasmissioneUtente_DataScadenza']);
                        //App::log($DataScadenzaUtente);
                        $anno = substr($DataScadenzaUtente, 0, 4);
                        $mese = substr($DataScadenzaUtente, 4, 2);
                        $giorno = substr($DataScadenzaUtente, 6, 2);
                        $DataScadenzaUtente = $anno . "-" . $mese . "-" . $giorno;
                        $reqProtocolloArrivo->setTrasmissione(array(
                            "InvioOriginaleCartaceo" => $DataEmergenza,
                            "NoteGenerali" => $_POST[$this->name_form . '_Emergenza_Numero'],
                            "SegueCartaceo" => $_POST[$this->name_form . '_Emergenza_Segnatura'],
                            /*
                              "TrasmissioniRuolo" => array(
                              "TrasmissioneRuolo" => $TrasmissioneRuolo
                              ),
                             * 
                             */
                            "TrasmissioniUtente" => array(
                                "TrasmissioneUtente" => array(
                                    //"DataScadenza" => $DataScadenzaUtente,
                                    "Note" => $_POST[$this->name_form . '_TrasmissioneUtente_Note'],
                                    "OperatoreDestinatario" => array(
                                        "CodiceUO" => $_POST[$this->name_form . '_TrasmissioneUtente_CodiceUO'],
                                        "Cognome" => $_POST[$this->name_form . '_TrasmissioneUtente_Cognome'],
                                        "Nome" => $_POST[$this->name_form . '_TrasmissioneUtente_Nome'],
                                        "Ruolo" => $_POST[$this->name_form . '_TrasmissioneUtente_Ruolo']
                                    ),
                                    "Ragione" => $_POST[$this->name_form . '_TrasmissioneUtente_Ragione']
                                )
                            )
                                )
                        );

                        //settaggio Emergenza
                        if (($_POST[$this->name_form . '_Emergenza_Data'] != '') && isset($_POST[$this->name_form . '_Emergenza_Numero']) && isset($_POST[$this->name_form . '_Emergenza_Segnatura'])) {
                            $DataEmergenza = $_POST[$this->name_form . '_Emergenza_Data'];
                            $anno = substr($DataEmergenza, 0, 4);
                            $mese = substr($DataEmergenza, 4, 2);
                            $giorno = substr($DataEmergenza, 6, 2);
                            $DataEmergenza = $anno . "-" . $mese . "-" . $giorno;
                            $reqProtocolloArrivo->setEmergenza(array(
                                "Data" => $DataEmergenza,
                                "Numero" => $_POST[$this->name_form . '_Emergenza_Numero'],
                                "Segnatura" => $_POST[$this->name_form . '_Emergenza_Segnatura']
                                    )
                            );
                        }
                        $ret = $paleoClient->ws_ProtocollazioneEntrata($reqProtocolloArrivo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        App::log($risultato);
                        Out::msgInfo("ProtocollazioneEntrata Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callProtocollazionePartenza':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        App::log('entrato');


//                        $OperatorePaleo = new itaOperatorePaleo();
//                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
//                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
//                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
//                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $reqProtocolloPartenza = new itareqProtocolloPartenza();
                        $reqProtocolloPartenza->setOperatore(array(
                            "CodiceUO" => $_POST[$this->name_form . '_OperatorePaleo_CodiceUO'],
                            "Cognome" => $_POST[$this->name_form . '_OperatorePaleo_Cognome'],
                            "Nome" => $_POST[$this->name_form . '_OperatorePaleo_Nome'],
                            "Ruolo" => $_POST[$this->name_form . '_OperatorePaleo_Ruolo']
                                )
                        );
                        App::log('operatore');
                        //gestione dell'upload del Documento Principale
                        $reqProtocolloPartenza->setDocumentoPrincipale(array(
                            "Nome" => $_POST[$this->name_form . '_DocumentoPrincipale_Nome'],
                            "Stream" => $this->StreamDocumentoPrincipale
                                )
                        );
                        App::log('setDocumentoPrincipale');
                        //gestione dell'upload dei Documenti Allegati (al momento gestito un solo allegato)
                        if ($_POST[$this->name_form . '_DocumentoAllegato_NumeroPagine'] != '') {
                            $DocAllegato = array(
                                "Allegato" => array(
                                    "Descrizione" => $_POST[$this->name_form . '_DocumentoAllegato_Descrizione'],
                                    "NumeroPagine" => $_POST[$this->name_form . '_DocumentoAllegato_NumeroPagine'],
                                    "Documento" => array(
                                        "Nome" => $_POST[$this->name_form . '_DocumentoAllegato_Nome'],
                                        "Stream" => $this->StreamDocumentoAllegato
                                    )
                                )
                            );
                        } else {
                            $DocAllegato = array(
                                "Allegato" => array(
                                    "Descrizione" => $_POST[$this->name_form . '_DocumentoAllegato_Descrizione'],
                                    "Documento" => array(
                                        "Nome" => $_POST[$this->name_form . '_DocumentoAllegato_Nome'],
                                        "Stream" => $this->StreamDocumentoAllegato
                                    )
                                )
                            );
                        }
                        $reqProtocolloPartenza->setDocumentiAllegati($DocAllegato);
                        App::log('setDocumentiAllegati');

                        App::log($this->StreamDocumentoPrincipale);
                        //fine upload del file principale

                        $reqProtocolloPartenza->setCodiceRegistro($_POST[$this->name_form . '_reqProtocolloArrivo_CodiceRegistro']);
                        App::log('setCodiceRegistro');
                        $reqProtocolloPartenza->setOggetto($_POST[$this->name_form . '_reqProtocolloArrivo_Oggetto']);
                        App::log('setOggetto');
                        $reqProtocolloPartenza->setPrivato($_POST[$this->name_form . '_reqProtocolloArrivo_Privato']);
                        App::log('setPrivato');
                        $reqProtocolloPartenza->setDPAI($_POST[$this->name_form . '_reqProtocolloArrivo_DPAI']);
                        App::log('setDPAI');
                        $reqProtocolloPartenza->setDestinatari(array(
                            "Corrispondente" => array(
                                "CodiceRubrica" => $_POST[$this->name_form . '_Mittente_CodiceRubrica'],
                                "CorrispondenteOccasionale" => array(
                                    "MessaggioRisultato" => array(
                                        "Descrizione" => $_POST[$this->name_form . '_BEBase_Descrizione'],
                                        "TipoRisultato" => $_POST[$this->name_form . '_BEBase_TipoRisultato']
                                    ),
                                    "Cognome" => $_POST[$this->name_form . '_DatiCorrispondente_Cognome'],
                                    "Email" => $_POST[$this->name_form . '_DatiCorrispondente_Email'],
                                    "IdFiscale" => $_POST[$this->name_form . '_DatiCorrispondente_IdFiscale'],
                                    "IstatComune" => $_POST[$this->name_form . '_DatiCorrispondente_IstatComune'],
                                    "Nome" => $_POST[$this->name_form . '_DatiCorrispondente_Nome'],
                                    "Tipo" => $_POST[$this->name_form . '_DatiCorrispondente_Tipo'],
                                )
                            )
                                )
                        );
                        App::log('setDestinatari');

                        //settaggi Classificazione
                        $NuovoFascicolo = array();
                        $NuovoFascicolo['CodiceClassifica'] = $_POST[$this->name_form . '_Classificazione_CodiceClassifica'];
                        $NuovoFascicolo['CodiceFaldone'] = $_POST[$this->name_form . '_Classificazione_CodiceFaldone'];
                        $NuovoFascicolo['Custode'] = array(
                            "CodiceUO" => $_POST[$this->name_form . '_Custode_CodiceUO'],
                            "Cognome" => $_POST[$this->name_form . '_Custode_Cognome'],
                            "Nome" => $_POST[$this->name_form . '_Custode_Nome'],
                            "Ruolo" => $_POST[$this->name_form . '_Custode_Ruolo']
                        );
                        $NuovoFascicolo['Descrizione'] = $_POST[$this->name_form . '_Classificazione_Descrizione'];
                        if ($_POST[$this->name_form . '_Classificazione_IdSerieArchivistica'] != '') {
                            $NuovoFascicolo['IdSerieArchivistica'] = $_POST[$this->name_form . '_Classificazione_IdSerieArchivistica'];
                        }
                        if ($_POST[$this->name_form . '_Classificazione_IdTipoDati'] != '') {
                            $NuovoFascicolo['IdTipoDati'] = $_POST[$this->name_form . '_Classificazione_IdTipoDati'];
                        }
                        $NuovoFascicolo['Note'] = $_POST[$this->name_form . '_Classificazione_Note'];
                        if ($_POST[$this->name_form . '_Classificazione_AnniConservazione'] != '') {
                            $NuovoFascicolo['AnniConservazione'] = $_POST[$this->name_form . '_Classificazione_AnniConservazione'];
                        }


//                        if (!empty($NuovoFascicolo)) {
//                            $Classificazione = array(
//                                    "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo'],
//                                    "NuovoFascicolo" => $NuovoFascicolo
//                            );
//                        } else {
//                            $Classificazione = array(
//                                    "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo']
//                            );
//                        }
                        $Classificazione = array(
                            "CodiceFascicolo" => $_POST[$this->name_form . '_Cassificazione_CodiceFascicolo']
                        );
                        $reqProtocolloPartenza->setClassificazioni(array(
                            "Classificazione" => $Classificazione
                                )
                        );

                        //settagio DocumentoPrincipale
                        $DocumentoPrincipale = $_POST[$this->name_form . '_DocumentoPrincipale_Stream'];

                        //settaggio Trasmissione
                        if (($_POST[$this->name_form . '_TrasmissioneRuolo_DataScadenza']) != '') {
                            $TrasmissioneRuolo = array(
                                "CodiceUODestinataria" => $_POST[$this->name_form . '_TrasmissioneRuolo_CodiceUODestinataria'],
                                "DataScadenza" => $_POST[$this->name_form . '_TrasmissioneRuolo_DataScadenza'],
                                "Note" => $_POST[$this->name_form . '_TrasmissioneRuolo_Note'],
                                "Ragione" => $_POST[$this->name_form . '_TrasmissioneRuolo_Ragione'],
                                "RuoloDestinatario" => $_POST[$this->name_form . '_TrasmissioneRuolo_RuoloDestinatario']
                            );
                        } else {
                            $TrasmissioneRuolo = array(
                                "CodiceUODestinataria" => $_POST[$this->name_form . '_TrasmissioneRuolo_CodiceUODestinataria'],
                                "Note" => $_POST[$this->name_form . '_TrasmissioneRuolo_Note'],
                                "Ragione" => $_POST[$this->name_form . '_TrasmissioneRuolo_Ragione'],
                                "RuoloDestinatario" => $_POST[$this->name_form . '_TrasmissioneRuolo_RuoloDestinatario']
                            );
                        }
                        //App::log($TrasmissioneRuolo);
                        //$DataScadenzaUtente=new DateTime ($_POST[$this->name_form . '_TrasmissioneUtente_DataScadenza']);
                        //App::log($DataScadenzaUtente);
                        $anno = substr($DataScadenzaUtente, 0, 4);
                        $mese = substr($DataScadenzaUtente, 4, 2);
                        $giorno = substr($DataScadenzaUtente, 6, 2);
                        $DataScadenzaUtente = $anno . "-" . $mese . "-" . $giorno;
                        $reqProtocolloPartenza->setTrasmissione(array(
                            "InvioOriginaleCartaceo" => $DataEmergenza,
                            "NoteGenerali" => $_POST[$this->name_form . '_Emergenza_Numero'],
                            "SegueCartaceo" => $_POST[$this->name_form . '_Emergenza_Segnatura'],
                            //  "TrasmissioniRuolo" => array(
                            //              "TrasmissioneRuolo" => $TrasmissioneRuolo
                            //      ),
                            "TrasmissioniUtente" => array(
                                "TrasmissioneUtente" => array(
                                    //"DataScadenza" => $DataScadenzaUtente,
                                    "Note" => $_POST[$this->name_form . '_TrasmissioneUtente_Note'],
                                    "OperatoreDestinatario" => array(
                                        "CodiceUO" => $_POST[$this->name_form . '_TrasmissioneUtente_CodiceUO'],
                                        "Cognome" => $_POST[$this->name_form . '_TrasmissioneUtente_Cognome'],
                                        "Nome" => $_POST[$this->name_form . '_TrasmissioneUtente_Nome'],
                                        "Ruolo" => $_POST[$this->name_form . '_TrasmissioneUtente_Ruolo']
                                    ),
                                    "Ragione" => $_POST[$this->name_form . '_TrasmissioneUtente_Ragione']
                                )
                            )
                                )
                        );

                        //settaggio Emergenza
                        if (($_POST[$this->name_form . '_Emergenza_Data'] != '') && isset($_POST[$this->name_form . '_Emergenza_Numero']) && isset($_POST[$this->name_form . '_Emergenza_Segnatura'])) {
                            $DataEmergenza = $_POST[$this->name_form . '_Emergenza_Data'];
                            $anno = substr($DataEmergenza, 0, 4);
                            $mese = substr($DataEmergenza, 4, 2);
                            $giorno = substr($DataEmergenza, 6, 2);
                            $DataEmergenza = $anno . "-" . $mese . "-" . $giorno;
                            $reqProtocolloPartenza->setEmergenza(array(
                                "Data" => $DataEmergenza,
                                "Numero" => $_POST[$this->name_form . '_Emergenza_Numero'],
                                "Segnatura" => $_POST[$this->name_form . '_Emergenza_Segnatura']
                                    )
                            );
                        }
                        $ret = $paleoClient->ws_ProtocollazionePartenza($reqProtocolloPartenza);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("ProtocollazionePartenza Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetSerieArchivisticheFascicoli':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);
                        $ret = $paleoClient->ws_GetSerieArchivisticheFascicoli($OperatorePaleo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("GetSerieArchivisticheFascicoli Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callApriRegistro':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $CodiceRegistro = $_POST[$this->name_form . '_ApriRegistro_CodiceRegistro'];
                        $ret = $paleoClient->ws_ApriRegistro($OperatorePaleo, $CodiceRegistro);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("ApriRegistro Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callChiudiRegistro':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $CodiceRegistro = $_POST[$this->name_form . '_ChiudiRegistro_CodiceRegistro'];
                        $ret = $paleoClient->ws_ChiudiRegistro($OperatorePaleo, $CodiceRegistro);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("ChiudiRegistro Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');

                        break;

                    case $this->name_form . '_callGetFile':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        $GetFile = new itaGetFile();
                        $GetFile->setIdFile($_POST[$this->name_form . '_GetFile_IdFile']);
                        $ret = $paleoClient->ws_GetFile($OperatorePaleo, $GetFile);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }
                        
                        $risultato = $paleoClient->getResult();
                        $attachments = $paleoClient->getAttachments();
                        $nome = $risultato['GetFileResult']['Oggetto']['Nome'] . "." . $risultato['GetFileResult']['Oggetto']['Estensione'];
                        $path = "/tmp/testPaleo/" . $nome;
                        file_put_contents($path,$attachments[0]);
                        //Out::msgInfo("GetFile Attachments", '<pre>' . print_r($attachments, true) . '</pre>');
                        Out::openDocument(utiDownload::getUrl($nome, $path, true));
                        
                        Out::msgInfo("GetFile Result", '<pre style="font-size:1.5em">' . print_r($risultato, true) . '</pre>');
                        break;
                    case $this->name_form . '_callCercaDocumentoProtocollo':
                        $paleoClient = new itaPaleoClient();
                        $this->setClientConfig($paleoClient);

                        $OperatorePaleo = new itaOperatorePaleo4();
                        $OperatorePaleo->setCodiceUO($_POST[$this->name_form . '_OperatorePaleo_CodiceUO']);
                        $OperatorePaleo->setCognome($_POST[$this->name_form . '_OperatorePaleo_Cognome']);
                        $OperatorePaleo->setNome($_POST[$this->name_form . '_OperatorePaleo_Nome']);
                        $OperatorePaleo->setRuolo($_POST[$this->name_form . '_OperatorePaleo_Ruolo']);
                        //App::log($OperatorePaleo);

                        $CercaDocumentoProtocollo = new itaCercaDocumentoProtocollo();
                        $CercaDocumentoProtocollo->setDocNumber($_POST[$this->name_form . '_CercaDocumentoProtocollo_DocNumber']);
                        if ($_POST[$this->name_form . '_CercaDocumentoProtocollo_Segnatura'] != '') {
                            $CercaDocumentoProtocollo->setSegnatura($_POST[$this->name_form . '_CercaDocumentoProtocollo_Segnatura']);
                        }

                        //App::log($reqFindRubrica);

                        $ret = $paleoClient->ws_CercaDocumentoProtocollo($OperatorePaleo, $CercaDocumentoProtocollo);
                        if (!$ret) {
                            if ($paleoClient->getFault()) {
                                Out::msgStop("Fault", '<pre style="font-size:1.5em">' . print_r($paleoClient->getFault(), true) . '</pre>');
                            } elseif ($paleoClient->getError()) {
                                Out::msgStop("Error", '<pre style="font-size:1.5em">' . print_r($paleoClient->getError(), true) . '</pre>');
                            }
                            break;
                        }

                        $risultato = print_r($paleoClient->getResult(), true);
                        Out::msgInfo("CercaDocumentoProtocollo Result", '<pre style="font-size:1.5em">' . $risultato . '</pre>');
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
//        App::$utente->removeKey($this->name_Form . '_StreamDocumentoPrincipale');
        $this->close = true;
        Out::closeDialog($this->name_Form);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}

?>
