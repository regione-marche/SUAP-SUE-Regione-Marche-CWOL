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
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praCompElencoPassi() {
    $praCompElencoPassi = new praCompElencoPassi();
    $praCompElencoPassi->parseEvent();
    return;
}

class praCompElencoPassi extends itaModel {
    
    /* @var $datiWorkflow praLibDatiWorkFlow */
    private $datiWorkflow;
    public $nameForm = 'praCompElencoPassi';

    function __construct() {
        parent::__construct();
    }

    protected function postInstance() {
        parent::postInstance();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    function getDatiWorkflow() {
        return $this->datiWorkflow;
    }

    function setDatiWorkflow($datiWorkflow) {
        $this->datiWorkflow = $datiWorkflow;
    }

    public function parseEvent() {
        parent::parseEvent();
        
        switch ($this->event) {
            case 'openform':
                $elenco = $this->datiWorkflow->getPassi();
                if ($elenco){
                    $this->CaricaGriglia($this->nameForm ."_gridPassi", $elenco);
                }
                
                break;
            case 'onSelectRow':

                // Trovo il propak della riga selezionata
                $praLib = new praLib();
                
                //$praLib->GetAnapra($Codice)
                
                $anapra_rec = $praLib->GetPropas($_POST['rowid'], 'rowid');
                if ($anapra_rec){
                    $this->returnToParent($anapra_rec['PROPAK'], false);
                    
                }

                break;
        }
    }

    public function close() {
        parent::close();
    }

    /**
     * 
     * @param type $griglia
     * @param array $appoggio
     * @param type $tipo 1=
     * @param type $caption
     * @return type
     */
    private function CaricaGriglia($griglia, $appoggio, $caption = '') {
        if ($caption) {
            TableView::setCaption($griglia, $caption);
        }
        if (is_null($appoggio))
            $appoggio = array();
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        TableView::enableEvents($griglia);
        $ita_grid01->getDataPage('json', true);
        return;
    }

    public function setSelection($rowid){
        TableView::setDeselectAll($this->nameForm . '_gridPassi');
        TableView::setSelection($this->nameForm . '_gridPassi', $rowid, 'id', false);
        //DiagramView::
    }
    
    public function refreshSelection(){
        
        $this->setSelection($this->datiWorkflow->passoCorrente['ROWID']);
            
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
    
}
