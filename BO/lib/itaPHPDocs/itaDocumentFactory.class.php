<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaDocumentFactory
 *
 * @author michele
 */
class itaDocumentFactory {
    static public function getDocument($tipo) {
        switch (strtoupper($tipo)) {
            case 'HTML':
            case 'HTM':
            case 'MSWORDHTML':                
                $driverKey = 'MSWORDHTML';
                break;
            case 'XHTML':
                $driverKey = 'XHTML';                
                break;
            case 'DOCX':
                $driverKey = 'DOCX';                
                break;
            case 'XLS':
            case 'XLSX':
                $driverKey = 'XLSX';                
                break;
            case 'EISEXLSX':
                $driverKey = 'EISEXLSX';
        }
        $driver_doc = dirname(__FILE__) . "/itaDocument.$driverKey.class.php";
        if (!file_exists($driver_doc)) {
            throw new Exception("Driver documento $driverKey non trovato");
        }
        include_once($driver_doc);
        $classe = 'itaDocument' . $driverKey;
        return new $classe();
    }

}

?>
