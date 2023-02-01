<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

include_once ITA_LIB_PATH . '/itaXlsxWriter/itaXlsxWriter.class.php';

function cwbStampaAutorizzazioni() {
    $cwbStampaAutorizzazioni = new cwbStampaAutorizzazioni();
    $cwbStampaAutorizzazioni->parseEvent();
    return;
}

class cwbStampaAutorizzazioni extends itaFrontController {
    
    private $areaSelezionata;
    private $aree;
    private $autorizzazioniPerModulo;
    private $autSelezionata;
    private $data;
    private $dataVis;
    private $libDB_BOR;
    private $listaUtentiRuoli;
    private $moduli;
    private $moduloSelezionato;
    private $ruoloSelezionato;
    private $tipo;
    
    
    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbStampaAutorizzazioni';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        
        parent::__construct($nameFormOrig, $nameForm);
        
        $this->load();
    }
    
    private function load() {
        $this->libDB_BOR = new cwbLibDB_BOR();
        
        $this->areaSelezionata = App::$utente->getKey($this->nameForm . '_areaSelezionata');
        $this->aree = App::$utente->getKey($this->nameForm . '_aree');
        $this->autorizzazioniPerModulo = App::$utente->getKey($this->nameForm . '_autorizzazioniPerModulo');
        $this->autSelezionata = App::$utente->getKey($this->nameForm . '_autSelezionata');
        $this->data = App::$utente->getKey($this->nameForm . '_data');
        $this->dataVis = App::$utente->getKey($this->nameForm . '_dataVis');
        $this->listaUtentiRuoli = App::$utente->getKey($this->nameForm . '_listaUtentiRuoli');
        $this->moduli = App::$utente->getKey($this->nameForm . '_moduli');
        $this->moduloSelezionato = App::$utente->getKey($this->nameForm . '_moduloSelezionato');
        $this->ruoloSelezionato = App::$utente->getKey($this->nameForm . '_ruoloSelezionato');
        $this->tipo = App::$utente->getKey($this->nameForm . '_tipo');
    }
    
    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_areaSelezionata', $this->areaSelezionata);
            App::$utente->setKey($this->nameForm . '_aree', $this->aree);
            App::$utente->setKey($this->nameForm . '_autorizzazioniPerModulo', $this->autorizzazioniPerModulo);
            App::$utente->setKey($this->nameForm . '_autSelezionata', $this->autSelezionata);
            App::$utente->setKey($this->nameForm . '_data', $this->data);
            App::$utente->setKey($this->nameForm . '_dataVis', $this->dataVis);
            App::$utente->setKey($this->nameForm . '_listaUtentiRuoli', $this->listaUtentiRuoli);
            App::$utente->setKey($this->nameForm . '_moduli', $this->moduli);
            App::$utente->setKey($this->nameForm . '_moduloSelezionato', $this->moduloSelezionato);
            App::$utente->setKey($this->nameForm . '_ruoloSelezionato', $this->ruoloSelezionato);
            App::$utente->setKey($this->nameForm . '_tipo', $this->tipo);
        }
    }
    
    protected function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_areaSelezionata');
        App::$utente->removeKey($this->nameForm . '_aree');
        App::$utente->removeKey($this->nameForm . '_autorizzazioniPerModulo');
        App::$utente->removeKey($this->nameForm . '_autSelezionata');
        App::$utente->removeKey($this->nameForm . '_data');
        App::$utente->removeKey($this->nameForm . '_dataVis');
        App::$utente->removeKey($this->nameForm . '_listaUtentiRuoli');
        App::$utente->removeKey($this->nameForm . '_moduli');
        App::$utente->removeKey($this->nameForm . '_moduloSelezionato');
        App::$utente->removeKey($this->nameForm . '_ruoloSelezionato');
        App::$utente->removeKey($this->nameForm . '_tipo');
    }
    
    
    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnGeneraExcel':
                        $this->generaExcel();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_UTENTI_RUOLI':
                        $this->onChangeUtenteRuolo();
                        break;
                    case $this->nameForm . '_AREE':
                        $this->onChangeArea();
                        break;
                    case $this->nameForm . '_MODULI':
                        $this->onChangeModulo();
                        break;
                    case $this->nameForm . '_AUTS':
                        $this->onChangeAutorizzazione();
                        break;
                }
                break;
        }
    }
    
    
    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }
    
    public function setListaUtentiRuoli($listaUtentiRuoli) {
        $this->listaUtentiRuoli = $listaUtentiRuoli;
    }
    
    
    public function loadForm() {
        Out::hide($this->nameForm . '_MODULI_lbl');
        Out::hide($this->nameForm . '_AUTS_lbl');
        switch($this->tipo) {
            case 1:
                Out::html($this->nameForm . '_UTENTI_RUOLI_lbl', 'Utenti');
                $title = 'Situazione autorizzazione per utenti';
                Out::setDialogTitle($this->nameForm, $title);
                Out::setAppSubTitle($this->nameForm, $title);
                break;
            case 2:
                Out::html($this->nameForm . '_UTENTI_RUOLI_lbl', 'Ruoli');
                $title = 'Situazione autorizzazione per ruoli';
                Out::setDialogTitle($this->nameForm, $title);
                Out::setAppSubTitle($this->nameForm, $title);
                break;
        }
        $this->loadAreeModuli();
        $this->loadFilters();
        $this->loadGrid();
    }
    
    private function loadAreeModuli() {
        $this->caricaAree();
        $this->caricaModuli();
    }
    
    private function caricaAree() {
        if(!(count($this->aree) > 0)) {
            $this->aree = $this->libDB_BOR->leggiBorMaster();
            if($this->aree) {
                // Sposto l'area Base in prima posizione
                $areaBase = null;
                $posAreaBase = 0;
                foreach ($this->aree as $key => $area) {
                    if($area['CODAREAMA'] == 'B') {
                        $posAreaBase = $key;
                        $areaBase = $area;
                        break;
                    }
                }
                if($posAreaBase > 0) {
                    $this->aree[$posAreaBase] = $this->aree[0];
                    $this->aree[0] = $areaBase;
                }

                // Rimuove le aree senza moduli e autorizzazioni definite
                foreach ($this->aree as $key => $area) {
                    $count = $this->libDB_BOR->countBorDesautJoinModuliPerArea($area['CODAREAMA']);
                    if($count == 0) {
                        unset($this->aree[$key]);
                    }
                }
            } else {
                Out::msgStop('ATTENZIONE', 'Impossibile caricare le aree di Cityware.');
            }
        }
    }
    
    private function caricaModuli() {
        if(!(count($this->moduli) > 0)) {
            $listaCodAree = array();
            foreach ($this->aree as $key => $area) {
                array_push($listaCodAree, $area['CODAREAMA']);
            }
            
            $filtri = array();
            $filtri['CODAREAMA_IN'] = $listaCodAree;
            $this->moduli = $this->libDB_BOR->leggiBorModuli($filtri, true);
        }
    }
    
    private function loadFilters() {
        Out::html($this->nameForm . '_UTENTI_RUOLI','');
        Out::select($this->nameForm . '_UTENTI_RUOLI', 1, '', false, 'TUTTI');
        foreach ($this->listaUtentiRuoli as $key => $utenteRuolo) {
            if($this->tipo == 1) {
                Out::select($this->nameForm . '_UTENTI_RUOLI', 1, $utenteRuolo, false, $utenteRuolo);
            } elseif($this->tipo == 2) {
                $bor_autruo = $this->libDB_BOR->leggiBorRuoliChiave($utenteRuolo);
                Out::select($this->nameForm . '_UTENTI_RUOLI', 1, $utenteRuolo, false, $bor_autruo['KRUOLO'] . ' - ' . $bor_autruo['DES_RUOLO']);
            }
        }
        
        Out::html($this->nameForm . '_AREE','');
        Out::select($this->nameForm . '_AREE', 1, '', false, 'TUTTE');
        foreach ($this->aree as $key => $area) {
            Out::select($this->nameForm . '_AREE', 1, $area['CODAREAMA'], false, $area['DESAREA']);
        }
    }
    
    
    private function loadGrid() {
        $aliasContainer = $this->nameForm . '_divAutorizzazioni';
            
        Out::innerHtml($aliasContainer, "");

        $model = cwbLib::innestaForm('utiJqGridCustom', $aliasContainer);
        
        //MODELLO DELLA TABELLA
        $titleKey = 'Utente';
        switch($this->tipo) {
            case 1:
                $titleKey = 'Utente';
                break;
            case 2:
                $titleKey = 'Ruolo';
                break;
        }
        
        $data = $this->generaData();
        $this->dataVis = $data;
        
        $colModel = array();
        
        foreach ($data[0] as $key => $value) {
            $title = $key;
            $align = 'left';
            if($key == 'KEY') {
                $title = $titleKey;
            } else {
                $align = 'center';
            }
            $col = array('name'=>$key, 'title'=>$title, 'class'=>"{align:'$align'}", 'width'=>'200');
            array_push($colModel, $col);
        }

        //METADATI DELLA TABELLA
        $metadata = array(
            'caption'=>'Autorizzazioni',
            'shrinkToFit'=>false,
            'width'=>1000,
            'resizeToParent'=>true,
            'rowList' => '[]',
            'rowNum'=>999,
            'reloadOnResize'=>false,
            'pgbuttons'=>false,
            'pginput'=>false,
            'navGrid'=>false
        );
        
        $model->setJqGridModel($colModel, $metadata);
        $model->setJqGridDataArray($data);

        $model->render();
    }
    
    
    private function generaData() {
        $moduli = $this->moduli;
        if($this->areaSelezionata || $this->moduloSelezionato) {
            foreach ($moduli as $key => $modulo) {
                $codmodulo = $modulo['CODMODULO'];
                if(($this->areaSelezionata && $codmodulo[0] <> $this->areaSelezionata) || 
                        ($this->moduloSelezionato && $codmodulo <> $this->moduloSelezionato)) {
                    unset($moduli[$key]);
                }
            }
        }

        foreach ($moduli as $key => $modulo) {
            $codmodulo = $modulo['CODMODULO'];
            $filtri = array();
            $filtri['CODMODULO'] = $codmodulo;
            if($this->autSelezionata > 0) {
                $filtri['PROGAUT'] = $this->autSelezionata;
            }
            if(!array_key_exists($codmodulo, $this->autorizzazioniPerModulo)) {
                $this->autorizzazioniPerModulo[$codmodulo] = $this->libDB_BOR->leggiBorDesaut($filtri, true);
            }
        }

        if($this->ruoloSelezionato) {
            $listaUtentiRuoli = array($this->ruoloSelezionato);
        } else {
            $listaUtentiRuoli = $this->listaUtentiRuoli;
        }
        
        $data = array();
        
        foreach ($listaUtentiRuoli as $keyRecord => $record) {
            
            $dataRecord = array();
            
            if($this->tipo == 2) {
                $descrizione = $this->getDesKey($record);
                if(strlen($descrizione) > 0) {
                    $dataRecord['KEY'] = $descrizione;
                } else {
                    $bor_autruo = $this->libDB_BOR->leggiBorRuoliChiave($record);
                    $dataRecord['KEY'] = $bor_autruo['KRUOLO'] . ' - ' . $bor_autruo['DES_RUOLO'];
                }
            } else {
                $dataRecord['KEY'] = $record;
            }
            
            if(count($moduli) > 0) {
                foreach ($moduli as $keyModulo => $modulo) {
                    $codmodulo = $modulo['CODMODULO'];
                    $desmodulo = $modulo['DESMODULO'];
                    
                    $autorizzazioni = $this->autorizzazioniPerModulo[$codmodulo];
                    if(count($autorizzazioni) > 0) {
                        $dataRecord[$codmodulo . ' - ' . $desmodulo] = '';
                        foreach ($autorizzazioni as $keyAut => $autorizzazione) {
                            $nomeAutorizzazione = $modulo['CODMODULO'] . ' - ' . $autorizzazione['PROGAUT'] . ' - ' . $autorizzazione['DESCRI'];
                            
                            $aut = $this->getAutorizzazionePerNome($nomeAutorizzazione, $codmodulo, $record);
                            if($aut) {
                                $dataRecord[$nomeAutorizzazione] = $aut;
                            } else {
                                $filtri = array();
                                $filtri['CODMODULO'] = $codmodulo;
                                switch($this->tipo) {
                                    case 1:
                                        $filtri['CODUTE'] = $record;
                                        $recordAut = $this->libDB_BOR->leggiBorAutute($filtri, false);
                                        break;
                                    case 2:
                                        $filtri['KRUOLO'] = $record;
                                        $recordAut = $this->libDB_BOR->leggiBorAutruo($filtri, false);
                                        break;
                                }

                                $progressivo = str_pad($autorizzazione['PROGAUT'], 3, '0', STR_PAD_LEFT);

                                $dataRecord[$nomeAutorizzazione] = $recordAut['AUTUTE_' . $progressivo];
                            }
                        }
                    }

                }
            }
            
            array_push($data, $dataRecord);
        }
        
        if(!$this->data) {
            $this->data = $data;
        }
        
        return $data;
    }
    
    
    private function getDesKey($cod) {
        $des = '';
        foreach ($this->data as $key => $data) {
            $key = $data['KEY'];
            $key = substr($key, 0, strpos($key, '-'));
            $key = trim($key);
            if($cod == $key) {
                $des = $data['KEY'];
                break;
            }
        }
        
        return $des;
    }
    
    private function getAutorizzazionePerNome($nomeAutorizzazione, $chiave) {
        $autorizzazione = '';
        foreach ($this->data as $key => $data) {
            $key = $data['KEY'];
            if($this->tipo == 2) {
                $key = substr($key, 0, strpos($key, '-'));
                $key = trim($key);
            }
            if($key == $chiave) {
                $autorizzazione = $data[$nomeAutorizzazione];
            }
        }
        
        return $autorizzazione;
    }
    
    private function onChangeUtenteRuolo() {
        $this->ruoloSelezionato = $_POST[$this->nameForm . '_UTENTI_RUOLI'];
        $this->loadGrid();
    }
    
    private function onChangeArea() {
        $this->areaSelezionata = $_POST[$this->nameForm . '_AREE'];
        $this->moduloSelezionato = null;
        
        $this->loadGrid();
        
        if($this->areaSelezionata) {
            Out::show($this->nameForm . '_MODULI_lbl');
            Out::show($this->nameForm . '_MODULI');
            
            Out::html($this->nameForm . '_MODULI','');
            Out::select($this->nameForm . '_MODULI', 1, '', false, 'TUTTI');
            
            $moduli = $this->moduli;
            foreach ($moduli as $key => $modulo) {
                if($modulo['CODAREAMA'] == $this->areaSelezionata) {
                    Out::select($this->nameForm . '_MODULI', 1, $modulo['CODMODULO'], false, $modulo['CODMODULO'] . ' - ' . $modulo['DESMODULO']);
                }
            }
        } else {
            Out::hide($this->nameForm . '_MODULI_lbl');
            Out::hide($this->nameForm . '_MODULI');
        }
    }
    
    private function onChangeModulo() {
        $this->moduloSelezionato = $_POST[$this->nameForm . '_MODULI'];
        $this->autSelezionata = null;
        
        $this->loadGrid();
        
        if($this->moduloSelezionato) {
            Out::show($this->nameForm . '_AUTS_lbl');
            Out::show($this->nameForm . '_AUTS');
            
            Out::html($this->nameForm . '_AUTS','');
            Out::select($this->nameForm . '_AUTS', 1, '', false, 'TUTTI');
            
            $auts = $this->getAutorizzazioniPerModulo($this->moduloSelezionato);
            
            foreach ($auts as $key => $aut) {
                Out::select($this->nameForm . '_AUTS', 1, $aut['PROGAUT'], false, $aut['PROGAUT'] . ' - ' . $aut['DESCRI']);
            }
        } else {
            Out::hide($this->nameForm . '_AUTS_lbl');
            Out::hide($this->nameForm . '_AUTS');
        }
    }
    
    private function onChangeAutorizzazione() {
        $this->autSelezionata = $_POST[$this->nameForm . '_AUTS'];
        
        $this->loadGrid();
    }
    
    private function getAutorizzazioniPerModulo($codmodulo) {
        $autorizzazioniPerModulo = $this->libDB_BOR->leggiBorDesautChiave($codmodulo, true);
        
        return $autorizzazioniPerModulo;
    }
    
    
    private function generaExcel() {
        $pathFile = itaLib::getAppsTempPath() . '/autorizzazioni/';
        if(!file_exists($pathFile)) {
            mkdir($pathFile, 0777);
        }
        $pathFile .= date('Ymd_Hms') . '_listaAutorizzazioni.xlsx';
        
        $this->xlsxWriter = new itaXlsxWriter();
        $this->xlsxWriter->setDataFromArray($this->dataVis);
        $metadati = $this->creaMetadatiPerExcel();
        $this->xlsxWriter->setRenderFieldsMetadata($metadati);
        $this->xlsxWriter->writeToFile($pathFile);
        
        Out::openDocument(
            utiDownload::getUrl(
                    basename($pathFile), $pathFile, false
            )
        );
    }
    
    private function creaMetadatiPerExcel() {
        $metadati = array();
        
        $row = $this->data[0];
        foreach ($row as $key => $value) {
            $metadatoRow = array();
            
            if($key == 'KEY') {
                switch ($this->tipo) {
                    case 1:
                        $metadatoRow['name'] = 'Utente';
                        break;
                    case 2:
                        $metadatoRow['name'] = 'Ruolo';
                        break;
                }
            } else {
                $metadatoRow['name'] = $key;
            }
            
            $metadatoRow['width'] = 30;
            
            $metadati[$key] = $metadatoRow;
        }
        
        return $metadati;
    }
    
}