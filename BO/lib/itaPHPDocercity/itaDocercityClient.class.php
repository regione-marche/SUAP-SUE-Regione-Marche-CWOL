<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
require_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

define('CALL_TYPE_SAVE_CUSTOM_MODEL', 'SAVE_CUSTOM_MODEL');
define('CALL_TYPE_INIT_ENV', 'INIT_ENV');
define('CALL_TYPE_INSERT_DOCUMENT', 'INSERT_DOCUMENT');
define('CALL_TYPE_QUERY_BY_UUID', 'QUERY_BY_UUID');
define('CALL_TYPE_QUERY', 'QUERY');
define('CALL_TYPE_QUERYALL', 'QUERYALL');
define('CALL_TYPE_COUNT_QUERY', 'COUNT_QUERY');
define('CALL_TYPE_COUNT_QUERYALL', 'COUNT_QUERYALL');
define('CALL_TYPE_GET_CONTENT', 'GET_CONTENT');
define('CALL_TYPE_GET_PLACE_BY_UUID', 'GET_PLACE_BY_UUID');
define('CALL_TYPE_UPDATE_DOCUMENT_METADATA', 'UPDATE_DOCUMENT_METADATA');
define('CALL_TYPE_UPDATE_DOCUMENT_CONTENT', 'UPDATE_DOCUMENT_CONTENT');
define('CALL_TYPE_DELETE_DOCUMENT_BY_UUID', 'DELETE_DOCUMENT_BY_UUID');
define('CALL_TYPE_GET_VERSION', 'GET_VERSION');

/**
 *
 * Classe di collegamento a Docercity per docer
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocercity
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    23.02.2016
 * @link
 * @see
 * 
 */
class itaDocercityClient {

    private $restClient;
    private $docercityHost;
    private $docercityPath;
    private $errCode;
    private $errMessage;
    private $result;
    private $httpStatus;
    private $utf8_encode;
    private $utf8_decode;

    public function __construct() {
        $this->restClient = new itaRestClient();
    }

    /**
     * Restituisce versione libreria Docercity
     * @return array Esito
     */
    public function version() {
        $data = array(
            'CALL_TYPE' => CALL_TYPE_GET_VERSION
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * Inserimento documento
     * 
     * 
     * @param String $docType Tipo di documento
     * @param String $place Posizione (non usato, viene messo per mantenere la stessa firma di alfresco)
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     * @param String $content Documento da inserire
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare in docer (key: nome metadato, value: valore) 
     * @param String $codEnte Codice ente docer
     * 
     * @return true/false su getResult : String UUID del documento inserito in caso di esito positivo
     * 
     */
    public function insertDocument($docType, $place, $fileName, $mimeType, $content, $aspects, $props, $codEnte) {
        $data = array(
            'CODENTE' => $codEnte,
            'DOC_TYPE' => $docType,
            'DATA' => $this->createXmlForInsertOrUpdateDocument($aspects, $props),
            'FILENAME' => $fileName,
            'MIME_TYPE' => $mimeType,
            'CALL_TYPE' => CALL_TYPE_INSERT_DOCUMENT
        );

        return $this->handleResult($this->call($data, 'post', base64_encode($content)));
    }

    /**
     * NON PREVISTO IN DOCER
     * Conta elementi in funzione dei criteri di ricerca impostati 
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare in docer (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa per la ricerca full text
     * @return int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQuery($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return null;
    }

    /**
     * NON PREVISTO IN DOCER
     * Conta elementi in funzione dei criteri di ricerca impostati 
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare in docer (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa di ricerca per tutti i metadati e full text
     * @return int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQueryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return null;
    }

    /**
     * Ricerca documenti
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente (Corrisponde all'ente su docer)
     * @param String $codAoo Codice AOO (Corrisponde all'aoo su docer)
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare a docer (key: nome metadato, value: valore) 
     * @param String $fullText Stringa per ricerca fullText
     * @param Integer $page Pagina inizio ricerca (Non usato in docer)
     * @param Integer $blockSize Dimensione blocco (Non usato in docer)
     * @return array Risultati della ricerca
     */
    public function query($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '', $page = 0, $blockSize = 0) {
        return $this->executeQuery(CALL_TYPE_QUERY, $docType, $codEnte, $codAoo, $aspects, $props, $fullText);
    }

    /**
     * Ricerca libera documenti
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente (Corrisponde alla cartella di Alfresco)
     * @param String $codAoo Cidice AOO (Corrisponde alla cartella di Alfresco)
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @param String $fullText Stringa per ricerca fullText
     * @param Integer $page Pagina inizio ricerca (0=prima pagina)
     * @param Integer $blockSize Dimensione blocco (0=carica tutto)
     * @return array Risultati della ricerca
     */
    public function queryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '', $page = 0, $blockSize = 0) {
        return $this->executeQuery(CALL_TYPE_QUERYALL, $docType, $codEnte, $codAoo, $aspects, $props, $fullText);
    }

    /**
     * Ricerca per UUID
     * 
     * @param String $uuid Tipo di documento
     * @return true/false su getResult array metadati
     */
    public function queryByUUID($uuid) {
        $data = array(
            'UUID' => $uuid,
            'CALL_TYPE' => CALL_TYPE_QUERY_BY_UUID
        );
        if (!$this->handleResult($this->call($data))) {
            return false;
        }
        // Elabora risultato
        $results = array();
        try {
            if ($this->utf8_decode) {
                //@TODO FIX-DECODIFICA
                //$decoded = utf8_decode(base64_decode($this->getResult()));  
                $decoded = base64_decode($this->getResult());
            } else {
                $decoded = base64_decode($this->getResult());
            }
            $xmlObj = new itaXML;
            if ($xmlObj->setXmlFromString($decoded)) {
                $results = $xmlObj->getArray();
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore analisi XML risultato');
            }
            $this->setResult($results);
        } catch (Exception $ex) {
            $this->setErrCode(-1);
            $this->setErrMessage($ex->getMessage());
            return false;
        }
        return true;
    }

    /**
     * NON PREVISTO IN DOCER
     * Restituisce posizione logica del documento su Alfresco, dato in ingresso il suo UUID
     * 
     * @param String $uuid Tipo di documento
     * @return String posizione del documento
     */
    public function placeByUUID($uuid) {
        return null;
    }

    /**
     * Aggiorna metadati documento
     * 
     * @param String $uuid UUID documento
     * @param String $docType Tipo di documento (non usato, viene messo per mantenere la stessa firma di alfcity)
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * 
     * #return true/false
     */
    public function updateDocumentMetadata($codEnte, $uuid, $docType, $aspects, $props) {
        $data = array(
            'CODENTE' => $codEnte,
            'UUID' => $uuid,
            'DATA' => $this->createXmlForInsertOrUpdateDocument($aspects, $props),
            'CALL_TYPE' => CALL_TYPE_UPDATE_DOCUMENT_METADATA
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * NON GESTITO IN DOCER
     * 
     * Aggiorna contenuto documento
     * @deprecated
     * 
     * @param String $uuid UUID documento
     * @param String $content Contenuto documento
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     */
    public function updateDocumentContent($uuid, $content, $fileName, $mimeType) {
        return null;
    }

    /**
     * Cancella documento
     * 
     * @param String $codEnte Codice Ente docer
     * @param String $uuid UUID documento da cancellare
     */
    public function deleteDocumentByUUID($uuid, $codEnte = null) {
        $data = array(
            'CODENTE' => $codEnte,
            'UUID' => $uuid,
            'CALL_TYPE' => CALL_TYPE_DELETE_DOCUMENT_BY_UUID
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * Legge documento
     * 
     * @param String $uuid UUID documento da cancellare
     * @param String $codEnte Codice Ente (non usato in alfresco)
     * @return true/false su getResult : binary Contenuto (null in caso di errore)
     */
    public function contentByUUID($uuid, $codEnte = null) {
        $data = array(
            'CODENTE' => $codEnte,
            'UUID' => $uuid,
            'CALL_TYPE' => CALL_TYPE_GET_CONTENT
        );
        if (!$this->handleResult($this->call($data))) {
            return false;
        }

        // Elabora risultato
        try {
            $content = null;
            $decoded = base64_decode($this->getResult());
            $xmlObj = new itaXML;
            if ($xmlObj->setXmlFromString($decoded)) {
                $results = $xmlObj->getArray();
                $content = file_get_contents($results['CONTENT'][0]['URL'][0]['@textNode']);
            }
        } catch (Exception $ex) {
            $this->setErrCode(-1);
            $this->setErrMessage($ex->getMessage());
            return false;
        }
        $this->setResult($content);
        return true;
    }

    // Funzione non gestita
    public function saveCustomModel($tipdoc) {
        return null;
    }

    // Funzione non gestita
    public function initEnv() {
        return null;
    }

    /**
     * Ritorna un array di metadati. 
     * 
     * @param array $listBgdMetDoc lista di record bgd_metdoc che contengono i metadati e le regole per reperirli
     * @param array $dizionario array composto dalla chiave MAIN in cui c'è il main record e dalla chiave EXT in cui ci 
     *                          sono i valori esterni

     * @return array metadati chiave/valore
     */
    public function getMetadata($listBgdMetDoc, $dizionario) {
        return null;
    }

    private function call($data, $verb = 'post', $contentRaw = null, $mimeType = null) {

        $this->restClient->setCurlopt_url($this->alfcityHost);
        if ($verb == 'post') {
            $result = $this->restClient->post($this->alfcityPath, $data, array(), $contentRaw, $mimeType);
        } else {
            $result = $this->restClient->$verb($this->alfcityPath, $data, array());
        }
        if ($result) {
            $xmlstr = $this->restClient->getResult();
            $result = trim($xmlstr);
        } else {
            $this->setErrCode($this->restClient->getErrCode());
            $this->setErrMessage($this->restClient->getErrMessage());
            $result = false;
        }
        $this->setHttpStatus($this->restClient->getHttpStatus());

        return $result;
    }

    public function setParameters($parameters = array()) {
        $this->docercityHost = $parameters['docercityHost'];
        $this->docercityPath = $parameters['docercityPath'];
    }

    public function setParametersFromJsonString($json) {
        try {
            $parameters = json_decode($json, true);
            if (is_array($parameters)) {
                $this->setParameters($parameters);
            } else {
                return false;
            }
            return true;
        } catch (Exception $exc) {
            return false;
        }
    }

    private function executeQuery($callType, $docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        $data = array(
            'DOC_TYPE' => $docType,
            'CODENTE' => $codEnte,
            'CODAOO' => $codAoo,
            'DATA' => $this->createXmlForQuery($aspects, $props, $fullText),
            'CALL_TYPE' => $callType
        );
        $this->handleResult($this->call($data));

        // Elabora risultato
        $results = array();
        if ($this->getErrCode() == 0) {
            $decoded = base64_decode($this->getMessage());
            $xmlObj = new itaXML;
            if ($xmlObj->setXmlFromString($decoded)) {
                $results = $xmlObj->getArray();
            }
        }

        return $results;
    }

    /**
     * Ritorna l'xml in formato stringa codificata in base 64 e con urlencode per inserimento documento
     * @param type $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param type $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @return String xml in base 64 e con urlencode 
     */
    private function createXmlForInsertOrUpdateDocument($aspects, $props) {
        $xmlData = new SimpleXMLElement('<Document/>');
        $aspectsChild = $xmlData->addChild('Aspects');
        foreach ($aspects as $key => $value) {
            $aspectChild = $aspectsChild->addChild('Aspect');
            $aspectChild->addChild("Name", $key);
            $aspectChild->addChild("Value", $value);
        }
        $propertiesChild = $xmlData->addChild('Properties');
        foreach ($props as $key => $value) {
            $propertyChild = $propertiesChild->addChild('Property');
            $propertyChild->addChild("Name", $key);
            $propertyChild->addChild("Value", $value);
        }

        return urlencode(base64_encode($xmlData->asXML()));
    }

    /**
     * Ritorna l'xml in formato stringa codificata in base 64 e con urlencode per ricerca
     * @param type $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param type $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @return String xml in base 64 e con urlencode 
     */
    private function createXmlForQuery($aspects, $props, $fullText) {
        $xmlData = new SimpleXMLElement('<Query/>');
        $aspectsChild = $xmlData->addChild('Aspects');
        foreach ($aspects as $key => $value) {
            $aspectChild = $aspectsChild->addChild('Aspect');
            $aspectChild->addChild("Name", $key);
            $aspectChild->addChild("Value", $value);
        }
        $propertiesChild = $xmlData->addChild('Properties');
        foreach ($props as $key => $value) {
            $propertyChild = $propertiesChild->addChild('Property');
            $propertyChild->addChild("Name", $key);
            $propertyChild->addChild("Value", $value);
        }
        $textChild = $xmlData->addChild('Text');
        $textChild->addChild("Value", $fullText);

        return urlencode(base64_encode($xmlData->asXML()));
    }

    private function handleResult($result) {

        /*
         *  il risultato vuoto se call non va a buon fine setErrMessage già valorizzato
         */
        if ($result === false) {
            return false;
        }
        $this->setResult(null);
        $this->setErrMessage('');
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($result);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage('Xml Non valido');
            return false;
        }
        $xmlResult = $xmlObj->toArray($xmlObj->asObject());
        $this->setErrCode($xmlResult['ESITO'][0]['@textNode']);
        if ($xmlResult['ESITO'][0]['@textNode'] == '1') {
            $this->setErrMessage($xmlResult['MESSAGGIO'][0]['@textNode']);
            return false;
        } else {
            $this->setResult($xmlResult['MESSAGGIO'][0]['@textNode']);
        }
        return true;
    }

    public function getDocercityHost() {
        return $this->docercityHost;
    }

    public function getDocercityPath() {
        return $this->docercityPath;
    }

    public function setDocercityHost($docercityHost) {
        $this->docercityHost = $docercityHost;
    }

    public function setDocercityPath($docercityPath) {
        $this->docercityPath = $docercityPath;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    private function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    private function setErrMessage($message) {
        $this->errMessage = $message;
    }

    public function getHttpStatus() {
        return $this->httpStatus;
    }

    private function setHttpStatus($httpStatus) {
        $this->httpStatus = $httpStatus;
    }

    function getResult() {
        return $this->result;
    }

    function setResult($result) {
        $this->result = $result;
    }

    function getUtf8_encode() {
        return $this->utf8_encode;
    }

    function setUtf8_encode($utf8_encode) {
        $this->utf8_encode = $utf8_encode;
    }

    function getUtf8_decode() {
        return $this->utf8_decode;
    }

    function setUtf8_decode($utf8_decode) {
        $this->utf8_decode = $utf8_decode;
    }

}

?>