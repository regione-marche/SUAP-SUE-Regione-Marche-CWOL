<?php

/**
 *
 * IMPORTAZIONE E SINCRONIZZAZIONE UTENTI CW
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Accessi
 * @author   
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    01.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR_UTENTI.class.php';

function accImportUtentiCW() {
    $accImportUtentiCW = new accImportUtentiCW();
    $accImportUtentiCW->parseEvent();
    return;
}

class accImportUtentiCW extends itaModel {

    public $nameForm = "accImportUtentiCW";

    function __construct() {
        parent::__construct();
        
        $this->accLib = new accLib();
        $this->libBorUtenti = new cwbLibDB_BOR_UTENTI();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                Out::msgInfo("Import Utenti", "W.I.P.");

                /*
                 * Estrazione indice utenti CW
                 * 
                 * MAPPATURA ARRAY A UN LIVELLO
                 * 
                 */


                $utentiCWIndex = $this->libBorUtenti->leggiIndiceUtenti();
                $utentiCWIndex = array_map(array($this,'colExtract'), $utentiCWIndex);
                
                //foreach ($result as $key => $utente) {
                //    $resultUte = $this->libBorUtenti->leggiUtenti($utente['CODUTE']);
                //}
                
                /*
                 * 
                 * ESTRAZIONE UTENTI CWOL
                 * 
                 * MAPPATURA ARRYA A UN LIVELLO
                 */

                $sqlUtentiCWOL = "SELECT UTELOG FROM UTENTI ORDER BY UTELOG";
                $utentiCWOLIndex = ItaDB::DBSQLSelect($this->accLib->getITW(), $sqlUtentiCWOL);
                $utentiCWOLIndex = array_map(array($this,'colExtractCWOL'), $utentiCWOLIndex);

                /*
                 * CONFRONTO DIFFERENZE
                 * 
                 */
                $utentiDaInserire = array_diff($utentiCWIndex,$utentiCWOLIndex);
                $UtentiDaAggiornare = array_diff($utentiCWOLIndex,$utentiDaInserire);

                /*
                 * INSERIMENTI
                 * 
                 */


                /*
                 * AGGIORNAMENTI
                 * 
                 * 
                 */

                break;
            case 'onBlur':
                break;
            case 'onClick':
                break;
        }
    }

    private function colExtract($array) {
        return $array['CODUTE'];
    }
    private function colExtractCWOL($array) {
        return $array['UTELOG'];
    }
}

?>
