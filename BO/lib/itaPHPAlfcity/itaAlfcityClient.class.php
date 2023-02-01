<?php

require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
require_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

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
 * Classe per collegamento ad AlfCity per Alfresco
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPAlfcity
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.11.2015
 * @link
 * @see
 * 
 */
class itaAlfcityClient {

    const ALIAS_ASP_COM = 'ASP_COM';
    const DICT_EXT_KEY = 'EXT';
    const DICT_MAIN_KEY = 'MAIN';
    const DICT_FN_KEY = '#fn#';

    private $restClient;
    private $alfcityHost;
    private $alfcityPath;
    private $errCode;
    private $errMessage;
    private $result;
    private $httpStatus;
    private $utf8_encode;
    private $utf8_decode;
    private $connectionData;

    public function __construct() {
        $this->restClient = new itaRestClient();
    }

    /**
     * Restituisce versione libreria Alfcity
     * @return versione
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
     * @param String $docType Tipo di documento
     * @param String $place Posizione dentro alfresco in cui salvare il documento
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     * @param String $content Documento da inserire
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @param String $codEnte Codice ente (non usato, viene messo per mantenere la stessa firma di docer)     
     * 
     * @return true/false su getResult : String UUID del documento inserito in caso di esito positivo
     */
    public function insertDocument($docType, $place, $fileName, $mimeType, $content, $aspects, $props, $codEnte = null) {
        $fileName = $this->cleanSpecialChar($fileName);
        $data = array(
            'DOC_TYPE' => $docType,
            'PLACE' => $place,
            'DATA' => $this->createXmlForInsertOrUpdateDocument($aspects, $props),
            'FILENAME' => ($this->utf8_encode) ? utf8_encode($fileName) : $fileName,
            'MIME_TYPE' => $mimeType,
            'CALL_TYPE' => CALL_TYPE_INSERT_DOCUMENT
        );

        return $this->handleResult($this->call($data, 'post', base64_encode($content), $mimeType));
    }

    /**
     * Conta elementi in funzione dei criteri di ricerca impostati
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare ad alfresco (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa per la ricerca full text
     * 
     * @return true/false su getResult int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQuery($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return $this->executeCount(CALL_TYPE_COUNT_QUERY, $docType, $codEnte, $codAoo, $aspects, $props, $fullText);
    }

    /**
     * Conta elementi in funzione dei criteri di ricerca impostati (Ricerca libera)
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare ad alfresco (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa di ricerca per tutti i metadati e full text
     * 
     * @return true/false su getResult: int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQueryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return $this->executeCount(CALL_TYPE_COUNT_QUERYALL, $docType, $codEnte, $codAoo, $aspects, $props, $fullText);
    }

    /**
     * Ricerca documenti
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente (Corrisponde alla cartella di Alfresco)
     * @param String $codAoo Codice AOO (Corrisponde alla cartella di Alfresco)
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @param String $fullText Stringa per ricerca fullText
     * @param Integer $page Pagina inizio ricerca (0=prima pagina)
     * @param Integer $blockSize Dimensione blocco (0=carica tutto)
     * 
     * @return true/false su getResult: array Risultati della ricerca
     */
    public function query($docType, $codEnte, $codAoo, $aspects, $props, $fullText = null, $page = 0, $blockSize = 0) {
        return $this->executeQuery(CALL_TYPE_QUERY, $docType, $codEnte, $codAoo, $aspects, $props, $fullText, $page, $blockSize);
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
     * 
     * @return true/false su getResult: array Risultati della ricerca
     */
    public function queryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '', $page = 0, $blockSize = 0) {
        return $this->executeQuery(CALL_TYPE_QUERYALL, $docType, $codEnte, $codAoo, $aspects, $props, $fullText, $page, $blockSize);
    }

    /**
     * Ricerca per UUID
     * 
     * @param String $uuid Tipo di documento
     * 
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
                $decoded = utf8_decode(base64_decode($this->getResult()));  
                //$decoded = base64_decode($this->getResult());
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
     * Restituisce posizione logica del documento su Alfresco, dato in ingresso il suo UUID
     * 
     * @param String $uuid Tipo di documento
     * 
     * @return true/false su getResult String posizione del documento
     */
    public function placeByUUID($uuid) {
        $data = array(
            'UUID' => $uuid,
            'CALL_TYPE' => CALL_TYPE_GET_PLACE_BY_UUID
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * Aggiorna metadati documento documento
     * 
     * @param String $uuid UUID documento
     * @param String $docType Tipo di documento
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @param String $codEnte Codice Ente (non usato, viene messo per mantenere la stessa firma di docer)
     * 
     * @return true/false 
     */
    public function updateDocumentMetadata($uuid, $docType, $aspects, $props, $codEnte = null) {
        $data = array(
            'DOC_TYPE' => $docType,
            'UUID' => $uuid,
            'DATA' => $this->createXmlForInsertOrUpdateDocument($aspects, $props),
            'CALL_TYPE' => CALL_TYPE_UPDATE_DOCUMENT_METADATA
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * Aggiorna contenuto documento
     * 
     * @param String $uuid UUID documento
     * @param String $content Contenuto documento
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     * 
     * @return true/false 
     */
    public function updateDocumentContent($uuid, $content, $fileName, $mimeType) {
        $data = array(
            'CONTENT' => urlencode(base64_encode($content)),
            'UUID' => $uuid,
            'FILENAME' => $fileName,
            'MIME_TYPE' => $mimeType,
            'CALL_TYPE' => CALL_TYPE_UPDATE_DOCUMENT_CONTENT
        );
        return $this->handleResult($this->call($data));
    }

    /**
     * Cancella documento
     * 
     * @param String $uuid UUID documento da cancellare
     * @param String $codEnte Codice Ente (non usato in alfresco)
     * 
     * @return true/false 
     */
    public function deleteDocumentByUUID($uuid, $codEnte = null) {
        $data = array(
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
     * @return true/false su getResult: binary Contenuto 
     */
    public function contentByUUID($uuid, $codEnte = null) {
        $data = array(
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
     * PHP_DATANAME puo' contenere le seguenti combinazioni:
     * - EXT.chiave: prende il valore da  $dizionario['EXT'][chiave]. Sono i valori aggiuntivi, esterni al record principale
     * - MAIN.chiave: prende il valore da $dizionario['MAIN'][chiave]. E' il record principale
     * - #fn#classe.metodo: prende il valore dalla funzione classe->metodo();
     * - EXT.chiave+#fn#classe.metodo: Con il + si possono concatenare varie casistiche per creare una stringa composta.
     * 
     * 
     * @param array $listBgdMetDoc lista di record bgd_metdoc che contengono i metadati e le regole per reperirli
     * @param array $dizionario array composto dalla chiave MAIN in cui c'è il main record e dalla chiave EXT in cui ci 
     *                          sono i valori esterni

     * @return array metadati chiave/valore
     */
    public function getMetadata($listBgdMetDoc, $dizionario) {
        $toReturn = array();

        foreach ($listBgdMetDoc as $metadato) {
            try {
                $regola = $metadato['PHP_DATANAME'];

                // splitto il + per vedere se è una concatenazione di regole
                $regolaSplit = explode('+', $regola);

                if (sizeof($regolaSplit) == 1) {
                    // regola semplice
                    $toReturn[$metadato['CHIAVE']] = $this->getMetadataValue($regolaSplit[0], $dizionario);
                } else {
                    // regole concatenate, le ciclo e le eseguo tutte
                    $dato = '';
                    foreach ($regolaSplit as $value) {
                        $res = $this->getMetadataValue($value, $dizionario);
                        $dato .= $res != null ? $res : '';
                    }
                    $toReturn[$metadato['CHIAVE']] = $dato;
                }
            } catch (Exception $exc) {
                // TODO log
            }
        }

        return $toReturn;
    }

    private function getMetadataValue($regola, $dizionario) {
        if (preg_match('#^' . self::DICT_EXT_KEY . '.#i', $regola) === 1) {
            // se inizia per ext devo prendere il valore dall'array $dizionario nella chiave EXT
            return $this->getMetadataValueDizionario($regola, $dizionario, self::DICT_EXT_KEY);
        } else if (preg_match('#^' . self::DICT_MAIN_KEY . '.#i', $regola) === 1) {
            // se inizia per main devo prendere il valore dall'array $dizionario nella chiave MAIN
            return $this->getMetadataValueDizionario($regola, $dizionario, self::DICT_MAIN_KEY);
        } else if (preg_match('/^' . self::DICT_FN_KEY . '/', $regola) === 1) {
            // se inizia per #FN# significa che devo eseguire la funzione oggetto.metodo
            // prendo la stringa dopo #fn# e poi separo il nome classe dal nome metodo
            $fn = explode(".", array_pop(explode(self::DICT_FN_KEY, $regola)));
            $classe = $fn[0];
            $metodo = $fn[1];

            // faccio il require
            $appRoute = App::getPath('appRoute.' . substr($classe, 0, 3));
            $modelSrc = App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $classe . '.php';

            if (file_exists($modelSrc)) {
                require_once $modelSrc;

                // eseguo il metodo
                $valore = $classe::$metodo();

                return $valore;
            }
        } else {
            // caso di tabella relazionata (es. BTA_TICOM.DES_TIPCOM)
            // splitto il . per prendere la chiave sull'array dizionario(nome tabella) che poi conterrà un array a sua volta con il nome del campo come chiave
            $regolaSplit = explode('.', $regola);
            if (sizeof($regolaSplit) > 1) {
                $valoreRec = $dizionario;
                // scorro tutte le chiavi e prendo di volta in volta il valore nell'array che ne esce, fino ad arrivare alla 'foglia'
                foreach ($regolaSplit as $value) {
                    $valoreRec = $valoreRec[$value];
                }
                return $valoreRec;
            }
        }

        return null;
    }

    private function getMetadataValueDizionario($regola, $dizionario, $separator) {
        // prendo la stringa dopo $separator.
        $chiave = array_pop(explode($separator . '.', $regola));
        // trasformo tutte le chiavi dell'array in lower case per evitare problemi 
        $dict = array_change_key_case($dizionario[$separator]);
        // prendo il valore dal dizionario
        $valore = $dict[strtolower($chiave)];

        return $valore;
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
        $this->alfcityHost = $parameters['alfcityHost'];
        $this->alfcityPath = $parameters['alfcityPath'];
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

    private function executeQuery($callType, $docType, $codEnte, $codAoo, $aspects, $props, $fullText = null, $page = 0, $blockSize = 0) {
        $connectionData = $this->getConnectionData();
        
        $data = array(
            'DOC_TYPE' => $docType,
            'CODENTE' => $connectionData['ENTE'] ?: $codEnte,
            'CODAOO' => $connectionData['AOO'] ?: $codAoo,
            'PAGE' => $page,
            'BLOCKSIZE' => $blockSize,
            'DATA' => $this->createXmlForQuery($aspects, $props, $fullText),
            'CALL_TYPE' => $callType
        );
        if (!$this->handleResult($this->call($data))) {
            return false;
        }

        // Elabora risultato
        try {
            $results = array();
            $decoded = base64_decode($this->getResult());
            $xmlObj = new itaXML;
            if ($xmlObj->setXmlFromString($decoded)) {
                $results = $xmlObj->getArray();
            }
        } catch (Exception $ex) {
            $this->setErrCode(-1);
            $this->setErrMessage($ex->getMessage());
            return false;
        }

        $this->setResult($results);

        return true;
    }

    private function executeCount($callType, $docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        $connectionData = $this->getConnectionData();
        
        $data = array(
            'DOC_TYPE' => $docType,
            'CODENTE' => $connectionData['ENTE'] ?: $codEnte,
            'CODAOO' => $connectionData['AOO'] ?: $codAoo,
            'DATA' => $this->createXmlForQuery($aspects, $props, $fullText),
            'CALL_TYPE' => $callType
        );
        return $this->handleResult($this->call($data));
    }
    
    private function getConnectionData(){
        if(empty($this->connectionData)){
            $devLib = new devLib();
            $string = $devLib->getEnv_config('ALFCITY', 'codice', 'ALFRESCO_BASEPLACE', false);
            preg_match('/^\/app:company_home\/cm:([A-Z0-9_]*)\/cm:ENTE_([0-9]*)\/cm:AOO_([A-Z0-9_]*)\/$/i', $string['CONFIG'], $matches);
            
            $this->connectionData = array(
                'APP'=>$matches[1],
                'ENTE'=>$matches[2],
                'AOO'=>$matches[3]
            );
        }
        return $this->connectionData;
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
            if ($value !== null && $value != '') {
                $propertyChild = $propertiesChild->addChild('Property');
                $propertyChild->addChild("Name", $key);
                $value = $this->cleanSpecialChar($value);

                if ($this->utf8_encode) {
                    $propertyChild->addChild("Value", utf8_encode($value));
                } else {
                    $propertyChild->addChild("Value", $value);
                }
            }
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
        // add aspects
        $aspectsChild = $xmlData->addChild('Aspects');
        foreach ($aspects as $key => $value) {
            $aspectChild = $aspectsChild->addChild('Aspect');
            $aspectChild->addChild("Name", $key);
            $aspectChild->addChild("Value", $value);
        }
        // add Properties
        $propertiesChild = $xmlData->addChild('Properties');
        foreach ($props as $key => $value) {
            $propertyChild = $propertiesChild->addChild('Property');
            $propertyChild->addChild("Name", $key);
            if ($this->utf8_encode) {
                $propertyChild->addChild("Value", utf8_encode($value));
            } else {
                $propertyChild->addChild("Value", $value);
            }
        }
        // add fulltext
        $textChild = $xmlData->addChild('Text');
        if ($this->utf8_encode) {
            $textChild->addChild("Value", utf8_encode($fullText));
        } else {
            $textChild->addChild("Value", $fullText);
        }
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

    private function cleanSpecialChar($toClean) {
        $detectedEncoding = mb_detect_encoding($toClean, 'UTF-8, ISO-8859-1');
        $toClean = htmlspecialchars($toClean, ENT_COMPAT, $detectedEncoding);
        str_replace("|", "", $toClean);
        return utf8_encode($toClean);
    }

    public function getAlfcityHost() {
        return $this->alfcityHost;
    }

    public function getAlfcityPath() {
        return $this->alfcityPath;
    }

    public function setAlfcityHost($alfcityHost) {
        $this->alfcityHost = $alfcityHost;
    }

    public function setAlfcityPath($alfcityPath) {
        $this->alfcityPath = $alfcityPath;
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