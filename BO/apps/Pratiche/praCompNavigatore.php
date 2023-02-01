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


/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function praCompNavigatore() {
    $praCompNavigatore = new praCompNavigatore();
    $praCompNavigatore->parseEvent();
    return;
}

class praCompNavigatore extends itaModel {

    protected $readOnly = false;

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();

        $this->readOnly = App::$utente->getKey($this->nameForm . '_readOnly');
    }

    function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_readOnly', $this->readOnly);
        }
    }

    public function setReadOnly($readOnly) {
        $this->readOnly = $readOnly;
    }

    public function parseEvent() {
        parent::parseEvent();

        switch ($this->event) {
            case 'openform':
                //$this->inizializza();
                $returnModel = $this->returnModel;
                break;

            case 'onClick':
             
                switch ($_POST['id']) {
                    case $this->nameForm . '_Pannelli':
//                        $this->returnEvent = 'returnConfiguraPannelli';
//                        $this->returnToParent(array(), false);
                        
                        $arrayNavigatore = array(
                            "eventoNavigatore" =>'configuraPannelli'
                            );
                        
                        $this->returnToParent($arrayNavigatore, false);
                        
                        break;

                    case $this->nameForm . '_callIndietro':
                        $arrayNavigatore = array(
                            "eventoNavigatore" =>'indietro'
                            );
                        
                        $this->returnToParent($arrayNavigatore, false);
                        break;

                    case $this->nameForm . '_callAvanti':
                        $arrayNavigatore = array(
                            "eventoNavigatore" =>'avanti'
                            );
                        
                        $this->returnToParent($arrayNavigatore, false);
                        break;

                    case $this->nameForm . '_callInizio':
                        $arrayNavigatore = array(
                            "eventoNavigatore" =>'inizio'
                            );
                        
                        $this->returnToParent($arrayNavigatore, false);
                        break;

//                    case 'close-portlet':
//                        $this->returnToParent();
//                        break;
                }


                break;
        }
    }

    public function close() {
        parent::close();

        App::$utente->removeKey($this->nameForm . '_readOnly');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($arrayNavigatore, $close = true) {
        if ($close) {
            $this->close();
        }
       
        $model = $this->returnModel;
        /* @var $objModel itaModel */
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent($this->returnEvent);
        $objModel->setFormData($arrayNavigatore);
        $objModel->parseEvent();
    }

    public function inizializza($datiWorkflow) {
        //Out::msgInfo("Dati WorkFlow", print_r($datiWorkflow, true));
        
        $passi_tab = $datiWorkflow->getPassi();
        
        $passi_rec = $passi_tab[0];
        $passoCorrente = $datiWorkflow->getPassoCorrente();

        // Si attiva il bottone indietro
        Out::enableButton($this->nameForm . '_callIndietro');
        //Out::show($this->nameForm . '_callIndietro');

        if ($passi_rec['PROPAK'] == $passoCorrente['PROPAK']){
            // Si disabilita bottone "indietro", perchè siamo sul primo passo
            Out::disableButton($this->nameForm . '_callIndietro');
            //Out::hide($this->nameForm . '_callIndietro');
            
            // Out::msgInfo("Primo Passo", "Sono sul primo passo");
        }

        Out::html($this->nameForm . '_callAvanti_lbl', "Avanti");
        Out::delClass($this->nameForm . '_callAvanti_icon_right', 'ui-icon-stop');
        Out::addClass($this->nameForm . '_callAvanti_icon_right', 'ui-icon-arrowthick-1-e');

        // Raccolta Dati (0) o Domanda Semplice (1)
        if ($passoCorrente['PROQST'] < 2){

            if ($passoCorrente['PROVPA'] == '') {
                Out::html($this->nameForm . '_callAvanti_lbl', "Fine");
                Out::delClass($this->nameForm . '_callAvanti_icon_right', 'ui-icon-arrowthick-1-e');
                Out::addClass($this->nameForm . '_callAvanti_icon_right', 'ui-icon-stop');  //ui-icon-circle-check
            }
            
            
        }
        
        Out::hide($this->nameForm . '_callInizio');
        
    }

}
