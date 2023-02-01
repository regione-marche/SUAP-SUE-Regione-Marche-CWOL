<?php

interface itaPHPSignatureProvider {

    /**
     * Firma esternamente un documento da pdf a p7m 
     * @param mixed $sourceDocument Documento\Array di documenti da firmare può essere un file oppure un base64 del file 
     * @param array $returnData array di ritorno della callback 
     * 'returnForm' => 'x',
      'returnId' => 'y',
      'returnEvent' => 'z')
     * @param array $params chiave valore da passare come parametro alla chiamata specifica
     */
    public function signature($sourceDocument, $returnData, $paramsIn);

    /**
     * Verifica il documento firmato 
     * @param mixed $signedDocument Documento p7m può essere un file oppure un base64 del file
     * @param mixed $signedDocument Documento originale  può essere un file oppure un base64 del file $signedDocument (facolativo)
     * @return file ritorna il documento originale 
     */
    public function verifySignature($signedDocument, $sourceDocument, $returnData);

    /**
     * Ritorna le informazioni dei firmati del documento
     * @param mixed $signedDocument Documento firmato può essere un file oppure un base64 del file $signedDocument
     * @param mixed $sourceDocument Documento originale preFirma può essere un file oppure un base64 del file
     * 'returnForm' => 'x',
      'returnId' => 'y',
      'returnEvent' => 'z')
     */
    public function signersInfo($signedDocument, $sourceDocument, $returnData);

    /*     * passaggio parametri all'oggetto firma
     * 
     */

    public function setParameters($parameters = array());

    /**
     * Indica se è consentita la firma multipla
     * @return boolean true/false 
     */
    public function isMultipleSignatureAllowed();
}

?>