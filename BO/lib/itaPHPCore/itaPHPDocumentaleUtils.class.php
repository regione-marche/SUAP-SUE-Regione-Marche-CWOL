<?php

require_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
require_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

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
class itaPHPDocumentaleUtils {

    const ALFRESCO_TYPE =  'Alfresco';


    private $documentaleUtils;

    public function __construct($type) {
        $this->documentaleUtils = null;

        if(self::ALFRESCO_TYPE === $type){
            require_once ITA_LIB_PATH . '/itaPHPAlfcity/itaDocumentaleAlfrescoUtils.class.php';
            $this->documentaleUtils = new itaDocumentaleAlfrescoUtils();
        }      
    }

    /*
     * inserisce un flusso con i suoi metadati nel documentale
     */

    public function inserisciFlussoCP($fileName, $content) {
        return $this->documentaleUtils->inserisciFlussoCP($fileName, $content);
    }

    /*
     * inserisce un annesso con i suoi metadati nel documentale
     */

    public function inserisciAnnessoCP($fileName, $content) {
        return $this->documentaleUtils->inserisciAnnessoCP($fileName, $content);
    }

    /*
     * inserisce un documento protocollato con i suoi metadati nel documentale
     */

    public function inserisciProtocollo($fileName, $content) {
        return $this->documentaleUtils->inserisciProtocollo($fileName, $content);
    }

    /*
     * Spacchetta tutte le fatture di un flusso
     */

    public function spacchettaFlusso($uuidFlusso) {
        return $this->documentaleUtils->spacchettaFlusso($uuidFlusso);
    }

    /*
     * Spacchetta tutte le fatture di tutti i flussi ancora da spacchettare
     */

    public function spacchettaTuttiIFlussi() {
        return $this->documentaleUtils->spacchettaTuttiIFlussi();
    }

    /*
     * accetta una fattura
     */

    public function accettaFattura($uuidFattura) {
        return $this->documentaleUtils->accettaFattura($uuidFattura);
    }

    /*
     * rifiuta una fattura
     */

    public function rifiutaFattura($uuidFattura, $motivo) {
        return $this->documentaleUtils->rifiutaFattura($uuidFattura, $motivo);
    }

    /*
     * Gestisci l'arrivo di una decorrenza termini
     * 
     * @param String $uuidFattura id della fattura da accettare
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaMetadatiRicezioneDT($uuidFattura) {
        return $this->documentaleUtils->aggiornaMetadatiRicezioneDT($uuidFattura);
    }

    /*
     * Gestisci l'aggiornamento dei metadati dopo l'invio dell'accettazione/rifiuto di una fattura
     * 
     * @param String $uuidFattura id della fattura da accettare
     * @param boolean $accettazione true se è stata inviata un'accettazione, false per il rifiuto
     * 
     * return true/false su getResult il risultato di ritorno
     */

    public function aggiornaMetadatiInvioEsitoFattura($uuidFattura, $accettazione) {
        return $this->documentaleUtils->aggiornaMetadatiInvioEsitoFattura($uuidFattura, $accettazione);
    }

    /*
     * Messaggio di errore
     */

    function getErrMessage() {
        return $this->documentaleUtils->getErrMessage();
    }

    /*
     * Risultato delle chiamate in caso di successo
     */
    function getResult() {
        return $this->documentaleUtils->getResult();
    }
    
    /*
     * Recupera le informazioni del dizionario
     */
    function getDizionario() {
        return $this->documentaleUtils->getDizionario();
    }
    
    /*
     * Imposta il dizionario
     */
    function setDizionario($dizionario) {
        return $this->documentaleUtils->setDizionario($dizionario);
    }

}

?>