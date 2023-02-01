<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontControllerCW.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbParGen.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';
include_once ITA_BASE_PATH . '/apps/Cep/cepHelper.class.php';

function menCwbConfig() {
    $menCwbConfig = new menCwbConfig();
    $menCwbConfig->parseEvent();
    return;
}

class menCwbConfig extends itaFrontControllerCW {
    function __construct($nameFormOrig=null, $nameForm=null) {
        if(!isSet($nameForm) || !isSet($nameFormOrig)){
            $nameFormOrig = 'menCwbConfig';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch($_POST['event']){
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_Applica':
                        if (itaHooks::isActive('citywareHook.php')) {                                                        
                            $anno = $this->formData[$this->nameForm . '_anno'];
                            $data = $this->formData[$this->nameForm . '_dataElaborazione'];
                            $ente = $this->formData[$this->nameForm . '_ente'];
                            $modOrg = $this->formData[$this->nameForm .'_modorg'];
                        } else {
                            $anno = $this->formData[$this->nameForm . '_cepEsercizio'];
                            $ente = $this->formData[$this->nameForm . '_cepEnte'];
                        }
                        
                        $msg = $this->getMessaggiValidazione($anno, $data, $ente, $modOrg);
                        if (strlen($msg) > 0) {
                            Out::msgStop('Errore di validazione', $msg);
                            return;
                        }
                        $this->confermaModifica();
                        break;
                    case $this->nameForm . '_ConfermaApplica':                                                
                        if (itaHooks::isActive('citywareHook.php')) {                                                        
                            $anno = $this->formData[$this->nameForm . '_anno'];
                            $data = $this->formData[$this->nameForm . '_dataElaborazione'];
                            $ente = $this->formData[$this->nameForm . '_ente'];
                            $modOrg = $this->formData[$this->nameForm .'_modorg'];
                            $ruolo = $this->formData[$this->nameForm . '_kruolo'];
                        } else {
                            $anno = $this->formData[$this->nameForm . '_cepEsercizio'];
                            $ente = $this->formData[$this->nameForm . '_cepEnte'];
                        }                                               
                        $this->closeOpenTabs();
                        $this->setData($anno,$data,$ente,$modOrg,$ruolo);
                        $this->closeDialog();
                        break;
                    case $this->nameForm . '_Info':
                        if(empty($this->formData[$this->nameForm . '_kruolo'])){
                            Out::msgError('Errore', 'Selezionare un ruolo per vedere le strutture organizzative collegate');
                        }
                        else{
                            $externalParams = array(
                                'KEY_CODUTE'=>array(
                                    'VALORE'=>strtoupper(trim(cwbParGen::getUtente())),
                                    'PERMANENTE'=>true
                                ),
                                'KRUOLO'=>array(
                                    'VALORE'=>$this->formData[$this->nameForm . '_kruolo'],
                                    'PERMANENTE'=>true
                                ),
                                'ATTIVO'=>array(
                                    'VALORE'=>true,
                                    'PERMANENTE'=>true
                                )
                            );
                            $model = cwbLib::apriFinestra('cwbBorUteorg', $this->nameForm, null, null, $externalParams, $this->nameFormOrig, '', array('forceReadOnly'=>true));
                            $model->parseEvent();
                        }
                        break;
                    case $this->nameForm . '_cepEnte_butt':
                        $this->selezionaEnte();
                        break;
                }
                break;
            case 'onChange':
                switch($_POST['id']){
                    case $this->nameForm . '_cepEnte':
                        $this->cambiaEnte($_POST[$this->nameForm . '_cepEnte']);
                        break;
                }
                break;
            case 'selezionaEnte':
                $this->confermaSelezionaEnte();
                break;
        }
    }
    
    private function getMessaggiValidazione($anno, $data, $ente, $modOrg) {
        $msg = '';
        if (itaHooks::isActive('citywareHook.php')) {                                        
            
            if (!$anno) {
                $msg .= 'Anno contabile non impostato.<br>';
            }
            if (!$data) {
                $msg .= 'Data registrazione non impostata.<br>';
            }
            if (!$ente) {
                $msg .= 'Ente non impostato.<br>';
            }
            if (!$modOrg) {
                $msg .= 'Modello organizzativo non impostato.<br>';
            }
        } else {            
            if (!$anno) {
                $msg .= 'Anno contabile non impostato.<br>';
            }            
            if (!$ente) {
                $msg .= 'Ente non impostato.<br>';
            }                       
        }
        return $msg;            
    }

    
    private function setData($anno,$data,$ente,$modOrg,$ruolo){
        if (itaHooks::isActive('citywareHook.php')) {
            cwbParGen::setAnnoContabile($anno);
            cwbParGen::setDataElaborazione($data);

            $enti = cwbParGen::getBorEnti();
            $desente = '';
            foreach($enti as $row){
                if($row['PROGENTE'] == $ente){
                    cwbParGen::setProgente($row['PROGENTE']);
                    cwbParGen::setCodente($row['CODENTE']);
                    cwbParGen::setDesente($row['DESENTE']);
                    $desente = $row['DESENTE'];
                    cwbParGen::setCodlocalEnte(substr($row['CODENTE'],3,3));
                    cwbParGen::setCodnazproEnte(substr($row['CODENTE'],0,3));

                    break;
                }
            }

            cwbParGen::setModelloOrganizzativo($modOrg);
            cwbParGen::setRuolo($ruolo);

            cwbLib::rewriteHeader();

            $authHelper = new cwbAuthHelper();
            $authHelper->clearAuthCache();
        }
        else {
            $enteRec = $this->cambiaEnte($_POST[$this->nameForm . '_cepEnte']);
            cepHelper::setHeaderTitle($enteRec['DESCRIZIONE'], $anno);
        }
        App::$utente->setKey('cepEnte', $ente);
        App::$utente->setKey('cepEsercizio', $anno);
    }
    
    private function init(){        
        if (itaHooks::isActive('citywareHook.php')) {
            Out::show($this->nameForm . '_divCityware');
            Out::hide($this->nameForm . '_divStandard');
            
            Out::valore($this->nameForm . '_anno',cwbParGen::getAnnoContabile());
            Out::valore($this->nameForm . '_dataElaborazione',cwbParGen::getDataElaborazione());

            $enti = cwbParGen::getBorEnti();
            $enteAttuale = cwbParGen::getProgEnte();
            Out::html($this->nameForm . '_ente', '');
            foreach($enti as $ente){
                Out::select($this->nameForm . '_ente', 1, $ente['PROGENTE'], $ente['PROGENTE'] == $enteAttuale, $ente['DESENTE']);
            }

            $dbLib = new cwbLibDB_BOR();
            $filtri = array(
                'PROGENTE' => $enteAttuale
            );
            $modelliOrganizzativi = $dbLib->leggiBorModorg($filtri);
            $modorgAttuale = cwbParGen::getModelloOrganizzativo();
            Out::html($this->nameForm . '_modorg', '');
            foreach($modelliOrganizzativi as $modorg){
                Out::select($this->nameForm . '_modorg', 1, $modorg['IDMODORG'], $modorg['IDMODORG'] == $modorgAttuale, $modorg['DESCRIZ']);
            }
        
            $filtri = array(
                'CODUTE'=>strtoupper(trim(cwbParGen::getUtente()))
            );
            $modoGesaut = $dbLib->leggiModoGesaut($filtri, false);
            if($modoGesaut['MODO_GESAUT'] == 1){
                Out::show($this->nameForm . '_divRuolo');
            
                $filtri = array(
                    'CODUTE'=>strtoupper(trim(cwbParGen::getUtente())),
                    'DATAINIZ_lt_eq'=>date('Ymd'),
                    'DATAFINE_gt'=>date('Ymd'),
                    'DATAFINE_null_or'=>true
                );
                $ruoli = $dbLib->leggiBorRuoliFromUteorg($filtri);
                $ruoloAttuale = cwbParGen::getRuolo();
                Out::html($this->nameForm . '_kruolo', '');

                Out::select($this->nameForm . '_kruolo', 1, 0, true, 'Nessun ruolo selezionato');
                foreach($ruoli as $ruolo){
                    Out::select($this->nameForm . '_kruolo', 1, $ruolo['KRUOLO'], $ruolo['KRUOLO'] == $ruoloAttuale, $ruolo['KRUOLO'] . ' - ' . $ruolo['DES_RUOLO']);
                }
            }
            else{
                Out::hide($this->nameForm . '_divRuolo');
            }
        } else {
            Out::hide($this->nameForm . '_divCityware');
            Out::show($this->nameForm . '_divStandard');
            
            $esercizio = App::$utente->getKey('cepEsercizio');
            if (!$esercizio) {
                $esercizio = date('Y');
            }
            Out::valore($this->nameForm . '_cepEsercizio', $esercizio);
            Out::valore($this->nameForm . '_cepEnte', App::$utente->getKey('cepEnte'));
            $this->cambiaEnte(App::$utente->getKey('cepEnte'));
        }        
    }
    
    private function confermaModifica(){
        Out::msgQuestion("Modifica parametri", "La modifica dei parametri comporterà la chiusura di tutte le finestre influenzate da questi. Proseguire?", array(
            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaApplica', 'model' => $this->nameForm, 'shortCut' => "f8"),
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaApplica', 'model' => $this->nameForm, 'shortCut' => "f5")
        ));
    }
    
    private function closeDialog(){
        $this->close();
        Out::closeDialog($this->nameForm);
    }
    
    private function closeOpenTabs(){
        foreach(cwbParGen::getOpenDetailFlags() as $model){
            $obj = itaFrontController::getInstance($model['model'], $model['alias']);
            if($obj){
                $obj->close();
            }
            cwbParGen::removeOpenDetailFlag($model['alias'], $model['model']);
        }        
    }
    
    private function selezionaEnte() {
        include_once ITA_BASE_PATH . '/apps/Cep/cepLib.class.php';
        include_once ITA_BASE_PATH . '/apps/Cep/cepRic.class.php';
        include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
        $CepLib = new cepLib;
        $AccLib = new accLib;
        $CepRic = new cepRic();
        $Utente_rec = $AccLib->getUtenti(App::$utente->getKey('nomeUtente'), 'utelog');
        $UtenteCep_rec = $CepLib->getUtentiCep($Utente_rec['UTECOD'], 'utecod', false);
        if ($UtenteCep_rec) {
            $Ente = $CepLib->getEnti($UtenteCep_rec['T_ENTE_ID'], 'id');
            if ($Ente['CODICE'] == "0000") {
                $CepRic->cepEnti($this->nameForm, 'selezionaEnte');
            } else {
                Out::valore($this->nameForm . '_cepEnte', $Ente['T_ENTE_ID']);
                Out::valore($this->nameForm . '_cepEnteDecod', strtoupper($Ente['DESCRIZIONE']));
            }
        } else {
            Out::msgInfo("", "Utente Non Abilitato");            
        }
    }
    
    private function confermaSelezionaEnte() {        
        $this->cambiaEnte($_POST['retKey']);
    }
    
    private function cambiaEnte($ente) {
        include_once ITA_BASE_PATH . '/apps/Cep/cepLib.class.php';
        $CepLib = new cepLib;
        $Ente_rec = $CepLib->getEnti($ente, 'id');
        Out::valore($this->nameForm . '_cepEnte', $Ente_rec['T_ENTE_ID']);
        Out::valore($this->nameForm . '_cepEnteDecod', strtoupper($Ente_rec['DESCRIZIONE']));
        return $Ente_rec;
    }
        
}
