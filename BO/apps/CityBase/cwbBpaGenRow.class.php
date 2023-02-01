<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';

/**
 *
 * Superclasse gestione form con singolo record
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbBpaGenRow extends cwbBpaGenModel {    
    
    protected function apriForm() {
        $this->preApriForm();
        $this->elenca();
        $this->postApriForm();
    }
    
    /**
     * Effettua il caricamento dei dati dal database
     * Presentazione dei dati direttamente nel dettaglio
     * Se il record esiste, va in modifica, altrimenti va in inserimento
     */
    protected function elenca() {        
        $this->preElenca();
        
        $this->loadCurrentRecord(null);
        if ($this->CURRENT_RECORD == null) {
            $this->nuovo();
        } else {
            $this->dettaglio(null);
        }
        
        $this->postElenca();
    }
    
    /* 
     * In questo caso di default non deve fare nulla, in quanto l'unico pulsante 
     * presente nella buttonbar è "Aggiorna"
     */
    protected function setVisControlli($divGestione, $divRisultato, $divRicerca, 
            $nuovo, $aggiungi, $aggiorna, $elenca, $cancella, $altraRicerca, $torna) {
               $this->setDetailView($divGestione);
        
        // controllo autorizzazioni
        $this->elaboraAutor();
    }
        
}

?>