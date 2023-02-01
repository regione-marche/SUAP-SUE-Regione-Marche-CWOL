<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';


function cwbDuplicaAutorizzazioni() {
    $cwbDuplicaAutorizzazioni = new cwbDuplicaAutorizzazioni();
    $cwbDuplicaAutorizzazioni->parseEvent();
    return;
}

class cwbDuplicaAutorizzazioni extends cwbBpaGenTab {
    
    private $tipoRicerca;
    private $utente;
    private $ruolo;
    private $aree;
    private $moduliCaricati;
    private $moduliSelezionati;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbDuplicaAutorizzazioni';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    protected function initVars() {
        $this->noCrud = true;
        
        $this->libDB = new cwbLibDB_BOR();
        $this->libDB_GENERIC = new cwbLibDB_GENERIC();
        
        $this->tipoRicerca = App::$utente->getKey($this->nameForm . '_tipoRicerca');
        $this->utente = App::$utente->getKey($this->nameForm . '_utente');
        $this->ruolo = App::$utente->getKey($this->nameForm . '_ruolo');
        $this->aree = App::$utente->getKey($this->nameForm . '_aree');
        $this->moduliCaricati = App::$utente->getKey($this->nameForm . '_moduliCaricati');
        $this->moduliSelezionati = App::$utente->getKey($this->nameForm . '_moduliSelezionati');
    }
    
    protected function preDestruct() {
        parent::preDestruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_tipoRicerca', $this->tipoRicerca);
            App::$utente->setKey($this->nameForm . '_utente', $this->utente);
            App::$utente->setKey($this->nameForm . '_ruolo', $this->ruolo);
            App::$utente->setKey($this->nameForm . '_aree', $this->aree);
            App::$utente->setKey($this->nameForm . '_moduliCaricati', $this->moduliCaricati);
            App::$utente->setKey($this->nameForm . '_moduliSelezionati', $this->moduliSelezionati);
        }
    }
    
    
    public function initializeForm() {
        Out::hide($this->nameForm . '_spanModuliDaDuplicare');
        Out::hide($this->nameForm . '_divModuli');
        
        switch ($this->tipoRicerca) {
            case 1:
                Out::show($this->nameForm . '_divRicUtenteOrigine');
                Out::show($this->nameForm . '_divRicUtenteDestinazione');
                Out::hide($this->nameForm . '_divRicRuoloOrigine');
                Out::hide($this->nameForm . '_divRicRuoloDestinazione');
                if($this->utente) {
                    $this->setUtenteSearch($this->utente, 1);
                }
                break;
            case 2:
                Out::show($this->nameForm . '_divRicRuoloOrigine');
                Out::show($this->nameForm . '_divRicRuoloDestinazione');
                Out::hide($this->nameForm . '_divRicUtenteOrigine');
                Out::hide($this->nameForm . '_divRicUtenteDestinazione');
                if($this->ruolo) {
                    $this->setRuoloSearch($this->ruolo, 1);
                }
                break;
        }
        
        $this->aree = $this->caricaAree();
        
        Out::html($this->nameForm . '_AREE','');
        Out::select($this->nameForm . '_AREE', 1, '', false, 'TUTTE');
        
        foreach ($this->aree as $key => $area) {
            Out::select($this->nameForm . '_AREE', 1, $area['CODAREAMA'], false, $area['DESAREA']);
        }
    }
    
    private function caricaAree() {
        $aree = $this->libDB->leggiBorMaster();
        if($aree) {
            // Sposto l'area Base in prima posizione
            $areaBase = null;
            $posAreaBase = 0;
            foreach ($aree as $key => $area) {
                if($area['CODAREAMA'] == 'B') {
                    $posAreaBase = $key;
                    $areaBase = $area;
                    break;
                }
            }
            if($posAreaBase > 0) {
                $aree[$posAreaBase] = $aree[0];
                $aree[0] = $areaBase;
            }
            
            // Rimuove le aree senza moduli e autorizzazioni definite
            foreach ($aree as $key => $area) {
                $count = $this->libDB->countBorDesautJoinModuliPerArea($area['CODAREAMA']);
                if($count == 0) {
                    unset($aree[$key]);
                }
            }
        } else {
            Out::msgStop('ATTENZIONE', 'Impossibile caricare le aree di Cityware.');
        }
        
        return $aree;
    }
    
    
    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_btnDuplica':
                        $this->duplica();
                        break;
                    case $this->nameForm . '_CODUTE_ORIG_butt':
                        cwbLib::apriFinestraRicerca('cwbBorUtenti', $this->nameForm, 'returnBorUtentiOrig', 'CODUTE', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_CODUTE_DEST_butt':
                        cwbLib::apriFinestraRicerca('cwbBorUtenti', $this->nameForm, 'returnBorUtentiDest', 'CODUTE', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_KRUOLO_ORIG_butt':
                        cwbLib::apriFinestraRicerca('cwbBorRuoli', $this->nameForm, 'returnBorRuoliOrig', 'KRUOLO', true, null, $this->nameFormOrig);
                        break;
                    case $this->nameForm . '_KRUOLO_DEST_butt':
                        cwbLib::apriFinestraRicerca('cwbBorRuoli', $this->nameForm, 'returnBorRuoliDest', 'KRUOLO', true, null, $this->nameFormOrig);
                        break;
                }
                if (preg_match('/' . 'MODULO_([0-9A-Z]{1,3})/', $_POST['id'], $matches)) {
                    $modulo = $matches[1];
                    $this->onClickmodulo($modulo);
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODUTE_ORIG':
                        $this->setUtenteSearch(trim($_POST[$this->nameForm . '_CODUTE_ORIG']), 1);
                        break;
                    case $this->nameForm . '_CODUTE_DEST':
                        $this->setUtenteSearch(trim($_POST[$this->nameForm . '_CODUTE_DEST']), 2);
                        break;
                    case $this->nameForm . '_KRUOLO_ORIG':
                        $this->setRuoloSearch(trim($_POST[$this->nameForm . '_KRUOLO_ORIG']), 1);
                        break;
                    case $this->nameForm . '_KRUOLO_DEST':
                        $this->setRuoloSearch(trim($_POST[$this->nameForm . '_KRUOLO_DEST']), 2);
                        break;
                    case $this->nameForm . '_AREE':
                        $this->onChangeArea();
                        break;
                }
                break;
            case 'returnBorUtentiOrig':
                $this->setUtenteSearch($this->formData['returnData']['CODUTE'], 1);
                break;
            case 'returnBorUtentiDest':
                $this->setUtenteSearch($this->formData['returnData']['CODUTE'], 2);
                break;
            case 'returnBorRuoliOrig':
                $this->setRuoloSearch($this->formData['returnData']['KRUOLO'], 1);
                break;
            case 'returnBorRuoliDest':
                $this->setRuoloSearch($this->formData['returnData']['KRUOLO'], 2);
                break;
        }
    }
    
    
    private function setUtenteSearch($codute, $tipo) {
        switch ($tipo) {
            case 1:
                $coduteField = 'CODUTE_ORIG';
                $nomeuteField = 'NOMEUTE_ORIG';
                break;
            case 2:
                $coduteField = 'CODUTE_DEST';
                $nomeuteField = 'NOMEUTE_DEST';
                break;
        }
        
        
        $codute = trim($codute);
        if(strlen($codute) > 0) {
            $utente = $this->libDB->leggiBorUtentiChiave($codute);

            if($utente) {
                Out::valore($this->nameForm . '_' . $coduteField, $utente['CODUTE']);
                Out::valore($this->nameForm . '_' . $nomeuteField, $utente['NOMEUTE']);
            } else {
                Out::valore($this->nameForm . '_' . $coduteField, null);
                Out::valore($this->nameForm . '_' . $nomeuteField, null);
            }
        } else {
            Out::valore($this->nameForm . '_' . $coduteField, null);
            Out::valore($this->nameForm . '_' . $nomeuteField, null);
        }
    }
    
    private function setRuoloSearch($kruolo, $tipo) {
        switch ($tipo) {
            case 1:
                $kruoloField = 'KRUOLO_ORIG';
                $desruoloField = 'DES_RUOLO_ORIG';
                break;
            case 2:
                $kruoloField = 'KRUOLO_DEST';
                $desruoloField = 'DES_RUOLO_DEST';
                break;
        }
        
        if($kruolo > 0) {
            $ruolo = $this->libDB->leggiBorRuoliChiave($kruolo);

            if($ruolo) {
                Out::valore($this->nameForm . '_' . $kruoloField, $ruolo['KRUOLO']);
                Out::valore($this->nameForm . '_' . $desruoloField, $ruolo['DES_RUOLO']);
            } else {
                Out::valore($this->nameForm . '_' . $kruoloField, null);
                Out::valore($this->nameForm . '_' . $desruoloField, null);
            }
        } else {
            Out::valore($this->nameForm . '_' . $kruoloField, null);
            Out::valore($this->nameForm . '_' . $desruoloField, null);
        }
    }
    
    private function onChangeArea() {
        $area = $_POST[$this->nameForm . '_AREE'];
        
        if($area) {
            $this->moduliSelezionati = array();
            $this->generaModuliPerArea($area);            
        } else {
            Out::hide($this->nameForm . '_spanModuliDaDuplicare');
            Out::hide($this->nameForm . '_divModuli');
        }
    }
    
    private function generaModuliPerArea($area) {
        $filtri = array();
        $filtri['CODAREAMA'] = $area;
        $moduli = $this->libDB->leggiBorModuli($filtri);
        
        if($moduli) {
            $this->moduliCaricati = $moduli;
            
            Out::show($this->nameForm . '_spanModuliDaDuplicare');
            Out::show($this->nameForm . '_divModuli');
            
            $html = '<div style="overflow:scroll; height:200px;">';
            
            $id = 'MODULO_' . 0;
            $htmlModulo = '<input type="checkbox" class="ita-checkbox ui-widget-content ui-corner-all"></input>';
            $htmlModulo = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $htmlModulo, null, null);
            $htmlModulo .= '<label for="' . $id . '">' . 'TUTTI' . '</label></br>';
            $html .= $htmlModulo;
            
            foreach ($moduli as $key => $modulo) {
                $id = 'MODULO_' . $modulo['CODMODULO'];
                $htmlModulo = '<input type="checkbox" class="ita-checkbox ui-widget-content ui-corner-all"></input>';
                $htmlModulo = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $htmlModulo, null, null);
                $htmlModulo .= '<label for="' . $id . '">' . $modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO'] . '</label></br>';
                $html .= $htmlModulo;
            }

            $html .= '</div>';

            Out::html($this->nameForm . '_divModuli', $html);
        } else {
            Out::hide($this->nameForm . '_spanModuliDaDuplicare');
            Out::hide($this->nameForm . '_divModuli');
        }
    }
    
    private function htmlModulo($modulo, $checked) {
        $html = '<input type="checkbox" ' . ($checked ? 'checked' : '') . ' class="ita-checkbox ui-widget-content ui-corner-all"></input>';
        Out::html('MODULO_' . $modulo, $html);
    }
    
    private function onClickmodulo($modulo) {
        if($modulo === '0') {
            if(!$this->moduliSelezionati[0]) {
                $this->moduliSelezionati[0] = true;
                
                foreach ($this->moduliCaricati as $key => $moduloCaricato) {
                    $this->moduliSelezionati[$moduloCaricato['CODMODULO']] = true;
                    
                    $this->htmlModulo($moduloCaricato['CODMODULO'], true);
                }
            } else {
                $this->moduliSelezionati = array();
                
                foreach ($this->moduliCaricati as $key => $moduloCaricato) {
                    $this->htmlModulo($moduloCaricato['CODMODULO'], false);
                }
            }
        } else {
            $this->moduliSelezionati[0] = false;
            $this->htmlModulo(0, false);
            
            if($this->moduliSelezionati[$modulo]) {
                unset($this->moduliSelezionati[$modulo]);
            } else {
                $this->moduliSelezionati[$modulo] = true;
            }
        }
    }
    
    
    private function controllaCampiPerDuplica() {
        $errori = array();
        
        if($this->tipoRicerca == 1) {
            if(!$_POST[$this->nameForm . '_CODUTE_ORIG']) {
                array_push($errori, "Selezionare l'utente di origine");
            }
            if(!$_POST[$this->nameForm . '_CODUTE_DEST']) {
                array_push($errori, "Selezionare l'utente di destinazione");
            }
            if($_POST[$this->nameForm . '_CODUTE_ORIG'] === $_POST[$this->nameForm . '_CODUTE_DEST']) {
                array_push($errori, "L'utente di origine e quello di destinazione devono essere diversi.");
            }
        } else {
            if(!$_POST[$this->nameForm . '_KRUOLO_ORIG']) {
                array_push($errori, "Selezionare il ruolo di origine");
            }
            if(!$_POST[$this->nameForm . '_KRUOLO_DEST']) {
                array_push($errori, "Selezionare il ruolo di destinazione");
            }
            if($_POST[$this->nameForm . '_KRUOLO_ORIG'] === $_POST[$this->nameForm . '_KRUOLO_DEST']) {
                array_push($errori, "Il ruolo di origine e quello di destinazione devono essere diversi.");
            }
        }
        
        $areaSelezionata = $_POST[$this->nameForm . '_AREE'];
        if($areaSelezionata) {
            if(count($this->moduliSelezionati) > 0) {
                $selezioneModuliOk = false;
                foreach ($this->moduliSelezionati as $key => $modulo) {
                    if($modulo) {
                        $selezioneModuliOk = true;
                        break;
                    }
                }
                
                if(!$selezioneModuliOk) {
                    array_push($errori, "Selezionare i moduli da duplicare.");
                }
            } else {
                array_push($errori, "Selezionare i moduli da duplicare.");
            }
        }
        
        if(!empty($errori)) {
            $msg = '';
            for($i = 0; $i < count($errori); $i++) {
                if($i > 0) {
                    $msg .= '</br>';
                }
                $msg .= $errori[$i];
            }
            Out::msgStop('ATTENZIONE', $msg);
        }
        
        return empty($errori);
    }
    
    private function duplica() {
        if($this->controllaCampiPerDuplica()) {
            
            $recordInseritiAggiornati = 0;
            
            switch($this->tipoRicerca) {
                case 1:
                    $tabella = 'BOR_AUTUTE';
                    $field = 'CODUTE';
                    break;
                case 2:
                    $tabella = 'BOR_AUTRUO';
                    $field = 'KRUOLO';
                    break;
            }
            
            $messaggio = 'Aggiornamento autorizzazioni';
            $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tabella), false, false);
            
            $listaAreeDaDuplicare = array();
            
            $areaSelezionata = $_POST[$this->nameForm . '_AREE'];
            if($areaSelezionata) {
                array_push($listaAreeDaDuplicare, array('CODAREAMA' => $areaSelezionata));
            } else {   
                $listaAreeDaDuplicare = $this->aree;
            }
            
            foreach ($listaAreeDaDuplicare as $keyArea => $area) {
                
                $moduli = array();
                
                if($this->moduliSelezionati) {
                    foreach ($this->moduliSelezionati as $keyModuloSelezionato => $moduloSelezionato) {
                        if(!empty($keyModuloSelezionato) && $moduloSelezionato) {
                            array_push($moduli, array('CODMODULO' => $keyModuloSelezionato));
                        }
                    }
                } else {
                    $filtri = array();
                    $filtri['CODAREAMA'] = $area['CODAREAMA'];
                    $moduli = $this->libDB->leggiBorModuli($filtri);
                }

                foreach ($moduli as $keyModulo => $modulo) {

                    $filtri = array();
                    $filtri[$field] = $_POST[$this->nameForm . '_' . $field . '_DEST'];
                    $filtri['CODMODULO'] = $modulo['CODMODULO'];
                    $toSave = $this->libDB_GENERIC->leggiGeneric($tabella, $filtri, false);

                    $insert = false;

                    if(!$toSave) {
                        $insert = true;
                        $toSave[$field] = $_POST[$this->nameForm . '_' . $field . '_DEST'];
                        $toSave['CODMODULO'] = $modulo['CODMODULO'];
                    }

                    if($toSave) {

                        $filtri = array();
                        $filtri[$field] = $_POST[$this->nameForm . '_' . $field . '_ORIG'];
                        $filtri['CODMODULO'] = $modulo['CODMODULO'];
                        $record = $this->libDB_GENERIC->leggiGeneric($tabella, $filtri, false);

                        if($record) {

                            $salva = true;
                            $recordModificato = false;

                            for($i = 1; $i <= 100; $i++) {
                                $prog = str_pad($i, 3, '0', STR_PAD_LEFT);
                                if(!$insert && !$_POST[$this->nameForm . '_SOST_AUT_ES']) {
                                    if(!empty($toSave['AUTUTE_' . $prog])) {
                                        $salva = false;
                                        break;
                                    }
                                }
                                if($salva && ($toSave['AUTUTE_' . $prog] <> $record['AUTUTE_' . $prog])) {
                                    $recordModificato = true;
                                    $toSave['AUTUTE_' . $prog] = $record['AUTUTE_' . $prog];
                                }
                            }

                            if($salva && $recordModificato) {
                                if($insert) {
                                    $modelService->insertRecord($this->MAIN_DB, $tabella, $toSave, $messaggio);
                                    $recordInseritiAggiornati++;
                                } else {
                                    $modelService->updateRecord($this->MAIN_DB, $tabella, $toSave, $messaggio);
                                    $recordInseritiAggiornati++;
                                }
                            }
                        }

                    }

                }

            }
            
            if($recordInseritiAggiornati) {
                Out::msgInfo('INFO', 'Record duplicati correttamente.');
            } else {
                Out::msgStop('ATTENZIONE', 'Nessun record duplicato.');
            }
        }
    }
    
    public function setTipoRicerca($tipoRicerca) {
        $this->tipoRicerca = $tipoRicerca;
    }
    
    public function setUtente($utente) {
        $this->utente = $utente;
    }
    
    public function setRuolo($ruolo) {
        $this->ruolo = $ruolo;
    }
    
}

?>