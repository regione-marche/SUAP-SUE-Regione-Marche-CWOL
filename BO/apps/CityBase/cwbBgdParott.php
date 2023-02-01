<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenRow.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBgdParott() {
    $cwbBgdParott = new cwbBgdParott();
    $cwbBgdParott->parseEvent();
    return;
}

class cwbBgdParott extends cwbBpaGenRow {
        
    protected function initVars() {
        $this->GRID_NAME = 'gridBgdParott';
        $this->divGes = "cwbBgdParott_divGestione";
        $this->divGesDocer = "cwbBgdParott_divGestioneDocer";
        $this->AUTOR_MODULO = 'BGD';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BGD(); 
    } 

    public function customParseEvent() {
        switch ($_POST['event']) {            
            case 'onChange':
                switch ($_POST['id']) {  
                    case $this->nameForm . '_BGD_PAROTT[ALF_ENDPOINT_PORT]':                        
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['ALF_ENDPOINT_PORT'], $this->nameForm . '_BGD_PAROTT[ALF_ENDPOINT_PORT]');    
                        break; 
                    case $this->nameForm . '_BGD_PAROTT[F_GESTDOC]':                        
                        $this->GestioneCampi();
                        break; 
                }
                break;
        }
    }
    
    protected function preApriForm() {
        $this->initComboGestDocu();
    }
    
    protected function postApriForm() {
        Out::hide($this->divGesDocer);
    }
    
    protected function postDettaglio($index) {
        Out::setFocus('', $this->nameForm . '_BGD_PAROTT[F_GESTDOC]');        
    }        

    protected function sqlDettaglio($index) {
        $this->SQL = $this->libDB->getSqlLeggiBgdParott();        
    }
        
    public function GestioneCampi() {
        switch($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['F_GESTDOC']){
            case 0:
                Out::hide($this->divGesDocer);
                Out::hide($this->divGes);
                break;    
        
            case 3:
                Out::show($this->divGesDocer);
                Out::hide($this->divGes);
                break;    

            case 4:
                Out::hide($this->divGesDocer);
                Out::show($this->divGes);
                break;
        }    
    }

    private function initComboGestDocu() {
        // Combo Gestione documentale in uso
        Out::select($this->nameForm . '_BGD_PAROTT[F_GESTDOC]', 1, "0", 1, "0 - Nessuna gestione documentale");
        Out::select($this->nameForm . '_BGD_PAROTT[F_GESTDOC]', 1, "3", 0, "3 - DOCER");
        Out::select($this->nameForm . '_BGD_PAROTT[F_GESTDOC]', 1, "4", 0, "4 - ALFRESCO INTERNO");
    }
}
?>