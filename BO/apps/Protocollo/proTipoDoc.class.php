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
class proTipoDoc {

    static $SISTEM_SUBJECT_CLASS_DOC = array(
        "EFAA" => array('CODICE' => "EFAA", 'DESCRIZIONE' => 'Fattura Elettronica in Arrivo', 'CLASSE' => 'FATTURAELETTRONICA')
        , "DGEN" => array('CODICE' => "DGEN", 'DESCRIZIONE' => 'Documento Generico')
        , "SUAP" => array('CODICE' => "SUAP", 'DESCRIZIONE' => 'Documento SUAP')
        , "DSUE" => array('CODICE' => "DSUE", 'DESCRIZIONE' => 'Documento SUE')
        , "EFAP" => array('CODICE' => "EFAP", 'DESCRIZIONE' => 'Fattura Elettronica in Partenza')
        , "SDIA" => array('CODICE' => "SDIA", 'DESCRIZIONE' => 'Notifica di interscambio in arrivo')
        , "SDIP" => array('CODICE' => "SDIP", 'DESCRIZIONE' => 'Notifica di interscambio in partenza')
        , "STRG" => array('CODICE' => "STRG", 'DESCRIZIONE' => 'Stampa del Registro Giornaliero')
        , "EFAS" => array('CODICE' => "EFAS", 'DESCRIZIONE' => 'Fattura Elettronica Spacchettata')
        , "PRAM" => array('CODICE' => "PRAM", 'DESCRIZIONE' => 'Pratica Amministrativa - Fascicolo Elettronico')
    );
    static $CONFIGURABLE_DOC_FROM_CODE = "1";
    static $CONFIGURABLE_DOC_TO_CODE = "1000";

    /**
     * 
     * @param type $proLib Libreria di lavoro 
     */
    static public function initSistemSubjectDoc($proLib) {
        foreach (self::$SISTEM_SUBJECT_CLASS_DOC as $Documento) {
            $Anaruo_rec = $proLib->GetAnaTipoDoc($Documento['CODICE']);
            if (!$Anaruo_rec) {
                $Anaruo_rec = $Documento;
                try {
                    $nrow = ItaDB::DBInsert($proLib->getPROTDB(), "ANATIPODOC", 'ROWID', $Anaruo_rec);
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

    /**
     * 
     * @param type $role
     * @return boolean
     */
    static public function isConfigurable($role) {
        if ($role >= self::$CONFIGURABLE_DOC_FROM_CODE && $role <= self::$CONFIGURABLE_DOC_TO_CODE) {
            return true;
        }
        return false;
    }

}

?>
