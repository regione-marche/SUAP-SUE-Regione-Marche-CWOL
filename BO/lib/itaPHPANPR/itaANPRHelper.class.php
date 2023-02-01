<?php

/**
 *
 * Classe di helper per ANPR
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     Massimo Biagioli
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    29.05.2018
 * @link
 * @see
 * @since
 * */
class itaANPRHelper {
    
    /**
     * Firma idPostazione
     * @param string $idPostazione Identificativo di postazione non firmato
     * @param array $certPostazione Array che contiene la coppia di certificati relativi alla postazione     
     * @param string $certPostazionePwd Password chiave privata del certificato di postazione
     * @return Identificativo di postazione firmato
     */
    public static function firmaIdPostazione($idPostazione, $certPostazione, $certPostazionePwd) {                                
        $unsignedPath = itaLib::createAppsTempPath("anpr") . DIRECTORY_SEPARATOR . 'idPostazione-unsigned-' . $idPostazione;
        $signedPath = itaLib::createAppsTempPath("anpr") . DIRECTORY_SEPARATOR . 'idPostazione-signed-' . $idPostazione;
        if (file_exists($signedPath)) {
            $content = file_get_contents($signedPath);
            return self::extractSignedDataFromContent($content);
        }        
        $fp = fopen($unsignedPath, "w");
        fwrite($fp, $idPostazione);
        fclose($fp);
        
        $worked = openssl_pkcs7_sign($unsignedPath, 
                $signedPath, 
                $certPostazione['cert'], 
                array($certPostazione['pkey'], $certPostazionePwd),
                null,
                PKCS7_BINARY);
        if (!$worked) {            
            return null;
        }        
        $content = file_get_contents($signedPath);    
        return self::extractSignedDataFromContent($content);        
    }
    
    private static function extractSignedDataFromContent($content) {        
        $parts  = preg_split("#\n\s*\n#Uis", $content);
        if (count($parts) > 1) {            
            return $parts[1];                        
        }
        return $content;
    }
    
    /**
     * Elabora p12
     * @param string $pathP12 Path del file .p12
     * @param string $passP12 Password del file .p12
     * @return array
     *      - cert => Certificato pubblico (PEM)
     *      - pkey => Chiave privata (PEM)
     */
    public static function processP12($pathP12, $passP12) {
        $results = array();
        $worked = openssl_pkcs12_read(file_get_contents($pathP12), $results, $passP12);
        if (!$worked) {
            $results = array(
                'cert' => null,
                'pkey' => null
            );
        } 
        return $results;
    }
    
}
