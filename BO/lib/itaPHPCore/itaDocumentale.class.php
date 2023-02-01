<?php

//require_once(ITA_BASE_PATH . '/lib/itaPHPCore/itaAlfcity.class.php');
//require_once(ITA_BASE_PATH . '/lib/itaPHPCore/itaDocercity.class.php');
require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

define('ALFCITY_TYPE', 'ALFCITY');
define('DOCERCITY_TYPE', 'DOCERCITY');

/**
 *
 * facade per gestione documentale, passando il tipo di documentale usato istanzia l'oggetto giusto.
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaDocumentale
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    23.02.2016
 * @link
 * @see
 * 
 */
class itaDocumentale {

    private $documentaleObj;

    public function __construct($type = ALFCITY_TYPE, $parameters = array()) {
        // in base al parametro type (default alfcity) decido quale documentale istanziare
        switch($type){
            case DOCERCITY_TYPE:
                require_once(ITA_BASE_PATH . '/lib/itaPHPCore/itaDocercity.class.php');
                $this->documentaleObj = itaDocercity::getDocercityClient($parameters);
                break;
            default:
                require_once(ITA_BASE_PATH . '/lib/itaPHPCore/itaAlfcity.class.php');
                $this->documentaleObj = itaAlfcity::getAlfcityClient($parameters);
                break;
        }
    }

    /**
     * Restituisce versione della lib usata
     * @return array Esito
     */
    public function version() {
        return $this->documentaleObj->version();
    }

    /**
     * Inserimento documento
     * 
     * @param String $docType Tipo di documento
     * @param String $place Posizione
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     * @param String $content Documento da inserire
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare in docer (key: nome metadato, value: valore) 
     * @return String UUID del documento inserito in caso di esito positivo, altrimenti ''
     * 
     * @return true/false su getResult : String UUID del documento inserito in caso di esito positivo
     * 
     */
    public function insertDocument($docType, $place, $fileName, $mimeType, $content, $aspects, $props, $codEnte = null) {
        return $this->documentaleObj->insertDocument($docType, $place, $fileName, $mimeType, $content, $aspects, $props, $codEnte);
    }

    /**
     * Conta elementi in funzione dei criteri di ricerca impostati 
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare in docer (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa per la ricerca full text
     * 
     * @return true/false su getResult int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQuery($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return $this->documentaleObj->countQuery($docType, $codEnte, $codAoo, $aspects, $props, $fullText);
    }

    /**
     * Conta elementi in funzione dei criteri di ricerca impostati 
     * 
     * @param String $docType Tipo di documento
     * @param String $codEnte Codice Ente
     * @param String $codAoo Codice AOO
     * @param array $aspects Aspetti che deve avere il documento
     * @param array $props Deve contenere i metadati di ricerca da passare in docer (key: nome metadato, value: valore) 
     * @param array $fullText Se valorizzato, indica la stringa di ricerca per tutti i metadati e full text
     * 
     * @return true/false su getResult: int Numero elementi che soddisfano i criteri di ricerca
     */
    public function countQueryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText = '') {
        return $this->documentaleObj->countQueryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText);
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
     * @param Integer $page Pagina inizio ricerca 
     * @param Integer $blockSize Dimensione blocco 
     * 
     * @return true/false su getResult: array Risultati della ricerca
     */
    public function query($docType, $codEnte, $codAoo, $aspects, $props, $fullText = null, $page = 0, $blockSize = 0) {
        return $this->documentaleObj->query($docType, $codEnte, $codAoo, $aspects, $props, $fullText, $page, $blockSize);
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
        return $this->documentaleObj->queryAll($docType, $codEnte, $codAoo, $aspects, $props, $fullText, $page, $blockSize);
    }

    /**
     * Ricerca per UUID
     * 
     * @param String $uuid Tipo di documento
     * 
     * @return true/false su getResult array metadati
     */
    public function queryByUUID($uuid) {
        return $this->documentaleObj->queryByUUID($uuid);
    }

    /**
     * Restituisce posizione logica del documento su Alfresco, dato in ingresso il suo UUID
     * 
     * @param String $uuid Tipo di documento
     * 
     * @return true/false su getResult String posizione del documento
     */
    public function placeByUUID($uuid) {
        return $this->documentaleObj->placeByUUID($uuid);
    }

    /**
     * Aggiorna metadati documento
     * 
     * @param String $uuid UUID documento
     * @param String $docType Tipo di documento
     * @param array $aspects Deve contenere gli aspetti attivi (key: nome aspetto, value:1,0)
     * @param array $props Deve contenere i metadati da passare ad alfresco (key: nome metadato, value: valore) 
     * @param String $codEnte Codice ente
     * 
     * @return true/false 
     */
    public function updateDocumentMetadata($uuid, $docType, $aspects, $props, $codEnte = null) {
        return $this->documentaleObj->updateDocumentMetadata($uuid, $docType, $aspects, $props, $codEnte);
    }

    /**
     * 
     * Aggiorna contenuto documento
     * @deprecated
     * 
     * @param String $uuid UUID documento
     * @param String $content Contenuto documento
     * @param String $fileName Nome del documento completo di estensione
     * @param String $mimeType MimeType del documento
     * 
     * @return true/false 
     */
    public function updateDocumentContent($uuid, $content, $fileName, $mimeType) {
        return $this->documentaleObj->updateDocumentContent($uuid, $content, $fileName, $mimeType);
    }

    /**
     * Cancella documento
     * 
     * @param String $codEnte Codice Ente docer
     * @param String $uuid UUID documento da cancellare
     * 
     * @return true/false 
     */
    public function deleteDocumentByUUID($uuid, $codEnte = null) {
        return $this->documentaleObj->deleteDocumentByUUID($uuid, $codEnte);
    }

    /**
     * Legge documento
     * 
     * @param String $codEnte Codice Ente (non usato in alfresco)
     * @param String $uuid UUID documento da cancellare
     * 
     * @return true/false su getResult: binary Contenuto 
     */
    public function contentByUUID($uuid, $codEnte = null) {
        return $this->documentaleObj->contentByUUID($uuid, $codEnte);
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
        return $this->documentaleObj->getMetadata($listBgdMetDoc, $dizionario);
    }

    /**
     * Contiene lo status http
     * 
     */
    public function getHttpStatus() {
        return $this->documentaleObj->getHttpStatus();
    }

    /**
     * In caso di errori contiene il codice errore
     * 
     */
    public function getErrCode() {
        return $this->documentaleObj->getErrCode();
    }

    /**
     * Contiene il messaggio di errore
     * 
     */
    public function getErrMessage() {
        return $this->documentaleObj->getErrMessage();
    }

    /**
     * Contiene il risultato di risposta
     *
     */
    public function getResult() {
        return $this->documentaleObj->getResult();
    }

    /**
     * Se i dati devono essere codificati in utf-8
     *
     */
    function getUtf8_encode() {
        return $this->documentaleObj->getUtf8_encode();
    }

    function setUtf8_encode($utf8_encode) {
        $this->documentaleObj->setUtf8_encode($utf8_encode);
    }

    function getUtf8_decode() {
        return $this->documentaleObj->getUtf8_decode();
    }

    function setUtf8_decode($utf8_decode) {
        $this->documentaleObj->setUtf8_decode($utf8_decode);
    }

}

?>
