<?php

/**
 *
 * Interfaccia per i client di protocollazione
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Protocollo
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 */
interface proWsClientInterface {

    public function getClientType();

    public function setClientConfig($clientObj);
    
    public function setKeyConfigParams($key);
    
    public function setArrConfigParams($clientParams);

    public function inserisciProtocollazionePartenza($elementi);

    public function inserisciProtocollazioneArrivo($elementi);

    public function inserisciDocumentoInterno($elementi);

    public function leggiProtocollazione($params);
    
    public function leggiDocumentoInterno($params);
}

?>
