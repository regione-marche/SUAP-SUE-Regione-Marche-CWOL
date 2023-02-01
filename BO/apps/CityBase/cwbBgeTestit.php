<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';


function cwbBgeTestit() {
    $cwbBgeTestit = new cwbBgeTestit();
    $cwbBgeTestit->parseEvent();
    return;
}

class cwbBgeTestit extends cwbBpaGenTab {
        
    function initVars() {
        $this->GRID_NAME = 'gridBgeTestit';
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 1;
        $this->libDB = new cwbLibDB_BGE();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    
    public function customParseEvent() {
        switch ($_POST['event']) {            
            case 'onChange':
                switch ($_POST['id']) {                    
                    case $this->nameForm . '_PROGTESTIT':                        
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_PROGTESTIT'], $this->nameForm . '_PROGTESTIT');
                        break; 
                }
                break;
        }
    }

    protected function preNuovo() {
        $this->pulisciCampi();
    }
    
    protected function postNuovo() {        
        $progr = cwbLibCalcoli::trovaProgressivo("PROGTESTIT", "BGE_TESTIT"); 
        Out::valore($this->nameForm . '_BGE_TESTIT[PROGTESTIT]', $progr);
        Out::setFocus("", $this->nameForm . '_BGE_TESTIT[TESTIT]');   
    }
    
    protected function postApriForm() {
        $this->initComboProvTestoRicerca();
        $this->initComboProvTestoGestione();
        Out::setFocus("", $this->nameForm . '_TESTIT');
    }
    
    protected function postAltraRicerca() {                           
        Out::setFocus("", $this->nameForm . '_TESTIT');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BGE_TESTIT[TESTIT]');
    }
       
    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['PROGTESTIT_formatted'] != '') {
            $this->gridFilters['PROGTESTIT'] = $this->formData['PROGTESTIT_formatted'];
        }
        if ($_POST['TESTIT'] != '') {
            $this->gridFilters['TESTIT'] = $this->formData['TESTIT'];
        }
    }
    
    protected function preDettaglio() {
        $this->pulisciCampi();
    }
   
    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BGE_TESTIT[PROGTESTIT]', 'readonly', '0');        
        Out::css($this->nameForm . '_BGE_TESTIT[PROGTESTIT]', 'background-color', '#FFFFE0');
        Out::valore($this->nameForm . '_BGE_TESTIT[TESTIT]', trim($this->CURRENT_RECORD['TESTIT']));        
        Out::setFocus('', $this->nameForm . '_BGE_TESTIT[TESTIT]');        
    }       
        
    public function postSqlElenca($filtri,&$sqlParams = array()){        
        $filtri['PROGTESTIT'] = trim($this->formData[$this->nameForm . '_PROGTESTIT']);        
        $filtri['TESTIT'] = trim($this->formData[$this->nameForm . '_TESTIT']);
        $filtri['TESTOPROV'] = trim($this->formData[$this->nameForm . '_TESTOPROV']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBgeTestit($filtri, true,$sqlParams);        
    }

    public function sqlDettaglio($index,&$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBgeTestitChiave($index, $sqlParams);        
    }
    
    protected function elaboraRecords($Result_tab) {        
        foreach ($Result_tab as $key => $Result_rec) {                        
            $Result_tab[$key]['PROGTESTIT_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['PROGTESTIT']);
            $Result_tab[$key]['TESTIT'] = trim($Result_tab[$key]['TESTIT']); // trimmo descrizione su grid.
            switch ($Result_tab[$key]['TESTOPROV']){
                case 1:
                    $Result_tab[$key]['TESTOPROV'] = 'Demografici';
                    break;
                
                case 2:
                    $Result_tab[$key]['TESTOPROV'] = 'Tributi';
                    break;
                
                case 3:
                    $Result_tab[$key]['TESTOPROV'] = 'Ici';
                    break;
                
                case 4:
                    $Result_tab[$key]['TESTOPROV'] = 'Servizi';
                    break;

                case 5:
                    $Result_tab[$key]['TESTOPROV'] = 'Finanziaria';
                    break;
                
                case 6:
                    $Result_tab[$key]['TESTOPROV'] = 'Unità ecografiche';
                    break;

                case 7:
                    $Result_tab[$key]['TESTOPROV'] = 'Atti Amministrativi';
                    break;

                case 8:
                    $Result_tab[$key]['TESTOPROV'] = 'Acquedotto';
                    break;

                case 9:
                    $Result_tab[$key]['TESTOPROV'] = 'Sociali';
                    break;
                
                case 10:
                    $Result_tab[$key]['TESTOPROV'] = 'Controllo di gestione';
                    break;
                
                case 11:
                    $Result_tab[$key]['TESTOPROV'] = 'Servizi cimiteriali';
                    break;
            
                case 12:
                    $Result_tab[$key]['TESTOPROV'] = 'Avviso Unico Pagamento';
                    break;
                
                case 13:
                    $Result_tab[$key]['TESTOPROV'] = 'Recupero crediti';
                    break;
                
                case 14:
                    $Result_tab[$key]['TESTOPROV'] = 'Anagrafe';
                    break;
                
                case 15:
                    $Result_tab[$key]['TESTOPROV'] = 'Elettorale';
                    break;
                
                case 16:
                    $Result_tab[$key]['TESTOPROV'] = 'Stato Civile';
                    break;
                
                case 17:
                    $Result_tab[$key]['TESTOPROV'] = 'Notifiche';
                    break;
                
                case 18:
                    $Result_tab[$key]['TESTOPROV'] = 'Portale Web';
                    break;
                
                case 19:
                    $Result_tab[$key]['TESTOPROV'] = 'Protocollo';
                    break;
            }
       }    
        return $Result_tab;
    }
    
    private function initComboProvTestoGestione() {
        // Provenienza Testo
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "0", 1, " ");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "1", 0, "Demografici");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "2", 0, "Tributi");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "3", 0, "Ici");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "4", 0, "Servizi");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "5", 0, "Finanziaria");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "6", 0, "Unità ecografiche");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "7", 0, "Atti Amministrativi");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "8", 0, "Acquedotto");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "9", 0, "Sociali");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "10", 0, "Controllo di gestione");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "11", 0, "Servizi cimiteriali");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "12", 0, "Avviso Unico Pagamento");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "13", 0, "Recupero crediti");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "14", 0, "Anagrafe");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "15", 0, "Elettorale");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "16", 0, "Stato Civile");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "17", 0, "Notifiche");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "18", 0, "Portale Web");
        Out::select($this->nameForm . '_BGE_TESTIT[TESTOPROV]', 1, "19", 0, "Protocollo");
    }

   
    private function initComboProvTestoRicerca() {
        // Provenienza Testo
        Out::select($this->nameForm . '_TESTOPROV', 1, "0", 1, " ");
        Out::select($this->nameForm . '_TESTOPROV', 1, "1", 0, "Demografici");
        Out::select($this->nameForm . '_TESTOPROV', 1, "2", 0, "Tributi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "3", 0, "Ici");
        Out::select($this->nameForm . '_TESTOPROV', 1, "4", 0, "Servizi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "5", 0, "Finanziaria");
        Out::select($this->nameForm . '_TESTOPROV', 1, "6", 0, "Unità ecografiche");
        Out::select($this->nameForm . '_TESTOPROV', 1, "7", 0, "Atti Amministrativi");
        Out::select($this->nameForm . '_TESTOPROV', 1, "8", 0, "Acquedotto");
        Out::select($this->nameForm . '_TESTOPROV', 1, "9", 0, "Sociali");
        Out::select($this->nameForm . '_TESTOPROV', 1, "10", 0, "Controllo di gestione");
        Out::select($this->nameForm . '_TESTOPROV', 1, "11", 0, "Servizi cimiteriali");
        Out::select($this->nameForm . '_TESTOPROV', 1, "12", 0, "Avviso Unico Pagamento");
        Out::select($this->nameForm . '_TESTOPROV', 1, "13", 0, "Recupero crediti");
        Out::select($this->nameForm . '_TESTOPROV', 1, "14", 0, "Anagrafe");
        Out::select($this->nameForm . '_TESTOPROV', 1, "15", 0, "Elettorale");
        Out::select($this->nameForm . '_TESTOPROV', 1, "16", 0, "Stato Civile");
        Out::select($this->nameForm . '_TESTOPROV', 1, "17", 0, "Notifiche");
        Out::select($this->nameForm . '_TESTOPROV', 1, "18", 0, "Portale Web");
        Out::select($this->nameForm . '_TESTOPROV', 1, "19", 0, "Protocollo");
    }
}
?>