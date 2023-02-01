<?php

/**
 *
 * Interfaccia per le form che vengono usate all'interno di un wizard
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
interface wizardable {

    /**
     * metodo per gestire la validazione
     * 
     * @param array() $formData la $_POST con i dati da validare
     * @param String $msgError messaggi di errore
     * @param String $msgWarn messaggi di warn
     * @param array() $addictionalData dati aggiuntivi
     */
    public function validaWizardStep($formData, &$msgError, &$msgWarn, $addictionalData);

    /*
     * Metodo da usare per tornare il valore di variabili (es. valore grid presenti sulla pagina.)
     * All'indietro o all'avanti se usata la cache, viene pescato il valore dalla cache e risettato alle proprietà passate in modo
     * da poterle riutilizzare (es. per inizializzare la grid).
     * (dalla POST non si riesce a leggere il contenuto di una grid)
     * 
     * return array() Deve tornare un array() cosi fatto: records[var] = value; oppure null se non ci sono.
     *                la key dell'array deve essere il nome del setter della proprietà che si vuole settare, il value invece il valore contenuto.
     */

    public function setValueToSave();

    /*
     * getter e setter da implementare per capire quando è stata applicata la cache
     */

    public function getAppliedCache();

    public function setAppliedCache($appliedCache);

}

