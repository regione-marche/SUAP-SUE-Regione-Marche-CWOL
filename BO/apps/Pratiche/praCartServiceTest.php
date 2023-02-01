<?php

/**
 *
 * TEST Servizi Web CART
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Simone Franchi <simone.franchi@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    10.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */


include_once(ITA_LIB_PATH . '/itaPHPCart/itaCartServiceClient.class.php');
include_once ITA_BASE_PATH . './apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . './apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCart.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';

function praCartServiceTest() {
    $praCartServiceTest = new praCartServiceTest();
    $praCartServiceTest->parseEvent();
    return;
}

class praCartServiceTest extends itaModel {

    public $name_form = "praCartServiceTest";
    public $invioComunicazione_filePath;
    static private $tipoFo = "STARWS";

    function __construct() {
        parent::__construct();

        $this->invioComunicazione_filePath = App::$utente->getKey($this->nameForm . '_invioComunicazione_filePath');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_invioComunicazione_filePath', $this->invioComunicazione_filePath);
        }
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->setClientConfig();
                $this->CreaCombo();
                
                Out::setFocus('', $this->name_form . "_CONFIGEROG[wsEndpoint]");
                break;
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->name_form . '_FileComunicazione':

                        //Out::msgInfo('Dati UpLoad', print_r($this->formData, true));

                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            // Mi genera un nome causale
                            $randName = itaLib::getRandBaseName() . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                                break;
                            }
                            $this->invioComunicazione_filePath = $destFile;
                            Out::valore($this->name_form . '_invioComunicazione_nomeFile', $this->formData['file']);
                            //Out::msgInfo('Base 64', base64_encode(file_get_contents($uplFile)));
                        }


                        break;
                
                
                    case $this->name_form . '_callGetPraticheCart':
                
                        $starClient = new itaCartServiceClient();
                        $starClient->setErog_uri($this->formData[$this->name_form . '_CONFIGEROG']['wsEndpoint']);
                        $starClient->setErog_username($this->formData[$this->name_form . '_CONFIGEROG']['wsUser']);
                        $starClient->setErog_password($this->formData[$this->name_form . '_CONFIGEROG']['wsPassword']);
                        $starClient->setErog_namespacePrefix($this->formData[$this->name_form . '_CONFIGEROG']['wsNameSpace']);
                        $starClient->setErog_timeout($this->formData[$this->name_form . '_CONFIGEROG']['wsTimeout']);

//                          $param = array(
//                          'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
//                          'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
//                          );

                        $retCall = $starClient->ws_getAllMessagesId();
                        if (!$retCall) {
                          Out::msgStop("Errore GetPraticheCart", htmlentities($starClient->getFault() . " " . $starClient->getError));
                          break;
                        }
                        $arrayMessaggi = $starClient->getResult();
                        Out::msgInfo("Result", print_r($arrayMessaggi,true));
                          
                        if ($arrayMessaggi) {
                              
                              // Scorrere i messaggi trovati e rileggerli
                              
                              
                        }
                        break;
                          
                    case $this->name_form . '_callGetPratica':
                        $starClient = new itaCartServiceClient();
                        $starClient->setErog_uri($this->formData[$this->name_form . '_CONFIGEROG']['wsEndpoint']);
                        $starClient->setErog_username($this->formData[$this->name_form . '_CONFIGEROG']['wsUser']);
                        $starClient->setErog_password($this->formData[$this->name_form . '_CONFIGEROG']['wsPassword']);
                        $starClient->setErog_namespacePrefix($this->formData[$this->name_form . '_CONFIGEROG']['wsNameSpace']);
                        $starClient->setErog_timeout($this->formData[$this->name_form . '_CONFIGEROG']['wsTimeout']);

                        $param = array(
                            'idEGov' => $this->formData[$this->name_form . '_GetPratica_IdPratica']
                        );

                        $retCall = $starClient->ws_getMessage($param);
                        if (!$retCall) {
                            Out::msgStop("Errore GetPratica", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        $messaggio = base64_decode($result['message'] );
                        
                        $xmlObj = new itaXML;
                        $retXml = $xmlObj->setXmlFromString($messaggio);
                        if (!$retXml) {
                            return false;
                        }
                        $arrayXml = $xmlObj->toArray($xmlObj->asObject());

//                        Out::msgInfo("Result", print_r($result,true));
//                        Out::msgInfo("Messaggio base 64", $result['message'] );
//                        Out::msgInfo("Messaggio", print_r($messaggio,true));
                        //Out::msgInfo("DOM", print_r($arrayXml,true));
                        
                        $corpo = $arrayXml['SOAP-ENV:Body'][0];
                        Out::msgInfo("DOM", print_r($corpo,true));
                        
                        $confermaRicezione = $corpo['confermaRicezioneReq'][0];
                        
                        if ($confermaRicezione) {   // (in_array('confermaRicezioneReq', $corpo)) {
                            $idMessaggio = $corpo['confermaRicezioneReq'][0]['idMessaggio'][0][itaXML::textNode];
                            Out::msgInfo("confermaRicezione", print_r($idMessaggio,true));
                        } else {
                            $idMessaggio = $corpo['riceviStimoloAsync'][0]['idMessaggio'][0][itaXML::textNode];
                            Out::msgInfo("idMessaggio", print_r($idMessaggio,true));
                        }                        
                        
                        break;

                    case $this->name_form . '_callRichiediAllegato':

                        //Out::msgInfo('Base 64', base64_encode(file_get_contents($this->invioComunicazione_filePath)));
                        $starClient = new itaCartServiceClient();
                        //$starClient->setErog_uri($this->formData[$this->name_form . '_CONFIGEROG']['wsEndpoint']);
                        $starClient->setFrui_uri($this->formData[$this->name_form . '_CONFIGFRUI']['wsEndpoint']);
                        $starClient->setFrui_username($this->formData[$this->name_form . '_CONFIGFRUI']['wsUser']);
                        $starClient->setFrui_password($this->formData[$this->name_form . '_CONFIGFRUI']['wsPassword']);
                        $starClient->setFrui_namespacePrefix($this->formData[$this->name_form . '_CONFIGFRUI']['wsNameSpace']);
                        $starClient->setFrui_timeout($this->formData[$this->name_form . '_CONFIGFRUI']['wsTimeout']);

                        $param = $this->formData[$this->name_form . '_RichiediAllegato_xml'];
                        
                        
                        //$param = file_get_contents($this->richiediAllegato_nomeFile);
//                        $param = array(
//                            'OpenSPCoop_PD' => file_get_contents($this->invioComunicazione_filePath)
//                        );

                        $retCall = $starClient->ws_fruizioneTest($param, "richiediAllegatoReq");
                        if (!$retCall) {
                            Out::msgStop("Errore", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }

                        Out::msgInfo("Risultato Invio Comunicazione", print_r($starClient->getResult(), true));
                        //Out::msgInfo("Request", print_r($starClient->getRequest(), true));
                        //Out::msgInfo("Response", print_r($starClient->getResponse(), true));
                        break;
                        
                    case $this->name_form . '_callSetStatoPratica':
                        $starClient = new itaCartServiceClient();
                        $starClient->setFrui_uri($this->formData[$this->name_form . '_CONFIGFRUI']['wsEndpoint']);
                        $starClient->setFrui_username($this->formData[$this->name_form . '_CONFIGFRUI']['wsUser']);
                        $starClient->setFrui_password($this->formData[$this->name_form . '_CONFIGFRUI']['wsPassword']);
                        $starClient->setFrui_namespacePrefix($this->formData[$this->name_form . '_CONFIGFRUI']['wsNameSpace']);
                        $starClient->setFrui_timeout($this->formData[$this->name_form . '_CONFIGFRUI']['wsTimeout']);

                        $param = $this->formData[$this->name_form . '_SetStatoPratica_xml'];
                        
//                        $param = array(
//                            '' => $this->formData[$this->name_form . '_SetStatoPratica_xml']
//                        );

                        $retCall = $starClient->ws_fruizioneTest($param, "aggiornaStatoPraticaReq");
                        if (!$retCall) {
                            Out::msgStop("Errore Fruizione", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        Out::msgInfo("Risultato Aggiornamento stato pratica", print_r($starClient->getResult(), true));
                        
                        break;

                    case $this->name_form . '_callSetFruizione':
                        $starClient = new itaCartServiceClient();
                        $starClient->setFrui_uri($this->formData[$this->name_form . '_CONFIGFRUI']['wsEndpoint']);
                        $starClient->setFrui_username($this->formData[$this->name_form . '_CONFIGFRUI']['wsUser']);
                        $starClient->setFrui_password($this->formData[$this->name_form . '_CONFIGFRUI']['wsPassword']);
                        $starClient->setFrui_namespacePrefix($this->formData[$this->name_form . '_CONFIGFRUI']['wsNameSpace']);
                        $starClient->setFrui_timeout($this->formData[$this->name_form . '_CONFIGFRUI']['wsTimeout']);

                        $param = $this->formData[$this->name_form . '_SetFruizione_xml'];
                        $tipoStimolo = $this->formData[$this->name_form . '_TipoStimolo'];

        //Out::msgInfo("Parametro", htmlspecialchars($param));

//        $param = '<proc:stimolo> 
//                <proc:mittente>
//			<proc:tipologia>SUAP</proc:tipologia>
//			<proc:ente>13.13.1.M.000.051004</proc:ente>
//		</proc:mittente>
//		<proc:data>2019-06-18T18:02:23</proc:data>
//		<proc:idPratica>FRNSMN70B18D612E-11062019-1220</proc:idPratica>
//		<proc:attributiSpecifici>
//			<proc:comunicazione>
//				<proc:destinatario>FACCT</proc:destinatario>
//				<proc:oggetto>Comunicazione Generico</proc:oggetto>
//				<proc:allegato>
//					<proc:contentID>abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org</proc:contentID>
//					<proc:contentType>application/x-pkcs7-mime</proc:contentType>
//					<proc:nomefileOriginale>segnalazione_errore.PDF.P7M</proc:nomefileOriginale>
//				</proc:allegato>
//			</proc:comunicazione>
//		</proc:attributiSpecifici>
//	</proc:stimolo>';

                        
                        
                        $retCall = $starClient->ws_fruizioneTest($param, $tipoStimolo);
                        //$retCall = $starClient->ws_fruizione($param, $tipoStimolo);
                        if (!$retCall) {
                            Out::msgStop("Errore Fruizione", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        
                        $allegati = $starClient->getAttachments();
                        if ($allegati){
                            file_put_contents("D:/works/allegatoCart.pdf.p7m",$allegati[0]['data']);
                            Out::msgInfo("Allegati", print_r($allegati, true));
                        }


                        Out::msgInfo("Risultato Fruizione", print_r($starClient->getResult(), true));
                       //Out::msgInfo("Attachment", print_r($allegati, true));

                        
                        
                        break;
                     
                    case $this->name_form . '_callGetStimoliCart':
                        
                        $frontOffice = praFrontOfficeFactory::getFrontOfficeManagerInstance(praFrontOfficeManager::TYPE_FO_CART_WS);

                        if (!$frontOffice) {
                            Out::msgStop("Errore", "Non ritrovato il metodo per rileggere le pratiche");
                            break;
                        }


                        $this->scaricaStimoli($frontOffice);
                        
                        
                        break;
                        
                    case $this->name_form . '_callInvioComunicazione':
                        $starClient = new itaCartServiceClient();
                        $starClient->setFrui_uri($this->formData[$this->name_form . '_CONFIGFRUI']['wsEndpoint']);
                        $starClient->setFrui_username($this->formData[$this->name_form . '_CONFIGFRUI']['wsUser']);
                        $starClient->setFrui_password($this->formData[$this->name_form . '_CONFIGFRUI']['wsPassword']);
                        $starClient->setFrui_namespacePrefix($this->formData[$this->name_form . '_CONFIGFRUI']['wsNameSpace']);
                        $starClient->setFrui_timeout($this->formData[$this->name_form . '_CONFIGFRUI']['wsTimeout']);

                        $param = $this->formData[$this->name_form . '_SetComunicazione_xml'];
                        $tipoStimolo = $this->formData[$this->name_form . '_InvioComunicazione_tipoComunicazione'];
                        
                        //$file = file($this->invioComunicazione_filePath);

                        //Out::msgInfo("File Allegato", print_r($this->invioComunicazione_filePath,true));
                        
                        
                        //$cid = "abb5fd19e3d60669517ec9f8337228816ba0248da13aedf3@apache.org";
                        //$contentType = 'application/x-pkcs7-mime';
                        //$starClient->addAttachment('', $this->invioComunicazione_filePath, $contentType, $cid);
                        //$cid = $starClient->addAttachment('', $this->invioComunicazione_filePath, $contentType, $cid);

                        
                        $retCall = $starClient->ws_fruizioneTest($param, $tipoStimolo);
                        if (!$retCall) {
                            Out::msgStop("Errore Fruizione", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        
                        $allegati = $starClient->getAttachments();
                        if ($allegati){
                            file_put_contents("D:/works/test1.pdf.p7m",$allegati[0]['data']);
                            Out::msgInfo("Allegati", print_r($allegati, true));
                        }


                        Out::msgInfo("Risultato Aggiornamento stato pratica", print_r($starClient->getResult(), true));
                        
                        $praLibCart = new praLibCart();
                        
                        
                        if ($result['esito']){
                            if ($result['esito'] == 'OK') {
                                
                                $cart_invio_rec = array();
                                $cart_invio_rec['IDMESSAGGIO'] = $result['idMessaggio'];
                                $cart_invio_rec['DATAINVIO'] = $praLibCart->getDatetimeNow();
                                $cart_invio_rec['TIPOINVIO'] = "comunicazione";
                                $cart_invio_rec['DESTINATARIO'] = "FACCT";
                                $cart_invio_rec['ESITO'] = $result['esito'];
                                $cart_invio_rec['IDTABRIF'] = -1 ;
                                $cart_invio_rec['NOMETABRIF'] = "INVIO MANUALE";

                                try {
                                    $nRows = ItaDB::DBInsert($praLibCart->getITALWEB(), 'CART_INVIO', 'ROWID', $cart_invio_rec);
                                    if ($nRows == -1) {
                                        Out::msgInfo("Errore", "Inserimento su cart_invio non avvenuto.");
//                                        $this->setErrCode(-1);
//                                        $this->setErrMessage("Inserimento su cart_invio non avvenuto.");
                                        return false;
                                    }
                                } catch (Exception $e) {
                                    Out::msgInfo("Errore12", "Inserimento su cart_invio non avvenuto.");
//                                    $this->setErrCode(-1);
//                                    $this->setErrMessage("Errore nell'inserimento su cart_stimolofile.");
                                    return false;
                                }
                                
                            }
                        }
                        
                        
/*                        
                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idPratica' => $this->formData[$this->name_form . '_InvioComunicazione_IdPratica'],
                            'tipoComunicazione' => $this->formData[$this->name_form . '_InvioComunicazione_tipoComunicazione'],
                            'destinatario' => $this->formData[$this->name_form . '_InvioComunicazione_destinatario'],
                            'oggetto' => $this->formData[$this->name_form . '_InvioComunicazione_oggetto'],
                            'messaggio' => $this->formData[$this->name_form . '_InvioComunicazione_messaggio'],
                            'fileZip' => base64_encode(file_get_contents($this->invioComunicazione_filePath))
                        );

                        $retCall = $starClient->ws_InvioComunicazione($param);
                        if (!$retCall) {
                            Out::msgStop("Errore", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }

                        Out::msgInfo("Risultato Invio Comunicazione", print_r($starClient->getResult(), true));
                        //Out::msgInfo("Request", print_r($starClient->getRequest(), true));
                        //Out::msgInfo("Response", print_r($starClient->getResponse(), true));
*/
                         break;
 


                        
                }
                break;
        }
    }

    
    public function scaricaStimoli($frontOffice) {

        $arrayXml = $frontOffice->getElencoStimoliNuovi();

        // Se trova nuovi stimoli, li esamina 
        if ($arrayXml) {

            //Out::msgInfo("Riposta getElencoStimoliNuovi", print_r($arrayXml, true));

            $num = 1;
            foreach ($arrayXml as $idStimolo)
            {
                //Out::msgInfo("Risultato array ".$num , print_r($idStimolo, true));
                //Si rilegge lo stimolo CART
    //            if ($num == 10) {
    //                Out::msgInfo("Id Stimolo che si rilegge", $idStimolo);
    //                $frontOffice->leggistimolo($idStimolo);    
    //                break;
    //            }
                //Out::msgInfo("Stimolo", "Stimolo riletto -> " . $idStimolo);
                $frontOffice->leggistimolo($idStimolo);            

                $num++;

            }        

            Out::msgStop("Errore rilettura dati da CART", "Codice: " . $frontOffice->getErrCode() . " - " . $frontOffice->getErrMessage());

        }
        
        
/*        
        $arrayPratiche = $arrayXml['Pratica'];

        //Out::msgInfo("Array Pratiche : ".count($arrayPratiche), print_r($arrayPratiche, true));

        $num = 1;
        foreach ($arrayPratiche as $pratica => $datiPratica) {

            $IdPratica = $datiPratica['DatiPratica'][0]['IdPratica'][0][itaXml::textNode];

            //Out::msgInfo("Risultato array ".$num , print_r($IdPratica, true));

            $this->leggiPratica($IdPratica, $frontOffice);

            $num++;
        }
*/        

        
    }
    

    public function close() {
//        App::$utente->removeKey($this->nameForm . '_invioComunicazione_filePath');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function setClientConfig() {
        $config_tab = array();
        $devLib = new devLib();
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGENDPOINT', false);
        $configErog_rec['wsEndpoint'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGUSER', false);
        $configErog_rec['wsUser'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGPASSWD', false);
        $configErog_rec['wsPassword'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGNAMESPACE', false);
        $configErog_rec['wsNameSpace'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTEROGTIMEOUT', false);
        $configErog_rec['wsTimeout'] = $config_val['CONFIG'];

        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIENDPOINT', false);
        $configFrui_rec['wsEndpoint'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIPDELEGATA', false);
        $configFrui_rec['wsPDelegata'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIUSER', false);
        $configFrui_rec['wsUser'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUIPASSWD', false);
        $configFrui_rec['wsPassword'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUINAMESPACE', false);
        $configFrui_rec['wsNameSpace'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('CART_TOSCANA', 'codice', 'WSCARTFRUITIMEOUT', false);
        $configFrui_rec['wsTimeout'] = $config_val['CONFIG'];


        Out::valori($configErog_rec, $this->name_form . '_CONFIGEROG');
        Out::valori($configFrui_rec, $this->name_form . '_CONFIGFRUI');
    }

    private function CreaCombo() {

        Out::select($this->name_form . '_TipoStimolo', 1, "aggiornaStatoPraticaReq", "1", "aggiornaStatoPraticaReq");
        Out::select($this->name_form . '_TipoStimolo', 1, "confermaRicezioneReq", "0", "confermaRicezioneReq");
        Out::select($this->name_form . '_TipoStimolo', 1, "richiediAllegatoReq", "0", "richiediAllegatoReq");
        Out::select($this->name_form . '_TipoStimolo', 1, "statoPraticaReq", "0", "statoPraticaReq");
        Out::select($this->name_form . '_TipoStimolo', 1, "statoMessaggioReq", "0", "statoMessaggioReq");
        Out::select($this->name_form . '_TipoStimolo', 1, "inviaStimoloRequest", "0", "inviaStimoloRequest");

        Out::select($this->name_form . '_InvioComunicazione_tipoComunicazione', 1, "inviaStimoloRequest", "1", "inviaStimoloRequest");
        //Out::select($this->name_form . '_InvioComunicazione_tipoComunicazione', 1, "notifica", "0", "notifica");
        
        Out::select($this->name_form . '_InvioComunicazione_destinatario', 1, "FACCT", "1", "FACCT");
        Out::select($this->name_form . '_InvioComunicazione_destinatario', 1, "ASL", "0", "ASL");
        Out::select($this->name_form . '_InvioComunicazione_destinatario', 1, "AMBRT", "0", "AMBRT");
        
    }
    
}
