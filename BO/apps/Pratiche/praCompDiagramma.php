<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDiagramma.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/DiagramView.class.php';

function praCompDiagramma() {
    $praCompDiagramma = new praCompDiagramma();
    $praCompDiagramma->parseEvent();
    return;
}

class praCompDiagramma extends itaModel {

    public $nameForm = 'praCompDiagramma';
    public $workFlow;
    public $praLib;

    /* @var $datiWorkflow praLibDatiWorkFlow */
    
    private $gesnum;
    private $propak;
    private $groupsStatus = array();

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_groupsStatus', $this->groupsStatus);
        App::$utente->setKey($this->nameForm . '_gesnum', $this->gesnum);
        App::$utente->setKey($this->nameForm . '_propak', $this->propak);
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_gesnum');
        App::$utente->removeKey($this->nameForm . '_propak');
        App::$utente->removeKey($this->nameForm . '_groupsStatus');

        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($propak, $close = true) {
        if ($close) {
            $this->close();
        }

        $model = $this->returnModel;
        /* @var $objModel itaModel */
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent($this->returnEvent);
        $objModel->setFormData($propak);
        $objModel->parseEvent();
    }

    public function setDatiWorkflow($datiWorkflow) {
        $this->datiWorkflow = $datiWorkflow;
    }

    protected function postInstance() {
        parent::postInstance();

        $this->praLib = new praLib;
        $this->flowChart = $this->nameForm . '_divFlowchart';
        $this->groupsStatus = App::$utente->getKey($this->nameForm . '_groupsStatus');
        $this->gesnum = App::$utente->getKey($this->nameForm . '_gesnum');
        $this->propak = App::$utente->getKey($this->nameForm . '_propak');

    }

    public function parseEvent() {
        parent::parseEvent();

        //Out::msgInfo("Evento sul Diagramma", $this->event);
        //Out::msgInfo(Diagramma, print_r($_POST, true));

        switch ($_POST['event']) {
            case 'openform':
                $proges_rec = $this->praLib->GetProges($this->datiWorkflow->passoCorrente['PRONUM']);
                if ($proges_rec['GESDIAG']) {
                    $dv = new DiagramView($this->nameForm . '_divFlowchart');
                    $dv->importJSON($proges_rec['GESDIAG']);
                    $this->setDiagramColors();
                    // Logica che attiva/disattiva i passi dei Gruppi
                    $this->setGestioneGruppi();
                    $this->inizializzaOggetti();
                }
                break;

            case 'onClick':

                switch ($_POST['id']) {
//                    case 'close-portlet':
//                        $this->returnToParent();
//                        break;
                    case $this->nameForm . '_divFlowchart':
                        $indice = $this->nameForm . '_divFlowchart';

                        //if ($_POST[$indice][selectedNodes][0] != ''){
                        if ($_POST['selectionNode'] != '' && $_POST['selectionAction'] == 1) {

                            $propak = $_POST['selectionNode'];

                            $this->returnToParent($propak, false);
                        }

                        break;

                    case $this->flowChart . '_ActionBar_ChangeColor':
                        if (!$_POST[$this->flowChart]['selectedNodes'][0]) {
                            break;
                        }

                        foreach ($_POST[$this->flowChart]['selectedNodes'] as $nodekey) {
                            DiagramView::diagramSetNodeBackgroundColor($this->flowChart, $nodekey, '#' . substr(md5(rand()), 0, 6));
                            DiagramView::diagramSetNodeBorderColor($this->flowChart, $nodekey, '#' . substr(md5(rand()), 0, 6));
                            DiagramView::diagramSetNodeTextColor($this->flowChart, $nodekey, '#' . substr(md5(rand()), 0, 6));
                        }
                        break;
                    case $this->flowChart . '_ActionBar_Groups':
                        
                        $inputs = array();
                        $prodiaggruppi_tab = $this->praLib->GetProdiagGruppi($this->gesnum);
                        
                        if (!count($prodiaggruppi_tab)) {
                            Out::msgInfo('Attenzione', 'Non ci sono gruppi configurati.');
                            break;
                        }

                        foreach ($prodiaggruppi_tab as $prodiaggruppi_rec) {
                            $inputGruppo = array(
                                'label' => array('value' => $prodiaggruppi_rec['DESCRIZIONE'], 'style' => 'float: right; margin-left: 10px;'),
                                'id' => $this->nameForm . '_GRUPPI[' . $prodiaggruppi_rec['ROW_ID'] . ']',
                                'name' => $this->nameForm . '_GRUPPI[' . $prodiaggruppi_rec['ROW_ID'] . ']',
                                'type' => 'checkbox'
                            );

                            if (!isset($this->groupsStatus[$prodiaggruppi_rec['ROW_ID']]) || $this->groupsStatus[$prodiaggruppi_rec['ROW_ID']]) {
                                $inputGruppo['checked'] = 'checked';
                            }

                            $inputs[] = $inputGruppo;
                        }

                        Out::msgInput('Configurazione gruppi', $inputs, array(
                            'Conferma' => array(
                                'id' => $this->nameForm . '_confermaConfigurazioneGruppi',
                                'model' => $this->nameForm
                            )
                            ), $this->nameForm);
                        
                        break;

                    case $this->nameForm . '_confermaConfigurazioneGruppi':
                        $gruppiConfigurati = $_POST[$this->nameForm . '_GRUPPI'];
                        
                        foreach ($gruppiConfigurati as $rowid =>$gruppo){

                            $prodiaggruppi_rec = $this->praLib->GetProdiagGruppi($rowid, "rowid", false);
                            $prodiaggruppi_rec['STATO'] = $gruppo;
                            
                            try {
                                $update_info = "Modificato stato gruppo ROW_ID = " . $prodiaggruppi_rec['ROW_ID'] . " Descrizione:  " . $prodiaggruppi_rec['DESCRIZIONE'];
                                $nrow_1 = $this->updateRecord($this->praLib->getPRAMDB(), 'PRODIAGGRUPPI', $prodiaggruppi_rec, $update_info, "ROW_ID");

                                if (!$nrow_1) {
                                    Out::msgStop("Errore", "Errore aggiornamento Gruppo.");
                                    return;
                                }
                            } catch (Exception $exc) {
                                Out::msgStop("Errore", $exc->getMessage());
                                return;
                            }
                            
                        }

                        $this->caricaGruppi();
                        $this->gestionePassiGruppi($this->gesnum, $this->propak);

                        break;
                        
                }

                break;
        }
    }

    public function workSpace() {
        Out::hide($this->nameForm . '_divRisultato');
        Out::hide($this->nameForm . '_divGestione');

        if (func_num_args()) {
            foreach (func_get_args() as $div) {
                Out::show($this->nameForm . "_$div");
            }
        }
    }

    public function buttonBar() {
        Out::hide($this->nameForm . '_Elenco');
        Out::hide($this->nameForm . '_Salva');

        if (func_num_args()) {
            foreach (func_get_args() as $button) {
                Out::show($this->nameForm . "_$button");
            }
        }
    }

    public function refreshSelection() {
        $passoCorrente = $this->datiWorkflow->getPassoCorrente();
        DiagramView::diagramSetSelection($this->nameForm . '_divFlowchart', $passoCorrente['PROPAK']);
    }

    public function setDiagramColors() {
        
        foreach ($this->datiWorkflow->passi as $propas) {
            $propasfatti_rec = $this->praLib->GetPropasFatti($propas['PROPAK'], 'propak');
            
            if ($propasfatti_rec) {
                DiagramView::diagramSetNodeBackgroundColor($this->flowChart, $propas['PROPAK'], 'orange');
            } else {
                DiagramView::diagramSetNodeBackgroundColor($this->flowChart, $propas['PROPAK'], 'LightGreen');
            }
        }
    }
    
    public function setGestioneGruppi(){
        
        $this->gestionePassiGruppi($this->datiWorkflow->passoCorrente['PRONUM'], $this->datiWorkflow->passoCorrente['PROPAK']);
    }

    private function gestionePassiGruppi($numeroFascicolo, $codicePassoCorrente){
        $formWorkflow = $this->nameForm . '_divFlowchart';

        $praLibDiagramma = new praLibDiagramma($formWorkflow, praLibDiagramma::DIAGRAMMA_FASCICOLO);
        
        // Scorro i gruppi associati al fascicolo elettronico, se ci sono gruppi nascosti, nascondo i passi associati
        $prodiaggruppi_tab = $this->praLib->GetProdiagGruppi($numeroFascicolo);
        foreach ($prodiaggruppi_tab as $prodiaggruppi_rec) {
            if ($prodiaggruppi_rec['STATO'] == 0){    // Controllo se gruppo impostato come nascosto STATO = 0
                $praLibDiagramma->nascondiNodiGruppo($prodiaggruppi_rec['ROW_ID']);
                
            }
            else{
                $praLibDiagramma->mostraNodiGruppo($prodiaggruppi_rec['ROW_ID']);
                
            }
            
        }

        //Scorro i passi fatti PROPASFATTI e controllo se fanno parte di qualche gruppo 
        $propassfatti_tab = $this->praLib->GetPropasFatti($numeroFascicolo, 'pronum', true);
        foreach ($propassfatti_tab as $propassfatti_rec) {
            // Se passo fatto appartiene ad un gruppo, visualizzo tutti i passi del gruppo
            $this->attivaPassiGruppo($propassfatti_rec['PROPAK'], $praLibDiagramma);
            
        }

        
        // Se passo attuale fa parte di un gruppo, si attivano tutti passi del gruppo
        $this->attivaPassiGruppo($codicePassoCorrente, $praLibDiagramma);

        
    }


    
    private function attivaPassiGruppo($propak, $praLibDiagramma){

        $passofattogruppo_tab = $this->praLib->GetProdiagPassiGruppi($propak, 'propak', true);
        foreach ($passofattogruppo_tab as $passofattogruppo_rec) {

            $praLibDiagramma->mostraNodiGruppo($passofattogruppo_rec['ROW_ID_PRODIAGGRUPPI']);
            
        }

        
    }
    
    
    private function nascondiFigura($propak, $nascondi = 'true'){
        if ($nascondi) DiagramView::diagramHideElement($this->flowChart, $propak);
        else DiagramView::diagramShowElement($this->flowChart, $propak);
        
        
    }
    
    private function inizializzaOggetti(){
        $this->groupsStatus = array();

        $this->gesnum = $this->datiWorkflow->passoCorrente['PRONUM'];
        $this->propak = $this->datiWorkflow->passoCorrente['PROPAK'];
        
        $this->caricaGruppi();
        
//        $prodiaggruppi_tab = $this->praLib->GetProdiagGruppi($this->gesnum);
//        foreach ($prodiaggruppi_tab as $prodiaggruppi_rec) {
//            $this->groupsStatus[$prodiaggruppi_rec['ROW_ID']] = $prodiaggruppi_rec['STATO'];
//        }
        
    }
    
    private function caricaGruppi(){
        $prodiaggruppi_tab = $this->praLib->GetProdiagGruppi($this->gesnum);
        foreach ($prodiaggruppi_tab as $prodiaggruppi_rec) {
            $this->groupsStatus[$prodiaggruppi_rec['ROW_ID']] = $prodiaggruppi_rec['STATO'];
        }
        
    }
    
    
}
