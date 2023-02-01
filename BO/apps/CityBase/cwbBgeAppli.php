<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

function cwbBgeAppli() {
    $cwbBgeAppli = new cwbBgeAppli();
    $cwbBgeAppli->parseEvent();
    return;
}

class cwbBgeAppli extends cwbBpaGenModel {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorLivell';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->libDB = new cwbLibDB_GENERIC();
        $this->AUTOR_MODULO = 'BGE';
        $this->AUTOR_NUMERO = 8;
        
        $this->actionAfterNew = self::GOTO_DETAIL;
        $this->actionAfterModify = self::GOTO_NONE;
    }
    
    public function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComboboxes();
                $this->initOpenDetail();
                break;
        }
    }
    
    public function customParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->checkLock();
                break;
        }
    }
    
    private function checkLock(){
        if($this->LOCK['status'] != 0){
            $this->close();
        }
    }
    
    private function initComboboxes(){
        Out::html($this->nameForm . '_BGE_APPLI[MODO_GESAUT]', '');
        Out::select($this->nameForm . '_BGE_APPLI[MODO_GESAUT]', 1, 0, false, 'Da utente');
        Out::select($this->nameForm . '_BGE_APPLI[MODO_GESAUT]', 1, 1, false, 'Da ruolo indicato in associazione utente-organigramma');
    }
    
    private function initOpenDetail(){
        $bgeAppli = $this->libDB->leggiGeneric('BGE_APPLI', array('APPLI_KEY'=>'AA'), false);
        if($bgeAppli){
            $this->apriDettaglioIndex = 'AA';
        }
        else{
            $this->apriDettaglioIndex = 'new';
        }
    }

    protected function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlGeneric('BGE_APPLI', array('APPLI_KEY'=>$index), $sqlParams);
    }
}

?>