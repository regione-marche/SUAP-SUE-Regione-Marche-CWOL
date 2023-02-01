<?php

include_once ITA_LIB_PATH . '/itaPHPDocs/itaDocumentFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

/**
 * Classe di utilità per variabili dei testi
 */
class cwbLibVarUtils {
    
    /**
     * Formatta data secondo il formato specificato (default = gg-mm-aa)
     * @param string $toFormat Data da formattare
     * @param string $newFormat Formato con cui formattare la data
     * @return string Data formattata
     */
    public static function formatDate($toFormat, $newFormat = 'd-m-Y') {
        $date = strtotime($toFormat);
        if($date){
            return date($newFormat, $date);
        }
        return '';
    }
    
    /**
     * Formatta float
     * @param mixed $n Numero da formattare
     * @param int $decimals Numero di decimali (default = 2)
     * @return string Float formattato
     */
    public static function formatFloat($n, $decimals = 2) {
        return number_format((float)$n, $decimals, ',', '.');
    }
       
    /**
     * @param string $documentiCod Codice documento DOC_DOCUMENTI     
     * @param array $baseDictionary (opzionale) Dizionario da utilizzare come base
     * @return itaDocumentDOCX
     */
    public static function getTestoInclude($documentiCod, $baseDictionary = array()) {
        $toReturn = array(
            'CODERR' => 0,
            'MSGERR' => ''
        );
        
        if (!$documentiCod) {
            $toReturn['CODERR'] = 1;
            $toReturn['MSGERR'] = 'Codice documento non valorizzato';
            return $toReturn;
        }        

        /* @var $docLib docLib */
        $docLib = new docLib();
        $documenti_rec = $docLib->getDocumenti($documentiCod);

        $docx_path = $docLib->setDirectory() . $documenti_rec['URI'];

        if (!$documenti_rec || !$documenti_rec['URI'] || !file_exists($docx_path)) {
            $toReturn['CODERR'] = 2;
            $toReturn['MSGERR'] = 'Documento non trovato';
            return $toReturn;
        }

        $dictionary = $baseDictionary;
        
        /* @var $doc itaDocumentDOCX */
        $doc = itaDocumentFactory::getDocument('docx');
        $doc->setDictionary($dictionary);
        $doc->loadContent($docx_path);

        if (!$doc->mergeDictionary()) {
            $toReturn['CODERR'] = 3;
            $toReturn['MSGERR'] = $doc->getMessage();
            return $toReturn;
        }        
        $toReturn['DOC'] = $doc;
        return $toReturn;
    }
    
}
