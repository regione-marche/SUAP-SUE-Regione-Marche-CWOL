<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';

function cwbBgdArcdoc() {
    $cwbBgdArcdoc = new cwbBgdArcdoc();
    $cwbBgdArcdoc->parseEvent();
    return;
}

class cwbBgdArcdoc extends itaFrontControllerCW {
    private $libDB_BGD;
    private $libDB_BOR;
    private $DB;
    private $recBgdParott;
    private $recBgdAdassm;
    private $workingClass;
    private $codiceEnte;
    private $codiceAOO;
    private $currentChecked;
    
    public function postItaFrontControllerCostruct() {
        $this->libDB_BGD = new cwbLibDB_BGD();
        $this->libDB_BOR = new cwbLibDB_BOR();
        $this->caricaCurrentChecked();
         
        try {
            $this->caricaCodiceEnteSession();
            $this->caricaCodiceAOOSession();
            $this->caricaBgdParott();
        } catch (Exception $ex) {
            Out::msgInfo("CITYWARE", $ex->getCode() . ' - ' . $ex->getMessage());
        }
    }
    
    public function __destruct() {        
        if ($this->close != true) {
            $this->salvaBgdParottSession();
            $this->salvaBgdAdassmSession();        
            $this->salvaCodiceEnteSession();
            $this->salvaCodiceAOOSession();
            $this->salvaCurrentCheckedSession(); 
        }
        
        parent::__destruct();        
    }
    
    protected function close() {
        cwbParGen::removeFormSessionVars($this->nameForm);
        parent::close();       
    }
    
     public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': 
                $this->initCombo();
                Out::setFocus("", $this->nameForm . '_CODAREAMA');
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca': 
                        $this->elenca(array());
                        break;
                    case $this->nameForm . '_ApplicaFiltri': 
                        $this->elenca(array(
                            'F_NO_UUID' => $_POST[$this->nameForm . '_SENZAUUID'],
                            'DATA_ARCH_DA' => $_POST[$this->nameForm . '_DATAARCH_da'],
                            'DATA_ARCH_A' => $_POST[$this->nameForm . '_DATAARCH_a'],
                        ));
                        break;
                    case $this->nameForm . '_Riversa': 
                        $this->riversaDocumenti();
                        break;
                    case 'close-portlet': 
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODAREAMA': 
                        $this->initComboModuli($_POST[$this->nameForm . '_CODAREAMA']);
                        $this->initComboTipoDoc($_POST[$this->nameForm . '_CODAREAMA'], $_POST[$this->nameForm . '_CODMODULO']);
                        break;
                    case $this->nameForm . '_CODMODULO': 
                        $this->initComboTipoDoc($_POST[$this->nameForm . '_CODAREAMA'], $_POST[$this->nameForm . '_CODMODULO']);
                        break;
                    case $this->nameForm . '_ENTE': 
                        $this->codiceEnte = $_POST[$this->nameForm . '_ENTE'];
                        $this->initComboAOO($this->codiceEnte);
                        break;
                    case $this->nameForm . '_AOO': 
                        $this->codiceAOO = $_POST[$this->nameForm . '_AOO'];                        
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridDocumenti':
                        switch ($_POST['colName']) {
                            case 'SEL':
                                if (strpos($_POST['cellContent'], 'check') === false) {
                                    array_push($this->currentChecked, $_POST['rowid']);
                                    Out::setCellValue($this->nameForm . '_gridDocumenti', $_POST['rowid'], 'SEL', '<span class="ui-icon ui-icon-check" style="display: inline-block;"></span>');
                                } else {
                                    unset($this->currentChecked[array_search($_POST['rowid'], $this->currentChecked)]);
                                    Out::setCellValue($this->nameForm . '_gridDocumenti', $_POST['rowid'], 'SEL', '&nbsp;');
                                }
                        }                         
                        break;
                }
                break;
        }
    }
    
    private function caricaBgdParott() {
      $this->recBgdParott = cwbParGen::getFormSessionVar($this->nameForm, 'recBgdParott');
      if (!$this->recBgdParott) {
            $this->recBgdParott = $this->libDB_BGD->leggiBgdParott();
        }        
    }
    
    private function salvaBgdParottSession() {
        cwbParGen::setFormSessionVar($this->nameForm,'recBgdParott', $this->recBgdParott);
    }
    
    private function caricaBgdAdassm() {
        $this->recBgdAdassm = cwbParGen::getFormSessionVar($this->nameForm,'recBgdAdassm');
        if (!$this->recBgdAdassm) {
            $this->recBgdAdassm = $this->libDB_BGD->leggiBgdAdassmChiave($_POST[$this->nameForm . '_TIPO_DOC']);
        }   
    }
    
    private function caricaCurrentChecked() {
        $this->currentChecked = cwbParGen::getFormSessionVar($this->nameForm, 'currentChecked');

        if(!$this->currentChecked){
            $this->currentChecked = array();
        }
    }
    
    private function salvaBgdAdassmSession() {
        cwbParGen::setFormSessionVar($this->nameForm,'recBgdAdassm', $this->recBgdAdassm);

    }
    
    private function instanceWorkingClass() {
        include_once ITA_BASE_PATH . '/apps/CityBase/' . $this->recBgdAdassm['PHP_CLASS'] . '.php';
        $this->workingClass = new $this->recBgdAdassm['PHP_CLASS'];      
        $this->workingClass->setRecBgdParott($this->recBgdParott);
        $this->workingClass->setRecBgdAdassm($this->recBgdAdassm);
        $this->workingClass->setCodiceEnte($this->codiceEnte);
        $this->workingClass->setCodiceAOO($this->codiceAOO);
    }
    
    private function caricaCodiceEnteSession() {
        $this->codiceEnte = cwbParGen::getFormSessionVar($this->nameForm, 'codiceEnte');
    }
    
    private function salvaCodiceEnteSession() {        
        cwbParGen::setFormSessionVar($this->nameForm,'codiceEnte', $this->codiceEnte);
    }
    
    private function caricaCodiceAOOSession() {
        $this->codiceAOO = cwbParGen::getFormSessionVar($this->nameForm, 'codiceAOO');
    }
    
    private function salvaCodiceAOOSession() {
        cwbParGen::setFormSessionVar($this->nameForm,'codiceAOO', $this->codiceAOO);
    }
    
    private function salvaCurrentCheckedSession() {
        cwbParGen::setFormSessionVar($this->nameForm,'currentChecked', $this->currentChecked);
    }
    
    private function riversaDocumenti(){
        try {           
            $this->caricaBgdAdassm(); 
            $this->instanceWorkingClass();
            $this->workingClass->riversaDocumenti($this->currentChecked);            
            Out::msgInfo("Gestione documentale", "I documenti selezionati sono stati riversati nel sistema documentale!");            
        } catch (Exception $e) {
             Out::msgStop("ERRORE RIVERSAMENTO", $e->getCode() . ' - ' . $e->getMessage());
        }
    }
    
    private function elenca($filtri) {
        // Controlla che sia stato selezionato il tipo documento
        if (trim($_POST[$this->nameForm . '_TIPO_DOC']) == '') {
            Out::msgStop("ERRORE", "Selezionare il tipo documento!");
            return;
        }
        
        // Controlla che siano stati selezionati Ente e AOO
        $this->codiceEnte = $_POST[$this->nameForm . '_ENTE'];
        $this->codiceAOO = $_POST[$this->nameForm . '_AOO'];
        if ($this->codiceEnte == '' || $this->codiceAOO == '') {
            Out::msgStop("ERRORE", "Selezionare Ente e AOO!");
            return;
        }
        
        // Se utilizza Doc/er, controlla che Ente e AOO siano valorizzati
        if ($this->recBgdParott['F_GESTDOC'] == 3) {
            $recBorEntidc = $this->libDB_BOR->leggiBorEntidc($this->codiceEnte);
            if ($recBorEntidc == null || strlen($recBorEntidc['ENTE_DOCER']) == 0) {
                Out::msgStop("ERRORE", "Selezionare Ente Docer!");
                return;                
            }
            
            $recBorAoodc = $this->libDB_BOR->leggiBorAoodc($this->codiceAOO);
            if ($recBorAoodc == null || strlen($recBorAoodc['AOO_DOCER']) == 0) {
                Out::msgStop("ERRORE", "Selezionare AOO Docer!");
                return;                
            }
            
            $this->codiceEnte = $recBorEntidc['ENTE_DOCER'];
            $this->codiceAOO = $recBorAoodc['AOO_DOCER'];
        }      
        
        $this->caricaBgdAdassm(); 
        
        $this->instanceWorkingClass();
        
        // Inietta il model all'interno del div dei risultati
        $this->impostaDivRisultati();
        
        // Imposta grid documenti
        $this->impostaGridDoc($filtri);        
    }    
        
    private function impostaDivRisultati() {        
        cwbLibHtml::includiFinestra($this->workingClass->getModel(), $this->nameForm, 'divRisultati');        
    }
    
    private function apriDB() {
        if (!$this->DB) {
            try {
                $this->DB = ItaDB::DBOpen($this->recBgdAdassm['DBNAME'], strlen($this->recBgdAdassm['DBSUFFIX']) == 0 ? $this->recBgdAdassm['DBSUFFIX'] : '');
            } catch (Exception $e) {
                Out::msgStop("ERRORE CONNESSIONE DB", $e->getCode() . ' - ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }
    
    private function impostaGridDoc($filtri) {        
        // Apertura connessione con il db indicato nella tabella BGD_ADASSM
        if (!$this->apriDB()) {
            return;
        }
        
        $gridName = $this->nameForm . '_gridDocumenti';
        $sql = $this->workingClass->getSqlCarica($filtri);
        $ita_grid01 = new TableView($gridName, array(
            'sqlDB' => $this->DB,
            'sqlQuery' => $sql));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);                
        $pageRows = isset($_POST['rows']) ? $_POST['rows'] : $_POST[$gridName]['gridParam']['rowNum'];
        $ita_grid01->setPageRows($pageRows ? $pageRows : 20);                   
        
        if (!$this->getDataPage($ita_grid01, $this->workingClass->elaboraGrid($ita_grid01))) {
            Out::msgStop("Selezione", "Nessun record trovato.");            
        } else {   
            TableView::enableEvents($gridName);
        }
    }
    
    private function getDataPage($ita_grid, $Result_tab) {
        if ($Result_tab == null) {
            return $ita_grid->getDataPage('json');
        } else {
            return $ita_grid->getDataPageFromArray('json', $Result_tab);
        }
    }
    
    private function initCombo() {        
        $this->initComboAree();
        $this->initComboModuli('');
        $this->initComboTipoDoc('', '');
        $this->initComboEnti($progente);
        $this->initComboAOO($progente);
    }
    
    private function initComboAree() {
        
        // Azzera combo
        Out::html($this->nameForm . '_CODAREAMA', '');

        // Carica lista aree
        $aree = $this->libDB_BGD->getAreeBgdAdmcnf(array(
            'F_GESTDOC' => $this->recBgdParott['F_GESTDOC']
        ));
        
        // Popola combo in funzione dei dati caricati da db
        Out::select($this->nameForm . '_CODAREAMA', 1, '', 1, "--- TUTTE ---");                
        foreach ($aree as $area) {
            Out::select($this->nameForm . '_CODAREAMA', 1, $area['CODAREAMA'], 0, trim($area['CODAREAMA'] . ' - ' . $area['DESAREA']));        
        }                
    }
    
    private function initComboModuli($area) {
                
        // Azzera combo
        Out::html($this->nameForm . '_CODMODULO', '');

        // Aggiungi voce 'TUTTI'
        Out::select($this->nameForm . '_CODMODULO', 1, '', 1, "--- TUTTI ---");
        
        // Se area corrente non valorizzata, esce
        if ($area == '') {
            return;
        }
        
        // Carica lista moduli
        $moduli = $this->libDB_BGD->getModuliBgdAdmcnf(array(
            'F_GESTDOC' => $this->recBgdParott['F_GESTDOC'],
            'CODAREAMA' => $area
        ));
        
        // Popola combo in funzione dei dati caricati da db        
        foreach ($moduli as $modulo) {
            Out::select($this->nameForm . '_CODMODULO', 1, $modulo['CODMODULO'], 0, trim($modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO']));            
        }                
    }
    
    private function initComboTipoDoc($area, $modulo) {
                
        // Azzera combo
        Out::html($this->nameForm . '_TIPO_DOC', '');
        
        // Se area e modulo non valorizzati, esce
        if ($area == '' || $modulo == '') {
            return;
        }
        
        // Carica lista documenti selezionabili
        $tipiDoc = $this->libDB_BGD->leggiBgdAdassm(array(
            'CODAREAMA' => $area,
            'CODMODULO' => $modulo
        ), 'TIPO_DOC');
        
        // Popola combo in funzione dei dati caricati da db        
        foreach ($tipiDoc as $tipoDoc) {
            Out::select($this->nameForm . '_TIPO_DOC', 1, $tipoDoc['IDADASSM'], 0, $tipoDoc['TIPO_DOC']);            
        }                
    }
    
    private function initComboEnti(&$progenteSel) {
        
        // Azzera combo
        Out::html($this->nameForm . '_ENTE', '');

        // Carica lista aree
        $enti = $this->libDB_BOR->leggiBorEnti(array());
        
        // Imposta progente di default
        $progenteSel = count($enti) > 0 ? $enti[0]['PROGENTE'] : 0;        
        
        // Popola combo in funzione dei dati caricati da db
        $default = 1;
        foreach ($enti as $ente) {            
            Out::select($this->nameForm . '_ENTE', 1, $ente['CODENTE'], $default, trim($ente['CODENTE'] . ' - ' . $ente['DESENTE']));                    
            $default = 0;
        }                
        
        if (count($enti) > 0) {
            $this->codiceEnte = $enti[0]['CODENTE'];
        }
    }
    
    private function initComboAOO($ente) {        
        
        // Azzera combo
        Out::html($this->nameForm . '_AOO', '');
        
        // Se ente non valorizzato, esce
        if ($ente == '') {
            return;
        }
        
        // Carica lista aree
        $aree = $this->libDB_BOR->leggiBorAoo(array(
            'PROGENTE' => $ente
        ));
        
        // Popola combo in funzione dei dati caricati da db
        $default = 1;
        foreach ($aree as $aoo) {
            Out::select($this->nameForm . '_AOO', 1, $aoo['IPA_CODAMM'], 0, trim($aoo['IPA_CODAMM'] . ' - ' . $aoo['DESAOO']));        
            $default = 0;
        }             
        
        if (count($aree) > 0) {
            $this->codiceAOO = $aree[0]['IPA_CODAMM'];
        }
    }
    
}

?>