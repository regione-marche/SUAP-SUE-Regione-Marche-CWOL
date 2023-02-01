<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';

//include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
//include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCustomClass.class.php';


class praCompPassoGest extends itaModel {

    protected $praLib;
    protected $praLibHtml;
    protected $PRAM_DB;
    protected $errCode;
    protected $errMessage;
    protected $gesnum;
    protected $propak;
    protected $readOnly = false;

    
    function __construct() {
        parent::__construct();

        // Apro il DB
        try {
            /*
             * Istanza risorse oggetti esterni
             */
            $this->praLib = new praLib();
//            /*
//             * Rilettura delle varuabili in session
//             */
//            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
//            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        
    }

    protected function postInstance() {
        parent::postInstance();

        $this->praLib = new praLib();
        $this->praLibHtml = new praLibHtml();
        $this->praLibHtml->setDefaultFullWidth(true);
        $this->PRAM_DB = ItaDB::DBOpen('PRAM');

        $this->gesnum = App::$utente->getKey($this->nameForm . '_gesnum');
        $this->propak = App::$utente->getKey($this->nameForm . '_propak');
        $this->readOnly = App::$utente->getKey($this->nameForm . '_readOnly');

    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_gesnum', $this->gesnum);
            App::$utente->setKey($this->nameForm . '_propak', $this->propak);
            App::$utente->setKey($this->nameForm . '_readOnly', $this->readOnly);
        }
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

        
    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function setPropak($propak) {
        $this->propak = $propak;
        // Out::msgInfo("Valore aasegnato ", $this->propak);
    }
    
    public function getPropak() {
        return $this->propak;
        // Out::msgInfo("Valore aasegnato ", $this->propak);
    }
    
    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':
                //TODO: Da togliere - Si usa per lo sviluppo di prova
                //$this->setPropak('2018000040153996601332');
                $this->inizializza();

                break;

            case 'onClick':

                break;

            case 'onBlur':
            case 'onChange':

                break;


        }
    }

    protected function getDatiPasso(){
        return $this->praLib->GetPropas($this->propak);
        
        
    }
    
    public function close() {
        parent::close();

        App::$utente->removeKey($this->nameForm . '_gesnum');
        App::$utente->removeKey($this->nameForm . '_propak');
        App::$utente->removeKey($this->nameForm . '_readOnly');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    protected function inizializza(){
        
    }

    public function preCondizioni(){
       
        return true;
    }

    public function openGestione(){
       
        return true;
    }

    public function aggiornaDati(){
       
        return true;
    }
    
    public function vaiAvanti() {
        
        Out::msgInfo("vaiAvanti - $_POST", print_r($_POST,true));
        
    }

    public function navigazione($datiWf, $evento = 'avanti') {
        $propakNew = '';
        //Out::msgInfo("datiwf", print_r($datiWf,true));
        
        // Trovare il passo attuale e vedere che tipo è (Domanda Semplice ; Domanda Multipla ; Raccolta Dati ecc..)
        $passoCorrente = $datiWf->getPassoCorrente();

        //Cerco record di PROPAS da aggiornare
//        $sql = "SELECT * FROM PROPAS "
//                . " WHERE PROPAS.PROPAK = '" . $passoCorrente['PROPAK'] . "' ";
//
//        $propas_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        $propas_rec = $this->praLib->GetPropas($passoCorrente['PROPAK'], 'propak');

        if (!$propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Non trovato record di PROPAS del passo corrente');

            return $propakNew;
        }

        
        if ($evento == 'avanti'){

            $praLibElab = new praLibElaborazioneDati();
            $propakNew = $praLibElab->getPassoDestinazione($propas_rec, $datiWf->getDizionari());
            
        }
        else if ($evento == 'indietro'){

            // Si cerca in PROPASFATTI il record che ha PROPASFATTI.PROSPA = $propas_rec['PROPAK']
            // e ci posizioniamo sul passo presente in PROPASFATTI.PROPAK
//            $sql1 = "SELECT * FROM PROPASFATTI "
//                    . " WHERE PROPASFATTI.PROSPA = '" . $passoCorrente['PROPAK'] . "' " 
//                    . " ORDER BY ROW_ID DESC ";
//
//            $propasFatti_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql1, false);
            
            $propasFatti_rec = $this->praLib->GetPropasFatti($passoCorrente['PROPAK']);
            if (!$propasFatti_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage('Non trovato record di PROPASFATTI precedente al passo corrente');

                return $propakNew;
            }
            
            
            $propakNew = $propasFatti_rec[PROPAK];            
            
        }
        return $propakNew;
        
    }
    
}

