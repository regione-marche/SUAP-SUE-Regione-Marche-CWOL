<?php

/**
 *
 * Classe per collegamento ws ANPR - Superclasse servizi
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     Massimo Biagioli
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    28.05.2018
 * @link
 * @see
 * @since
 * */
require_once(ITA_LIB_PATH . '/nusoap/nusoap.php');
require_once(ITA_LIB_PATH . '/nusoap/nusoapmime.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaSOAP.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaJSON.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';

abstract class itaANPRClientSuper {

    //REGOLA PER EVITARE DI AGGIUNGERE IL PADRE 
    //ESEMPIO con regola:
    //<datiControllo><tipoMutazione>01</tipoMutazione><tipoMutazione>14</tipoMutazione><gestioneCF>3</gestioneCF></datiControllo>
    //ESEMPIO senza regola:
    //<datiControllo><tipoMutazione><tipoMutazione>01</tipoMutazione><tipoMutazione>14</tipoMutazione></tipoMutazione><gestioneCF>3</gestioneCF></datiControllo>
    CONST NOT_CREATED_PARENT_NODE_ELEMENT = "notCreatedParentnode";
    //nome directory 
    CONST DIRECTORY_NAME_ATTACHMENTS = "attachments";
    //forzatura nodo, anche se vuoto, per chiamata 1010, dove datiControllo deve essere vuoto -https://github.com/italia/anpr/issues/102
    CONST FORCE_NODE_ELEMENT = "forceNode";

    protected $namespace;
    protected $namespaces;
    protected $webservices_url = '';
    protected $connectionTimeout = 2400;
    protected $responseTimeout = 2400;
    protected $debugLevel;
    protected $version;
    protected $result;
    protected $error;
    protected $fault;
    protected $attachments = array();
    protected $responseAttachments = array();
    protected $headers;
    protected $signCert;
    protected $bodyId;
    protected $wsArgs;
    protected $testataRichiestaSoapval;
    protected $bodySoapValArray = array();
    protected $groupId;
    protected $validationXsdTag;

    public function __construct() {
        $this->bodyId = uniqid("BODY_");
        $this->init();
        $this->clearAttachments();
    }

    /**
     * Inizializzazione namespace, ecc...
     */
    protected abstract function init();

    public function inizializzaChiamata($args) {
        $this->wsArgs = $args;
        $this->testataRichiestaSoapval = $this->creaTestataRichiesta($args);
    }

    public function eseguiChiamata($wsse, $methodName) {
        $this->setHeaders($wsse);
        $params = $this->serializeParams();

        //Rimuovo i tag vuoti come <codiceFiscale/>
        $pattern = '/\<[A-Za-z0-9]+\/\>/';
        $replacement = '';
        $params = preg_replace($pattern, $replacement, $params);
//        file_put_contents('C:\Works\PhpDev\dati\itaEngine\BO\testi\enteS004\CityPeople\ANPR\param.xml', $params);

        if ($this->isXMLContentValid($methodName, $params)) {
            $this->signSoapMessage($wsse, $params, $methodName);
            return $this->ws_call($methodName, $params, 'ns2:', false, $soapMessage);
        } else { //Errore di validazione
            return false;
        }
    }

    private function isXMLContentValid($methodName, $xmlContent, $version = '1.0', $encoding = 'utf-8') {
        if (trim($xmlContent) == '') {
            return false;
        }

        $fileXsd = ITA_LIB_PATH . '/itaPHPANPR/spec/wsdl' . $this->getGroupId() . '/' . $this->getValidationXsdTag() . '.xsd';

        $xmlHeader = '<?xml version="1.0" encoding="UTF-8"?><data>';
        $xmlFooter = '</data>';
        $xmlContent = "<n1:" . $methodName . " xsi:schemaLocation=\"http://sogei.it/ANPR/" . $this->getValidationXsdTag() . " " . $this->getValidationXsdTag() . ".xsd\" xmlns:n1=\"http://sogei.it/ANPR/" . $this->getValidationXsdTag() . "\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\">" . $xmlContent . "</n1:" . $methodName . ">";
        $paramFileName = $this->wsArgs['inPath'] . '/' . $this->wsArgs['callId'] . '_params.xml';

        //Serializzo anche i parametri che trovo su ws aggiungo un nodo metadata
        $xmlContentTagMetadata = "";
        $mappa = $this->generaMappaChiaviWS($this->wsArgs['testataRichiesta']['operazioneRichiesta']);
        $mappa['testataRichiesta'] = 'testataRichiesta'; //Questo non viene restituito
        foreach ($this->wsArgs as $key => $value) {
            $isMetadato = false;
            //se non è un array lo aggiungo come metadato 
            if (is_array($mappa)) {
                if (in_array($key, $mappa)) {
                    $isMetadato = false; //E' della chiamata ANPR
                } else {
                    $isMetadato = true;
                }
            } elseif (is_array($value) == False) { //Se non sono riuscito a verificare la mappa, uso il vecchio metodo: se è un array è di ANPR
                $isMetadato = false;
            } else {
                $isMetadato = true;
            }

            if ($isMetadato) {
//            if (is_array($value) == False) { //cambiato controllo con ISSUE #658 - Tag errati nel campo metadata (SG il 02/05/2019)
                $xmlContentTagMetadata .= "<" . $key . ">" . $value . "</" . $key . ">";
            }
        }
        $xmlContentComplete = $xmlHeader . $xmlContent . "<metadata>" . $xmlContentTagMetadata . "</metadata>" . $xmlFooter;

        //Pretty XML
        $xmlContentCompletePretty = $this->prettyXML($xmlContentComplete);

        $saved_file = file_put_contents($paramFileName, $xmlContentCompletePretty);
        if (($saved_file === false) || ($saved_file == -1)) {
            return false;
        }

        libxml_use_internal_errors(true);

        $doc = new DOMDocument($version, $encoding);
        $doc->preserveWhiteSpace = FALSE;
        //$doc->loadXML($xmlContent);
        $doc->loadXML(utf8_encode($xmlContent));
        $doc->formatOutput = TRUE;

        if (!$doc->schemaValidate($fileXsd)) {
            $errorMessages = $this->libxml_display_errors();
            //Scrittura File degli errori 
            $paramFileName = $this->wsArgs['inPath'] . '/' . $this->wsArgs['callId'] . '_out_Error.txt';
            file_put_contents($paramFileName, $errorMessages);
            return false;
        } else {
            return true;
        }
    }

    private function libxml_display_errors() {
        $messages = "";
        $errors = libxml_get_errors();
        foreach ($errors as $error) {
            if (strlen($messages) > 0) {
                $messages .= "<br/>\n";
            }
            $messages .= $this->libxml_display_error($error);
        }
        libxml_clear_errors();
        return $messages;
    }

    //Formattazione stringa errori validazione xml 
    private function libxml_display_error($error) {
        $return = "<br/>\n";
        switch ($error->level) {
            case LIBXML_ERR_WARNING:
                $return .= "<b>Warning $error->code</b>: ";
                break;
            case LIBXML_ERR_ERROR:
                $return .= "<b>Error $error->code</b>: ";
                break;
            case LIBXML_ERR_FATAL:
                $return .= "<b>Fatal Error $error->code</b>: ";
                break;
        }
        $return .= trim($error->message);
        if ($error->file) {
            $return .= " in <b>$error->file</b>";
        }
        $return .= " on line <b>$error->line</b>\n";

        return $return;
    }

    public function creaBody($bodyKeys, $exceptionKeys = array(), $forceKeys = array()) {
        $this->bodySoapValArray = array();
        foreach ($bodyKeys as $bodyNode => $bodyKey) {
            if ($bodyKey == 'datiControllo') { //Per debug, mettere qua il nome del tag da debuggare
                $a = 0;
            }
            $bodyElementSoapvalArr = array();
            $toAppend = true;
            $this->creaBodyElement($bodyElementSoapvalArr, $bodyNode, $toAppend, $exceptionKeys, $forceKeys);
            // se ho dei nodi da aggiungere 
            if (count($bodyElementSoapvalArr) != 0) {
                if ($toAppend) {
                    $this->bodySoapValArray[] = new soapval($bodyKey, $bodyKey, $bodyElementSoapvalArr, false, false);
                } else {
                    $i = 1;
                    $soapValElementCopy = array();
                    $isArrayMultiTag = false;
                    foreach ($bodyElementSoapvalArr as $indice => $soapValElement) {
                        if (is_array($soapValElement) && gettype($bodyElementSoapvalArr[$indice]) == gettype($bodyElementSoapvalArr[$indice + 1]) || $isArrayMultiTag) {
                            $soapValElementCopy = array();
                            $soapValElementCopy[] = $soapValElement;
                            $this->bodySoapValArray[] = new soapval($bodyKey, $bodyKey, $soapValElementCopy, false, false);
                            unset($bodyElementSoapvalArr[$indice]); //Rimuovo il tag, così non lo conto se entro nel ciclo sotto, nel caso abbia una lista e un tag tipo gestioneCF(tag) e tipoMutazione(lista)
                            $soapValElementCopy[] = array();
                            $isArrayMultiTag = true;
                        } else {
                            $isArrayMultiTag = false;
                            $soapValElementCopy[] = $soapValElement;
                            if (count($bodyElementSoapvalArr) == $i) {
                                $this->bodySoapValArray[] = new soapval($bodyKey, $bodyKey, $soapValElementCopy, false, false);
                            }
                            $i = $i + 1;
                        }
                    }

                    $toAppend = true;
                } //FOR
            } else if (in_array($bodyKey, $forceKeys)) { //Anche se il tag è vuoto, va scritto ugualmente //#568 Convivenza
                $this->bodySoapValArray[] = new soapval($bodyKey, $bodyKey, '', false, false); //ANPR1010 datiControllo, obbligatorio, ma non implementato - https://github.com/italia/anpr/issues/102
            }
        }
    }

    private function creaBodyElement(&$bodyElementSoapvalArr, $bodyKey, &$toAppend = true, $exceptionKeys = array(), $forceKeys = array()) {
        // Cerca l'array in base alla chiave (considera anche array multi-livello)
        $bodyKeyParts = explode('.', $bodyKey);
        $bodyValues = $this->wsArgs;
        //legge i dati da scrivere dagli argomenti
        foreach ($bodyKeyParts as $bodyKeyPart) {
            $bodyValues = $bodyValues[$bodyKeyPart];
        }
        // se non ci sono i dati da scrivere per quel nodo lo salto
        if ($bodyValues != null) {
            // Scorre tutte le chiavi dell'array        
            foreach ($bodyValues as $key => $value) {
                //Cambiato controllo per valori == 0 
                if ($value !== null) {

                    // Se l'elemento corrente è un array, elabora ricorsivamente i sottoelementi
                    if (is_array($value)) {

                        // Controlla se l'elemento ha una chiave numerica. Se si, deve trattare il valore di rotorno come un array
                        if (is_numeric($key)) {

                            // Scorre tutti gli elementi dell'array
                            $entrySoapvalArr = array();
                            foreach ($value as $currentKey => $currentValue) {
                                $entrySoapvalArr[] = new soapval($currentKey, $currentKey, $currentValue, false, false);
                            }
                            $bodyElementSoapvalArr[] = $entrySoapvalArr;

                            //la soapVal della chiave viene fatta esternamente (siamo sul primo nodo 
                            $toAppend = false;
                        } else {
//
//                            // In questo caso si tratta di un elemento annidati
                            $entrySoapvalArr = array();
                            $this->creaBodyElement($entrySoapvalArr, $bodyKey . "." . $key, $toAppend, $exceptionKeys, $forceKeys);
                            //devo creare gli elementi solo se ho almeno un valore 
                            if (count($entrySoapvalArr)) {
                                //controllo sulla lista delle eccezioni se devo inserire l'elemento 
                                //(usato esempio per tipo mutazione del 5008)
                                $tmp = $bodyKey != null ? $bodyKey . "." . $key : $key;
                                if (count($exceptionKeys) && array_key_exists($tmp, $exceptionKeys) && $exceptionKeys[$tmp] == self::NOT_CREATED_PARENT_NODE_ELEMENT) {
                                    $bodyElementSoapvalArr[] = $entrySoapvalArr;
                                } else {
                                    $bodyElementSoapvalArr[] = new soapval($key, $key, $entrySoapvalArr, false, false);
                                }
                            }
                            $toAppend = false;
                        }
                    } else {
//                        if (!empty(isset($value))) {
                        if (!cwbLibCheckInput::IsNBE($value)) {
                            if (is_numeric($key)) {
                                $bodyElementSoapvalArr[] = new soapval($bodyKeyPart, $bodyKeyPart, $value, false, false);
                            } else {
                                $bodyElementSoapvalArr[] = new soapval($key, $key, $value, false, false);
                            }
                        } else if (in_array($bodyKey, $forceKeys)) { //Anche se il tag è vuoto, va scritto ugualmente //#568 Convivenza
                            $bodyElementSoapvalArr[] = new soapval($bodyKeyPart, $bodyKeyPart, '', false, false); //ANPR1010 datiControllo, obbligatorio, ma non implementato - https://github.com/italia/anpr/issues/102
                        }
                    }
                }
            }
        }
    }

    private function serializeParams() {
// testata
        $param = $this->testataRichiestaSoapval->serialize("literal");

// body

        foreach ($this->bodySoapValArray as $bodySoapValEntry) {
            $result = "";
            $result = $bodySoapValEntry->serialize("literal");
//Non dovrebbero esserci dei Tag con item. Se così fosse creare una mappa
//per effettuare la bonifica dei tag. Esempio datiControllo della chimata 5008 
            $result = str_replace("<item>", "", $result);
            $result = str_replace("</item>", "", $result);

            $param = $param . $result;
        }
        return $param;
    }

    public function clearAttachments() {
        $this->attachments = array();
    }

    public function addAttachment($data) {
        $this->attachments[] = $data;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

    function setNamespaces($namespaces) {
        $this->namespaces = $namespaces;
    }

    public function setWebservicesEndPoint($webservices_uri) {
        $this->webservices_uri = $webservices_uri;
    }

    public function setConnectionTimeout($connectionTimeout) {
        $this->connectionTimeout = $connectionTimeout;
    }

    public function setResponseTimeout($responseTimeout) {
        $this->responseTimeout = $responseTimeout;
    }

    function setDebugLevel($debugLevel) {
        $this->debugLevel = $debugLevel;
    }

    public function getResult() {
        return $this->result;
    }

    public function getError() {
        return $this->error;
    }

    public function getFault() {
        return $this->fault;
    }

    function setVersion($version) {
        $this->version = $version;
    }

    public function getHeaders() {
        return $this->headers;
    }

    public function setHeaders($headers) {
        $this->headers = $headers;
    }

    public function getSignCert() {
        return $this->signCert;
    }

    public function setSignCert($signCert) {
        $this->signCert = $signCert;
    }

    private function clearResult() {
        $this->result = null;
        $this->error = null;
        $this->fault = null;
    }

    protected function ws_call($operationName, $param, $ns = "web:", $returnSoapMessage = false, &$soapMessage) {
        $this->clearResult();
        /*
         * Istanza del client
         */
        $client = new nusoap_client_mime($this->webservices_uri, false);
        /*
         * Configurazioni di base
         */
        $client->setDebugLevel($this->debugLevel); //Impostare a 1 per debug
        if ($this->debugLevel > 0) {
            if (empty($this->name)) {
                $nomeFileLog = 'logNusoap_' . $operationName . '_' . strtoupper(cwbParGen::getUtente()) . '_' . date('Y-m-d_H.i.s') . '.html';
                $this->name = App::getPath('temporary.appsPath') . '/ente' . App::$utente->getKey('ditta') . '/citypeople/Debug/' . $nomeFileLog;
                file_put_contents($this->name, 'InizioCall'); //Se va a buon fine, sovrascrivo la parte della firma, con la chiamata completa
            }
        }

        $client->timeout = $this->connectionTimeout > 0 ? $this->connectionTimeout : 120;
        $client->response_timeout = $this->responseTimeout;
        $client->soap_defencoding = 'UTF-8';
        $client->soapBodyAttributes = array(
            'xmlns:wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd',
            'wsu:Id' => $this->bodyId
        );

        /*
         * File di log
         */
        $client->writeRequestFile = true;
        $client->requestFileName = $this->wsArgs['inPath'] . '/' . $this->wsArgs['callId'] . '_out.xml';
        $client->writeResponseFile = true;
        $client->responseFileName = $this->wsArgs['inPath'] . '/' . $this->wsArgs['callId'] . '_in.xml';
        $client->writeFaultFile = true;
        $client->faultFileName = $this->wsArgs['inPath'] . '/' . $this->wsArgs['callId'] . '_out_Error.xml';

        /*
         * Impostazione certificato SSL 
         */
//        $client->curl_options[CURLOPT_CAINFO] = ITA_LIB_PATH . '/itaJava/itaANPR/keystore/ANPR_test.cer';
//        $client->curl_options[CURLOPT_SSL_VERIFYPEER] = false;
//        $client->curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        
        if ($this->wsArgs['testataRichiesta']['tipoInvio'] == 'TEST') {
            $ca = ITA_LIB_PATH . '/itaPHPANPR/CA/CAPostazioniSvil.cer';
        } else {
            $ca = ITA_LIB_PATH . '/itaPHPANPR/CA/CAPostazioniANPR.cer';
        }
        if (file_exists($ca)) { //Se il file non esite uso la precedente tipologia, ovvero non verifico l'autenticità del server ANPR
            $client->curl_options[CURLOPT_CAINFO] = $ca;
            $client->curl_options[CURLOPT_SSL_VERIFYPEER] = 1; //Abilito il controllo
            $client->curl_options[CURLOPT_SSL_VERIFYHOST] = 2;
        } else {
            $client->curl_options[CURLOPT_CAINFO] = ITA_LIB_PATH . '/itaJava/itaANPR/keystore/ANPR_test.cer';
            $client->curl_options[CURLOPT_SSL_VERIFYPEER] = false;
            $client->curl_options[CURLOPT_SSL_VERIFYHOST] = 0;
        }
        /*
         * Configurazione envelope SOAP
         */
        $soapAction = ''; //$this->namespace . "/" . $operationName ;        
        $rpcParams = null;
        $style = 'rpc';
        $use = 'literal';

        /*
         * Gestione attachments
         */
        foreach ($this->attachments as $attachment) {
            $client->addAttachment($attachment['data'], $attachment['filename'], $attachment['contenttype'], $attachment['cid']);
        }

        /*
         * Chiamata
         */
//        $result = $client->call($ns . $operationName, $param, $this->namespaces, $soapAction, $this->headers, $rpcParams, $style, $use, $returnSoapMessage, $soapMessage);
        $result = $client->call($ns . $operationName, utf8_encode($param), $this->namespaces, $soapAction, $this->headers, $rpcParams, $style, $use, $returnSoapMessage, $soapMessage);
        if ($returnSoapMessage === true) {
            if ($this->debugLevel > 0) {
                file_put_contents($this->name, $client->getDebug());
            }
            return;
        }

        if ($this->debugLevel > 0) {
            file_put_contents($this->name, $client->getDebug()); //Se va a buon fine, sovrascrivo la parte della firma, con la chiamata completa
        }

        /**
         * Legge gli allegati della risposta e li salva sulla cartella callId
         */
        if (count($client->responseAttachments)) {
            //Estrapolo la riposta del multipart, o meglio scrivo il solo file XML
            $file_IN = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Body xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/">' . utf8_encode($client->document . "</SOAP-ENV:Body>");
//            file_put_contents("C:/Works/PhpDev/dati/itaEngine/tmp/itaEngine/ANPR/2018/08/13/22615_3003_5b7137b5376f0_in_full.xml",$client->response);
            //            file_put_contents($client->responseFileName, $file_IN);
            //Pretty XML
            $file_INPretty = $this->prettyXML($file_IN);
            file_put_contents($client->responseFileName, $file_INPretty);

            $this->responseAttachments = $client->responseAttachments;
            //Creazione cartella attachements
            $dirAttachment = $this->makeFolder($this->wsArgs['outPath'] . DIRECTORY_SEPARATOR . self::DIRECTORY_NAME_ATTACHMENTS);
            //Creazione File nella cartella attachemt
            foreach ($this->responseAttachments as &$attachment) {
                //creazione File name
                $dirCallId = $this->makeFolder($dirAttachment . DIRECTORY_SEPARATOR . $this->wsArgs['callId']);
                //rimuove i caratteri strani nel  nome del file 
                $filename = preg_replace("/[^a-z0-9\_\-\.\@]/i", '', $attachment["cid"]);
                //rimuove estensione
                //$filename = preg_replace('/\\.[^.\\s]{3,4}$/', '', $filename); (nel JAR resta l'estesione e viene aggiunto XML)
//                $extension = itaMimeTypeUtils::estraiEstensioneDaContentType($this->mime2ext($attachment["contenttype"]));
                $extension = itaMimeTypeUtils::estraiEstensioneDaContentType($attachment["contenttype"]);
                $attachment['filename'] = $filename;
                if ($extension == 'bin') {
                    $extension = 'txt';
                    $attachment["cid"] = $filename . '.txt';
                }
//                $extension = itaMimeTypeUtils::estraiEstensioneDaContentType(itaMimeTypeUtils::getMimeTypes($attachment["contenttype"]));
                $saved_file = file_put_contents($dirCallId . DIRECTORY_SEPARATOR . $filename . "." . $extension, $attachment["data"]);
                if (($saved_file === false) || ($saved_file == -1)) {
                    $this->error = "File non creato:" . $filename . $extension;
                    return false;
                }
            }
        }

        /*
         * 
         * Controllo del risultato
         */
        if ($client->fault) {
            $this->fault = $client->faultstring;
            return false;
        } else {
            $err = $client->getError();
            if ($err) {
                $this->error = $err;
                return false;
            }
        }
        $this->result = $result;
        return true;
    }

    private function makeFolder($dir = "") {
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    protected function creaTestataRichiesta($args) {
        $testataRichiestaSoapvalArr = array();
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "idOperazioneComune", "idOperazioneComune", $args['testataRichiesta']['idOperazioneComune'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "codMittente", "codMittente", $args['testataRichiesta']['codMittente'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "codDestinatario", "codDestinatario", $args['testataRichiesta']['codDestinatario'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "operazioneRichiesta", "operazioneRichiesta", $args['testataRichiesta']['operazioneRichiesta'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "dataOraRichiesta", "dataOraRichiesta", $this->formatDateTime($args['testataRichiesta']['dataOraRichiesta'], true), false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "tipoOperazione", "tipoOperazione", $args['testataRichiesta']['tipoOperazione'], false, false);
//        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "protocolloComune", "protocolloComune", $args['testataRichiesta']['protocolloComune'], false, false);
//        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "dataProtocolloComune", "dataProtocolloComune", $this->formatDate($args['testataRichiesta']['dataProtocolloComune']), false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "tipoInvio", "tipoInvio", $args['testataRichiesta']['tipoInvio'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "dataDecorrenza", "dataDecorrenza", $this->formatDate($args['testataRichiesta']['dataDecorrenza']), false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "nomeApplicativo", "nomeApplicativo", $args['testataRichiesta']['nomeApplicativo'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "versioneApplicativo", "versioneApplicativo", $args['testataRichiesta']['versioneApplicativo'], false, false);
        $this->insertNewSafeSoapValIntoArray($testataRichiestaSoapvalArr, "fornitoreApplicativo", "fornitoreApplicativo", $args['testataRichiesta']['fornitoreApplicativo'], false, false);
        return new soapval('testataRichiesta', 'testataRichiesta', $testataRichiestaSoapvalArr, false, false);
    }

    protected function insertNewSafeSoapValIntoArray(&$dest, $name = 'soapval', $type = false, $value = -1, $element_ns = false, $type_ns = false, $attributes = false) {
        if (!$value) {
            return;
        }
        $dest[] = new soapval($name, $type, $value, $element_ns, $type_ns, $attributes);
    }

    protected function formatDate($toFormat) {
        $date = new DateTime($toFormat, new DateTimeZone('Europe/Rome'));
//        return $date->format('Y-m-dP');
        return $date->format('Y-m-d');
    }

    protected function formatDateTime($toFormat, $calcTime = false) {
        if ($calcTime === true && strlen($toFormat) === 10) {
            $tmp = new DateTime();
            $toFormat .= ' ' . $tmp->format("H:i:s.z");
        }
        $date = new DateTime($toFormat, new DateTimeZone('Europe/Rome'));

        return $date->format('Y-m-d\TH:i:s.zP');
    }

    protected function signSoapMessage($wsse, $param, $wsName) {
        $this->ws_call($wsName, $param, 'ns2:', true, $soapMessage);
        $references = array(
            array(
                'ns' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd',
                'tagName' => 'Timestamp'
            ),
            array(
                'ns' => 'http://schemas.xmlsoap.org/soap/envelope/',
                'tagName' => 'Body'
            )
        );
        $wsse = itaSOAP::signSoapMessage($soapMessage, $wsse, $this->getSignCert(), $references);
        $this->setHeaders($wsse);
    }

    public function getResponseAttachments() {
        return $this->responseAttachments;
    }

    /**
     * Restituisce il livello di ACL per utente e tipo documento specificati
     * @param string $user Utente
     * @param string $documentType Tipo documento
     * @return int Livello ACL:
     *      0 = Full Access
     *      1 = Normal Access
     *      2 = Read only
     */
    public function getACL($user, $documentType) {
        return 0;
    }

    public function getGroupId() {
        return $this->groupId;
    }

    public function setGroupId($groupId) {
        $this->groupId = $groupId;
    }

    public function getValidationXsdTag() {
        return $this->validationXsdTag;
    }

    public function setValidationXsdTag($validationXsdTag) {
        $this->validationXsdTag = $validationXsdTag;
    }

    public function generaMappaChiaviWS($WSID) {
        $doc = new DOMDocument("1.0", "ISO-8859-15");
        $doc->preserveWhiteSpace = true;
        $fileXsd = ITA_LIB_PATH . '/itaPHPANPR/spec/wsdl' . $this->getGroupId() . '/' . $this->getValidationXsdTag() . '.xsd';
//        $doc->load($fileXsd);
//        $doc->save($fileXsd . '.xml');
//        $xmlfile = file_get_contents($fileXsd . '.xml');
        $xmlfile = file_get_contents($fileXsd);
        $parseObj = str_replace($doc->lastChild->prefix . ':', "", $xmlfile);
        $ob = simplexml_load_string($parseObj);
        $json = json_encode($ob);
        $data = json_decode($json, true);

        //Determino gli elementi presenti nella chiamata
        $tagRichiesta = 'Richiesta' . $WSID;
//        foreach ($data['element'] as $key => $value) {
        foreach ($data['xselement'] as $key => $value) {
            if ($value['@attributes']['name'] == $tagRichiesta) {//
                foreach ($value['xscomplexType']['xssequence']['xselement'] as $mappaChiavi) {
                    if ($mappaChiavi['@attributes']['name'] <> 'testataRichiesta') { //Escludo testata richiesta, ma non so perchè
                        $nomeTag = $mappaChiavi['@attributes']['name'];
                        if (!empty($nomeTag)) {
                            $bodyKeys[$nomeTag] = $nomeTag;
                        }
                    }
                }
                foreach ($value['xscomplexType']['xssequence']['xschoice']['xselement'] as $mappaChiavi) { //Caso 4005, il primo livello è un choice
                    if ($mappaChiavi['@attributes']['name'] <> 'testataRichiesta') {
                        $nomeTag = $mappaChiavi['@attributes']['name'];
                        $bodyKeys[$nomeTag] = $nomeTag;
                    }
                }
            }
        }

        return $bodyKeys;
    }

    protected function _stripSoapHeaders($response) {
        // Find first occurance of xml tag
        preg_match('/(?<xml><.*?\?xml version=.*>)/', $response, $match);
        $xml = $match['xml'];

        // Strip SOAP http headers, and SOAP XML
        $offset = strpos($response, $xml) + strlen($xml . PHP_EOL);
        return substr($response, $offset);
    }

    protected function _parseMimeData($data) {
        // Find MIME boundary string
        preg_match('/--(?<MIME_boundary>.+?)\s/', $data, $match);
        $mimeBoundary = $match['MIME_boundary']; // Always unique compared to content
        // Copy headers to client
        if (preg_match('/(Content-Type: .+?)' . PHP_EOL . '/', $data, $match)) {
            header($match[1]);
        }
        $contentType = $match[1];
        if (preg_match('/(Content-Transfer-Encoding: .+?)' . PHP_EOL . '/', $data, $match)) {
            header($match[1]);
        }

        // Remove string headers and MIME boundaries from data
        preg_match('/(.*Content-Id.+' . PHP_EOL . ')/', $data, $match);
        $start = strpos($data, $match[1]) + strlen($match[1]);
        $end = strpos($data, "--$mimeBoundary--");
        $data = substr($data, $start, $end - $start);

        return trim($data, "\r\n");
    }

    //Rende leggibile una stringa XML
    protected function prettyXML($strXML) {
        $dom = new DOMDocument;
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($strXML);
        $xmlPretty = $dom->saveXML();
        if ($xmlPretty) {
            if ($xmlPretty === '<?xml version="1.0"?>') {
                return $strXML; //Se la stringa ? stata tagliata o non ? riuscita la formttazione rispondo con il tracciato originale
            } else {
                return $xmlPretty;
            }
        } else {
            return $strXML;
        }
    }

}
