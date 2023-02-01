<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    27.02.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
class proTipoNode {

    static $SISTEM_SUBJECT_TIPONODE = array(
        "F" => array('TIPONODE' => "F", 'SEQORD' => '10')
        , "N" => array('TIPONODE' => "N", 'SEQORD' => '20')
        , "A" => array('TIPONODE' => "A", 'SEQORD' => '30')
        , "P" => array('TIPONODE' => "P", 'SEQORD' => '30')
        , "C" => array('TIPONODE' => "C", 'SEQORD' => '30')
        , "T" => array('TIPONODE' => "T", 'SEQORD' => '40')
    );

    /**
     * 
     * @param type $proLib Libreria di lavoro 
     */
    static public function initSistemSubjectNode($proLib) {
        foreach (self::$SISTEM_SUBJECT_TIPONODE as $Nodo) {
            $Anaordnode_rec = $proLib->GetAnaTipoNode($Nodo['TIPONODE']);
            if (!$Anaordnode_rec) {
                $Anaordnode_rec = $Nodo;
                try {
                    $nrow = ItaDB::DBInsert($proLib->getPROTDB(), "ANAORDNODE", 'ROWID', $Anaordnode_rec);
                    if ($nrow != 1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    return false;
                }
            }
        }
        return true;
    }

}

?>
