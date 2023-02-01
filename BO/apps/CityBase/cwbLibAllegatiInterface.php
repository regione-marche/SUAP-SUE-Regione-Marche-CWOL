<?php

interface cwbLibAllegatiInterface {

    /**
     * Effettua il caricamento dell'allegato singolo
     * @param <array> $chiaveTestata Sono le chiavi necessarie ad caricare la lib
     * @param <int> $riga progressivo da caricare 
     * @param <array> $datiAggiuntivi dati specifici per caricare coerentemente le informazioni su afresco 
     * @param <bool> $startedTransaction  se true non effettua la commit\Rollback delle transazioni per la connessione corrente 
     * @param <bool> $modVis se true viene utilizzato in visualizzazione, quindi non effettua lo spostamento in Alfresco
     * @return <array> arrayModelAllegato
     */
    public function caricaAllegato($chiaveTestata, $riga, $datiAggiuntivi = array(), $startedTransaction = false, $modVis = false);

    /**
     * Effettua il caricamento degli allegati
     * @param <array> $chiaveTestata Sono le chiavi necessarie ad caricare la lib
     * @param <bool> $startedTransaction  se true non effettua la commit\Rollback delle transazioni per la connessione corrente  
     * @return <array> arrayModelAllegati
     */
    public function caricaAllegati($chiave = array());

    /**
     * Effettua il download del documento
     * @param <array> $rowModel modello documento
     * @return <uri> uri oneshot del file per effettuare il download
     */
    public function scarica($rowModel);

    /**
     * Effettua apertura del documento tramite il viewer. in caso di estensione non gestibile fa il download 
     * @param <array> $rowModel modello documento
     */
    public function apri($rowModel);

    /**
     * Effettua il salvataggio del modello
     * @param <array> $rowModel modello documento
     * @param <array> $datiAggiuntivi dati specifici per caricare coerentemente le informazioni su afresco 
     * @param <bool> $startedTransaction se true non effettua la commit\Rollback delle transazioni per la connessione corrente  
     * @return <bool> 
     */
    public function salvaAllegato($rowModel, $datiAggiuntivi = array(), $startedTransaction = false);

    /**
     * Effettua la cancellazione del modello e relativa eliminazione su alfresco 
     * @param <array> $rowModel modello documento
     * @param <bool> $startedTransaction se true non effettua la commit\Rollback delle transazioni per la connessione corrente  
     * @return <bool> 
     */
    public function eliminaAllegato($rowModel = array(), $startedTransaction = false);

    /**
     * Carica  i dati dalla tabella 'BTA_NTNOTE'
     * @param <string> $chiave filtro personalizzato per libreria
     */
    public function caricaNaturaNote($chiave);

    /*
     * Nome della chiave natura note
     */

    public function getChiaveNaturaNote();

    /**
     * Dati personalizzati da passare ad il documentale
     * @param <array> $datiAggiuntivi
     * @param <arra> $rowModel modello
     */
    public function getConfigDocumentale($datiAggiuntivi, $rowModel);
    /**
     * Ritorna il nome del componente custom per gestire intestazione
     */
    public function getNomeComponenteHeaderCustom();

    /**
     * Stampa i dati personalizzati sull'intestazione 
     * @param <array> $metadati personalizzati da stampare sull'intestazione della pagina
     * @param <string> $nameform nome del componente hedader da stampare
     */
    public function popolaHeaderCustom($metadati, $nameform);

    /**
     * Ritorna il nome del componente custom per gestire il corpo
     */
    public function getNomeComponenteBodyCustom();

    /**
     * Inizializza il componente personalizzato body
     * @param <string> $nameform nome del componente body
     */
    public function initBodyCustom($nameform);

    /**
     * Stampa i dati custom nel componente personalizzato di ogni lib 
     * @param <array> $metadati personalizzati da stampare
     * @param <string> $nameform nome del componente 
     */
    public function popolaBodyCustom($metadati, $nameform);

    /**
     * Trasforma un allegato un un allegatoModel(Array con campi specifici geTempleteRowModel) 
     * @param <array> $allegato specifico 
     * @return <array> rowModel
     */
    public function allegatoToAllegatoModel($allegato = array());

    /**
     * Trasforma un rowModel in un allegato specifico  
     * @param <array> $allegato allegato specifico 
     * @param <array> rowModel rowMmodello allegato 
     */
    public function allegatoModelToAllegato(&$allegato = array(), $rowModel = array());
    
    /**
     * Effettua il caricamento dei dati necessari per il popolamento dell'header
     * @param <array> $chiaveTestata Chiave testata
     * @return <array> Dati per popolamento testata
     */
    public function caricaDatiHeader($chiaveTestata);
    
    /**
     * Restituisce l'autorizzazione dell'utente
     * @param <array> $datiProvenienza
     * @return <string> '': nessuna autorizzazione, 'L': lettura, 'G': gestione, 'C': cancellazione
     */
    public function getAutorLevel($datiProvenienza = null);
}
