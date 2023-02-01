<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_CITYWARE.class.php';


/**
 *
 * Utility DB Cityware (Modulo BGE)
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Stefano Guidetti
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbLibTestiMain extends cwbLibDB_CITYWARE {
    
    public $codTesto;
    protected $omnisClient;
    
    function __construct() {
        $this->omnisClient = new itaOmnisClient();
        
    }
    
    public function Carica_tipologia ($par_testoprova=0,&$par_lista_tipo) {
        // Carico i valori della combo (tipologia) con il parametro che mi viene passato dalla w
        $loc_sql .='';
        $loc_sql .= '  SELECT * FROM BGE_TESTIT  ';
        if ($par_testoprova) {
            $loc_sql .= '  WHERE TESTOPROV='.$par_testoprova.'  ';
        }
        $loc_sql .= '  ORDER BY TESTIT ';
        $par_lista_tipo = ItaDB::DBSQLSelect($this->getCitywareDB(), $loc_sql, true);

        // Inserisco una riga vuota per permettere di non selezionare nessun valore
        //array_unshift($par_lista_tipo,array(''=>'Selezionare'));
    
        Return false;
    // ===============================================================================================
    // 
    // Author:     Guidetti Stefano / PAL Informatica S.r.l.                                                          Date:   0/1/2002
    // Author:     Michele Soparanzetti / APRA Progetti S.r.l.                                                     Date:   03/12/2002
    // modificato il 24-10-2008 da Silvia Rivi   tolto il Begin Statement e messo il Text
    // ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
    }
    
    public function esistenza_lista($nomeLista) {
        $methodArgs[0]= $this->codTesto;
        $methodArgs[1]= $nomeLista;        
        $result= $this->omnisClient->callExecute('OBJ_BGE_PHP_TESTI', 'congruenzaListe', $methodArgs, 'CITYWARE', false);
        return $result['RESULT']['MESSAGE'] ;
    }
    
    public function setCdoTesto($codTesto) {
        $this->codTesto=$codTesto;
    }
    
    public function getCdoTesto() {
        return $this->codTesto;
    }
}


?>