<?php

    include_once 'utiFirmaValidator.class.php';

    class utiFirmaIdFlussoValidator implements utiFirmaValidator {
        
        public function validate($pathFileNotSigned, $pathFileSigned) {

            $flussoXml = file_get_contents($pathFileNotSigned);
            if($flussoXml) { 
                $xml = new SimpleXMLElement($flussoXml); 
                $node = $xml->xpath('//identificativo_flusso'); 
                $xmlElement = $node[0]; 
                $idFlusso = $xmlElement->__toString(); 

                $flussoFirmatoXml = file_get_contents($pathFileSigned); 
                if($flussoFirmatoXml) { 
                    $xml = new SimpleXMLElement($flussoFirmatoXml); 
                    $node = $xml->xpath('//identificativo_flusso'); 
                    $xmlElement = $node[0]; 
                    $idFlussoFirmato = $xmlElement->__toString(); 

                    if($idFlusso == $idFlussoFirmato) { 
                        $result['STATO'] = true; 
                    } else { 
                        $result['STATO'] = false; 
                        $result['MESSAGGIO'] = "L'identificativo flusso non corrisponde a quello del flusso originale!"; 
                    } 

                } else { 
    +
                    $result['STATO'] = false; 
                    $result['MESSAGGIO'] = "Errore nel caricamento del xml del flusso firmato."; 
                } 
            } else { 
                $result['STATO'] = false; 
                $result['MESSAGGIO'] = "Errore nel caricamento del xml del flusso."; 
            } 

            return $result; 
            
        }
        
    }

?>