<?php

/**
 * Description of segIterDelibere
 *
 * @author michele
 */
class segIterDelibere {
    
    public function getKeyModelloProc(){
        return "ITER_DELIBERE";
    }

    public function getDescription(){
        return "Gestione Iter Delibere";
    }
    
    
    
    public function getFunzioniProc(){
        return array(
            'INSPROPOSTA'=>'Inserimento Nuova Proposta',
            'CANPROPOSTA'=>'Cancellazione Proposta',
            'MODPROPOSTA'=>'Modifica Dati Proposta',            
            'MODTESTO'=>'Revisione del Testo proposta',
            'INSPARERE'=>'Inserimento Nuovo Parere',            
            'CANPARERE'=>'Cancellazione Parere',
            'MODPARERE'=>'Modifica dati parere',
            'ESITOSI'=>'Espressione esito positivo',
            'ESITONO'=>'Espressione Esito negativo',
            'ESITOREVISIONE'=>'Esito di rinvio al Responsabile per revisione',
            'ANNPROPOSTA'=>'Annullamento di una proposta'            
        );
    }
    
    public function getKeysModelloPasso(){
        return array(
            'INSPROPOSTA'=>'Inserimento Nuova Proposta',
            'REVISIONETESTO'=>'Revisione del Testo proposta',
            'RICHPARERE'=>'Inserimento Nuovo Parere',
            'RICHREVISIONE'=>'Richiesta Revisione',
            'INSORDINEGIORNO'=>'Inserimento nell\'ordine del giorno',
            'TRASFROMAZIONE'=>'Trasformazione in delibera', 
            'ANNPROPOSTA'=>'Inserimento Nuovo Parere'            
        );
    }
    
    
    public function parsePasso(){
        
    }
    
    
}

?>
