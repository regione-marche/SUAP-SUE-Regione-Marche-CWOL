<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    03.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/ZTL/ztlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';

class praLibDatiWorkFlow {

    /**
     * Libreria di funzioni Generiche e Utility per Integrazione SUAP/FIERE
     */
    private $errMessage;
    private $errCode;
    private $pratica;
    public $passoCorrente;
    /*
     * Elenco dei passi da visualizzare nella griglia con le caratteristiche grafiche
     */
    public $passi;
    /*
     * Metati del diagramma
     */
    public $diagramma;
    /*
     * Tracking dello stato del workflow dei passi fatti e  delle risoos date e da dare
     * 
     */
    public $navigatore;
    /*
     * Stato corrente dei dizionari applicativi
     * 
     */
    public $dizionari;
    /*
     * Sfogo per dati non assimilabili ia nessuna categoria
     * 
     * 
     */
    public $extraInfo;
    
    
    function __construct($pratica) {

        $praLib = new praLib();
        $this->pratica = $pratica;
        $this->passi = $praLib->caricaPassiBO($this->pratica);
        $this->caricaPassoCorrente();
        $this->creaDizionari();
        //$this->creaDizionari($this->pratica, $passoCorrente['PROPAK']);
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }
    
    public function getDizionari() {
        return $this->dizionari;
    }

    public function setDizionari($dizionari) {
        $this->dizionari = $dizionari;
    }

    public function creaDizionari(){ //($pronum, $propak){
        $praLibVar = new praLibVariabili();
        $praLibVar->setCodicePratica($this->pratica);  // $pronum
        $praLibVar->setChiavePasso($this->passoCorrente['PROPAK']);   // $propak

        $this->dizionari = $praLibVar->getVariabiliPratica();
    }
    
    
    function getPassi() {
        return $this->passi;
    }

    function setPassi($passi) {
        $this->passi = $passi;
    }

    private function caricaPassoCorrente(){
        $praLib = new praLib();
        // Scorrere i passi caricati
        //$this->passoCorrente = $this->passi[0];
        
        // Cerca ultimo record tabella PROPASFATTI 
        $sql = "SELECT * FROM PROPASFATTI "
                . " WHERE PROPASFATTI.PRONUM = '" . $this->pratica . "' "
                . " ORDER BY ROW_ID DESC ";

        $propasFatti_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);

        if (!$propasFatti_rec) {
            $this->setPassoCorrente($this->passi[0]['PROPAK']);
        }
        else {
            $this->setPassoCorrente($propasFatti_rec['PROSPA']);
            
        }
    }
    
    public function getPassoCorrente() {
        return $this->passoCorrente;
    }

    public function setPassoCorrente($propakPasso) {
        $praLib = new praLib();
        
        //Si trova record di PROPAS con PROPAK = $propakPasso
        //Cerco record di PROPAS da aggiornare
        $sql = "SELECT * FROM PROPAS "
                . " WHERE PROPAS.PROPAK = '" . $propakPasso . "' ";

        $propas_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);

        if (!$propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Non trovato record di PROPAS del passo corrente');

            return $propakNew;
        }
        
        $this->passoCorrente = $propas_rec;
        
    }

        
    public function setPassoSuccessivo() {
        $praLibElab = new praLibElaborazioneDati();
        $propakNew = $praLibElab->getPassoDestinazione($this->getPassoCorrente(), $this->getDizionari());
        
        $this->setPassoCorrente($propakNew);
    }

    public function setPassoPrecedente() {
        /*
         * TODO
         * ATTENZIONE - Tenere conto storicità basata su ROWID (vedi praLibPasso->aggiornaPassiFatti
         */
    }

    public function setPrimoPasso() {
        /*
         * TODO
         * ATTENZIONE - Tenere conto storicità basata su ROWID (vedi praLibPasso->aggiornaPassiFatti
         */
    }
    
    public function setUltimoPasso() {
        /*
         * TODO
         * ATTENZIONE - Tenere conto storicità basata su ROWID (vedi praLibPasso->aggiornaPassiFatti
         */
    }

    public function setPrimoPassoDaFare() {
        /*
         * TODO
         */
    }
    
    
}
