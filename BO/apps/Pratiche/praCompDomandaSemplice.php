<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Pratiche
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    23.10.2018
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Pratiche/praCompPassoGest.php';


function praCompDomandaSemplice() {
    $praCompDomandaSemplice = new praCompDomandaSemplice();
    $praCompDomandaSemplice->parseEvent();
    return;
}

class praCompDomandaSemplice extends praCompPassoGest {

    public $nameForm = 'praCompDomandaSemplice';
    private $tipoScelta = false;
    
    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->tipoScelta = App::$utente->getKey($this->nameForm . '_tipoScelta');
        
        
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tipoScelta', $this->tipoScelta);
        }
        
        
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':

                $this->inizializza();

                break;

            case 'onClick':


                break;

            case 'onChange':
                
                //Se preme su Check si setta la variabile $tipoScelta
                switch ($_POST['id']) {
                    case $this->nameForm . '_FlagSi':
                        $this->tipoScelta = 'Si';
                        break;

                    case $this->nameForm . '_FlagNo':
                        $this->tipoScelta = 'No';
                        break;
                }
                break;


        }
    }

    public function close() {
        parent::close();

        App::$utente->removeKey($this->nameForm . '_tipoScelta');

    }

    public function returnToParent($close = true) {
        parent::returnToParent();
        if ($close) {
            $this->close();
        }
    }

    public function openGestione(){
        
        //Out::msgInfo("inizializza", "Entrato in praCompDomandaSemplice->inizializza()");
        
        $propas_rec = $this->getDatiPasso(); 
        
        if (!$propas_rec){
            Out::msgInfo("Errore", "Non trovato il form associato al passo");
            return;
        }
        
        Out::html($this->nameForm . '_divDomanda', $propas_rec['PRODPA']);
        Out::html($this->nameForm . '_divNote', $propas_rec['PRONOT']);
        Out::attributo($this->nameForm . '_divNote', 'style', 0, $propas_rec['PRONOTSTYLE']);
        Out::css($this->nameForm . '_FlagSi_lbl', 'color', 'green');
        Out::css($this->nameForm . '_FlagSi_lbl', 'font-weight', 'bold');
        Out::css($this->nameForm . '_FlagNo_lbl', 'color', 'red');
        Out::css($this->nameForm . '_FlagNo_lbl', 'font-weight', 'bold');
        
        //Imposta eventualke scelta già memorizzata
        $this->tipoScelta = $propas_rec['PRORIS'];
        
        if ($this->tipoScelta == 'Si'){
            Out::attributo($this->nameForm . "_FlagSi", "checked", "0", "checked");
        }
        else if ($this->tipoScelta == 'No'){
            Out::attributo($this->nameForm . "_FlagNo", "checked", "0", "checked");
        }
        
        
    }

    public function preCondizioni(){
        $this->setErrCode(null);
        $this->setErrMessage('');

        //Out::msgInfo("Tipo Scelta", $this->tipoScelta);
        
        if ($this->tipoScelta == ''){

            $this->setErrCode(-1);
            $this->setErrMessage('Rispondere alla domanda prima di proseguire');

            return false;
        }
        /*
         * $this->formmdata si legge il button radio
         */

        return true;
    }

    public function aggiornaDati(){
        $this->setErrCode(null);
        $this->setErrMessage('');
        
        
        //Out::msgInfo("tipoScelta",$this->tipoScelta);
        $propas_rec = $this->getDatiPasso(); 

        if (!$propas_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage('Non trovato record di PROPAS da aggiornare');

            return false;
        }

        $propas_rec[PRORIS] = $this->tipoScelta;
        
        $update_info = 'Salvataggio risposta su passo: ' . $passoCorrente[PROPAK] . ' Pratica' . $passoCorrente[PROPAK];

        if (!$this->updateRecord($this->PRAM_DB, 'PROPAS', $propas_rec, $update_info, 'ROWID')) {
            $this->setErrCode(-1);
            $this->setErrMessage('Salvataggio risposta non riuscito');
            
            return false;
        }
        
        return true;
    }
    
    public function vaiAvanti() {
        parent::vaiAvanti();

        return true;
    }
    
    
    public function validaDati($ricaricaDati = true) {
        if (!$this->validaPasso()) {
            return false;
        }

        if (!$this->validaDatiAggiuntivi()) {
            return false;
        }

        if ($ricaricaDati) {
            $this->caricaGrigliaDatiAggiuntivi();
        }

        return true;
    }

    public function validaPasso() {

        return true;
    }

    public function getTipoScelta() {
        return $this->tipoScelta;
    }

    public function setTipoScelta($tipoScelta) {
        $this->tipoScelta = $tipoScelta;
    }

}

