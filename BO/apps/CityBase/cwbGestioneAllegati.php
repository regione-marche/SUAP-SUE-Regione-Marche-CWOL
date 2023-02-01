<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelServiceFactory.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbAuthHelper.php';


function cwbGestioneAllegati() {
    $cwbGestioneAllegati = new cwbGestioneAllegati();
    $cwbGestioneAllegati->parseEvent();
    return;
}

class cwbGestioneAllegati extends itaFrontController {

    public $nameForm = "cwbGestioneAllegati";
    private $authLevel;
    private $datiProvenienza;
    private $datiHeader;
    private $libAllegati;
    private $CURRENT_RECORD;
    private $detailView;

    function __construct() {
        parent::__construct();
        
        $this->authLevel = cwbParGen::getFormSessionVar($this->nameForm, 'authLevel');
        $this->datiProvenienza = cwbParGen::getFormSessionVar($this->nameForm, 'datiProvenienza');
        $this->CURRENT_RECORD = cwbParGen::getFormSessionVar($this->nameForm, 'CURRENT_RECORD');
        $this->datiHeader = cwbParGen::getFormSessionVar($this->nameForm, 'datiHeader');
        $this->detailView = cwbParGen::getFormSessionVar($this->nameForm, 'detailView');
        
        if(empty($this->authLevel)){
            $this->authLevel = null;
        }
        if(empty($this->datiProvenienza)){
            $this->datiProvenienza = null;
        }
        if(empty($this->CURRENT_RECORD)){
            $this->CURRENT_RECORD = null;
        }
        if(empty($this->datiHeader)){
            $this->datiHeader = null;
        }
        if(empty($this->detailView)){
            $this->detailView = null;
        }
        
        // Istanzia libreria gestione allegati specifica
        $this->instanceLibAllegati();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'authLevel', $this->authLevel);
            cwbParGen::setFormSessionVar($this->nameForm, 'datiProvenienza', $this->datiProvenienza);
            cwbParGen::setFormSessionVar($this->nameForm, 'CURRENT_RECORD', $this->CURRENT_RECORD);
            cwbParGen::setFormSessionVar($this->nameForm, 'datiHeader', $this->datiHeader);
            cwbParGen::setFormSessionVar($this->nameForm, 'detailView', $this->detailView);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->init();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Nuovo':
                        $this->nuovo();
                        break;
                    case $this->nameForm . '_Torna':
                        $this->elenco();
                        break;
                    case $this->nameForm . '_Aggiungi':
                        $this->aggiungi();
                        break;
                    case $this->nameForm . '_Aggiorna':
                        $this->aggiorna();
                        break;
                    case $this->nameForm . '_Cancella':
                        $this->cancella();
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $this->confermaCancella();
                        break;
                    case $this->nameForm . '_Importa':
                    case $this->nameForm . '_ImportAlleg':
                        $this->importa();
                        break;
                    case $this->nameForm . '_Scarica':
                        $this->scarica();
                        break;
                    case $this->nameForm . '_Apri':
                        $this->apri();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_CaricaDocumento_upld':
                        $this->caricaDocumento();
                        break;
                    case $this->nameForm . '_ConfermaUpload':
                        $this->confermaUpload();
                        break;
                    case $this->nameForm . '_AnnullaUpload':
                        $this->annullaUpload();
                        break;
                }
                break;
            case 'dbClickRow':
                if($this->authLevel == 'L'){
                    $this->visualizza();
                }
                else{
                    $this->modifica();
                }
                break;
            case 'editGridRow':
            case 'editRowInline':
                $this->modifica();
                break;
            case 'viewRowInline':
                $this->visualizza();
                break;
            case 'addGridRow':
                $this->nuovo();
                break;
            case 'delGridRow':
                $this->cancella();
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_authLevel');
        App::$utente->removeKey($this->nameForm . '_datiProvenienza');
        App::$utente->removeKey($this->nameForm . '_currentRecord');
        App::$utente->removeKey($this->nameForm . '_datiHeader');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    private function init() {
        $this->instanceLibAllegati();
        $this->caricaAutorLevel();
        $this->iniettaComponenteHeader();
        $this->iniettaComponenteCustom();
        $this->caricaCampoSelectNaturaNote();
        $this->elenco();
    }

    private function instanceLibAllegati() {
        if ($this->datiProvenienza['CONTESTO'] === null) {
            return;
        }
        try {
            $this->libAllegati = cwbLibAllegatiFactory::getLibAllegato($this->datiProvenienza['CONTESTO']);
        } catch (Exception $ex) {
            Out::msgStop("Errore", $ex->getMessage());
            $this->close();
        }
    }
    
    private function caricaAutorLevel(){
        $this->authLevel = $this->libAllegati->getAutorLevel($this->datiProvenienza);
        
        if($this->authLevel == ''){
            Out::msgStop('Errore', 'Non si dispone delle autorizzazioni necessarie per visualizzare gli allegati');
            $this->close();
        }
        elseif($this->viewMode){
            $this->authLevel = 'L';
        }
    }

    private function iniettaComponenteHeader() {
        $nomeComponente = $this->libAllegati->getNomeComponenteHeaderCustom();
        if ($nomeComponente) {
            $this->datiHeader = $this->libAllegati->caricaDatiHeader($this->datiProvenienza['CHIAVE_TESTATA']);
            itaLib::openInner($nomeComponente, '', true, $this->nameForm . '_divComponentHeader', '', '', $nomeComponente);
            $this->libAllegati->popolaHeaderCustom($this->datiHeader, $nomeComponente);
        }
    }

    private function iniettaComponenteCustom() {
        $nomeComponente = $this->libAllegati->getNomeComponenteBodyCustom();
        if ($nomeComponente) {
            itaLib::openInner($nomeComponente, '', true, $this->nameForm . '_divComponentCustom', '', '', $nomeComponente);
            $this->libAllegati->initBodyCustom($this->libAllegati->getNomeComponenteBodyCustom());
        }
    }

    private function popolaGrigliaAllegati() {
        TableView::clearGrid($this->nameForm . '_gridGestioneAllegati');
        $ita_grid01 = new TableView($this->nameForm . '_gridGestioneAllegati', array('arrayTable' => $this->caricaAllegati(),
            'rowIndex' => 'idx'));
        $ita_grid01->setSortOrder('RIGA');
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        $ita_grid01->getDataPageFromArray('json', $this->elaboraRecords($ita_grid01->getDataArray()));
    }

    private function elaboraRecords($Result_tab) {
        if (is_array($Result_tab)) {
            foreach ($Result_tab as $key => $Result_rec) {
                $Result_tab[$key]['NOME_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['NOME']);
            }
        }
        return $Result_tab;
    }

    private function caricaAllegati() {
        $data = $this->libAllegati->caricaAllegati($this->datiProvenienza['CHIAVE_TESTATA']);
        
        // Rimuove chiavi per evitare warning in TableView
        foreach ($data as $k => $v) {
            unset($data[$k]['CHIAVE_ESTERNA']);
            unset($data[$k]['METADATI']);
            unset($data[$k]['CORPO']);
        }
        
        return $data;
    }

    private function elenco() {
        // Imposta div
        
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_divRisultato');
        
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divParametriRicerca');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Importa');
        Out::hide($this->nameForm . '_Scarica');
        Out::hide($this->nameForm . '_Apri');

        // Imposta pulsanti buttonbar
        switch($this->authLevel){
            case '':
            case 'L':
                Out::hide($this->nameForm . '_Nuovo');
                Out::hideLayoutPanel($this->nameForm . '_buttonBar');
                Out::gridHideCol($this->nameForm . '_gridGestioneAllegati', 'EDITROW');
                Out::hide($this->nameForm . "_gridGestioneAllegati_addGridRow");
                Out::hide($this->nameForm . "_gridGestioneAllegati_editGridRow");
                Out::hide($this->nameForm . "_gridGestioneAllegati_delGridRow");
                break;
            case 'G':
                Out::show($this->nameForm . "_gridGestioneAllegati_addGridRow");
                Out::show($this->nameForm . "_gridGestioneAllegati_editGridRow");
                Out::hide($this->nameForm . "_gridGestioneAllegati_delGridRow");
                Out::show($this->nameForm . '_Nuovo');
                break;
            case 'C':
                Out::show($this->nameForm . "_gridGestioneAllegati_addGridRow");
                Out::show($this->nameForm . "_gridGestioneAllegati_editGridRow");
                Out::show($this->nameForm . "_gridGestioneAllegati_delGridRow");
                Out::show($this->nameForm . '_Nuovo');
                break;
        }
        


        // Popola grig allegati
        $this->popolaGrigliaAllegati();
        
        // Rimuove variabili di sessione
        cwbParGen::removeFormSessionVar($this->nameForm, 'uplFile');
        
        Out::enableContainerFields($this->nameForm . "_workSpace");
        Out::disableContainerFields($this->nameForm . "_divComponentHeader");
        
        $this->detailView = false;
    }

    private function caricaCampoSelectNaturaNote() {
        $listaNaturaNote = $this->libAllegati->caricaNaturaNote($this->libAllegati->getChiaveNaturaNote());
        if (is_array($listaNaturaNote)) {            
            Out::select($this->nameForm . '_REC[NATURA]', 1, '', 1, '--- non specificata ---');
            foreach ($listaNaturaNote as $rowNaturaNote) {
                Out::select($this->nameForm . '_REC[NATURA]', 1, $rowNaturaNote['NATURANOTA'], 0, $rowNaturaNote['NATURANOTA'] . ' - ' . $rowNaturaNote['DESNATURA']);                
            }
        }
    }

    private function nuovo() {
        // Imposta div
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_divRisultato');

        // Imposta pulsanti buttonbar        
        Out::show($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divParametriRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Importa');
        Out::show($this->nameForm . '_Scarica');
        Out::show($this->nameForm . '_Apri');

        $this->controlliAuditDettaglio(itaModelService::OPERATION_INSERT);

        Out::hide($this->nameForm . '_REC[NODE_UUID]');
        Out::hide($this->nameForm . '_REC[NODE_UUID]_lbl');

        Out::show($this->nameForm . '_REC[NATURA]');
        Out::show($this->nameForm . '_REC[NATURA]_lbl');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]_lbl');        
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_DURCAL'){
            Out::hide($this->nameForm . '_REC[NATURA]');
            Out::hide($this->nameForm . '_REC[NATURA]_lbl');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_SOGGAL'){
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        // Pulisce record corrente
        $this->pulisciCurrentRecord();

        // Valorizza campi da current record
        $this->popolaCampiDettaglioDaCurrentRecord();
        
        Out::enableContainerFields($this->nameForm . "_workSpace");
        Out::disableContainerFields($this->nameForm . "_divComponentHeader");
        
        $this->detailView = true;
    }

    private function pulisciCurrentRecord() {
        $this->CURRENT_RECORD = $this->libAllegati->defineRowModel();
        $this->CURRENT_RECORD["CHIAVE_ESTERNA"] = $this->datiProvenienza['CHIAVE_TESTATA'];
    }

    private function caricaCurrentRecord($riga = 0) {
        if(!$this->detailView){
            $riga = ($riga === 0 ? (isset($_POST['rowid']) ? $_POST['rowid'] : null) : $riga);
            if ($riga === null) {
                Out::msgStop("Errore", "Nessun record selezionato");
                return false;
            }
            
            $this->CURRENT_RECORD = $this->libAllegati->caricaAllegato($this->datiProvenienza['CHIAVE_TESTATA'], $riga, $this->datiProvenienza['METADATI']);
        }
        else{
            $this->formDataToCurrentRecord();
        }
        
        return true;
    }

    private function popolaCampiDettaglioDaCurrentRecord() {
        // Campi standard
        Out::valore($this->nameForm . '_REC[NOME]', $this->CURRENT_RECORD['NOME']);
        Out::valore($this->nameForm . '_REC[DESCRIZIONE]', $this->CURRENT_RECORD['DESCRIZIONE']);
        Out::valore($this->nameForm . '_REC[NATURA]', $this->CURRENT_RECORD['NATURA']);
        Out::valore($this->nameForm . '_REC[ST_EXP_WEB]', $this->CURRENT_RECORD['ST_EXP_WEB']);
        Out::valore($this->nameForm . '_REC[NODE_UUID]', $this->CURRENT_RECORD['NODE_UUID']);
        Out::valore($this->nameForm . '_REC[FLAG_DIS]', $this->CURRENT_RECORD['FLAG_DIS']);

        // Campi audit
        Out::valore($this->nameForm . '_REC[CODUTEINS]', $this->CURRENT_RECORD['CODUTEINS']);
        Out::valore($this->nameForm . '_REC[DATAINSER]', $this->CURRENT_RECORD['DATAINSER']);
        Out::valore($this->nameForm . '_REC[TIMEINSER]', $this->CURRENT_RECORD['TIMEINSER']);
        Out::valore($this->nameForm . '_REC[CODUTE]', $this->CURRENT_RECORD['CODUTE']);
        Out::valore($this->nameForm . '_REC[DATAOPER]', $this->CURRENT_RECORD['DATAOPER']);
        Out::valore($this->nameForm . '_REC[TIMEOPER]', $this->CURRENT_RECORD['TIMEOPER']);

        // Campi custom
        $this->libAllegati->popolaBodyCustom($this->CURRENT_RECORD['METADATI'], $this->libAllegati->getNomeComponenteBodyCustom());
    }

    private function formDataToCurrentRecord() {
        // Campi standard
        $this->CURRENT_RECORD["NOME"] = $_POST[$this->nameForm . '_REC']['NOME'];
        $this->CURRENT_RECORD["DESCRIZIONE"] = $_POST[$this->nameForm . '_REC']['DESCRIZIONE'];
        $this->CURRENT_RECORD["NATURA"] = $_POST[$this->nameForm . '_REC']['NATURA'];
        $this->CURRENT_RECORD["ST_EXP_WEB"] = $_POST[$this->nameForm . '_REC']['ST_EXP_WEB'];
        $this->CURRENT_RECORD["NODE_UUID"] = $_POST[$this->nameForm . '_REC']['NODE_UUID'];
        $this->CURRENT_RECORD["FLAG_DIS"] = $_POST[$this->nameForm . '_REC']['FLAG_DIS'];

        $this->CURRENT_RECORD["CODUTEINS"] = $_POST[$this->nameForm . '_REC']['CODUTEINS'];
        $this->CURRENT_RECORD["DATAINSER"] = $_POST[$this->nameForm . '_REC']['DATAINSER'];
        $this->CURRENT_RECORD["TIMEINSER"] = $_POST[$this->nameForm . '_REC']['TIMEINSER'];
        $this->CURRENT_RECORD["CODUTE"] = $_POST[$this->nameForm . '_REC']['CODUTE'];
        $this->CURRENT_RECORD["DATAOPER"] = $_POST[$this->nameForm . '_REC']['DATAOPER'];
        $this->CURRENT_RECORD["TIMEOPER"] = $_POST[$this->nameForm . '_REC']['TIMEOPER'];        

        $fileURI = cwbParGen::getFormSessionVar($this->nameForm, 'uplFile');
        //Se ho fatto importa mi trovo in sessione il file 
        if ($fileURI) {
            $this->CURRENT_RECORD['CORPO'] = file_get_contents($fileURI);
            if (!$this->CURRENT_RECORD['CORPO']) {
                $this->CURRENT_RECORD['CORPO'] = null;
            }
        }
        if(empty($fileURI) && empty($this->CURRENT_RECORD['NODE_UUID'])){
            $allegato = $this->libAllegati->caricaAllegato($this->datiProvenienza['CHIAVE_TESTATA'], $this->CURRENT_RECORD['RIGA'], $this->datiProvenienza['METADATI']);
            $this->CURRENT_RECORD['CORPO'] = $allegato['CORPO'];
        }
        // Componente custom da normalizzare
        if ($this->libAllegati->getNomeComponenteBodyCustom()) {
            $this->CURRENT_RECORD["METADATI"] = $_POST[$this->libAllegati->getNomeComponenteBodyCustom() . '_CUSTOM'];
        }
    }

    private function modifica() {
        $status = $this->caricaCurrentRecord();
        if ($status) {
            $esito = $this->preModifica();
            if(!$esito){
                return;
            }
        }
        
        // Imposta div
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_divRisultato');

        // Imposta pulsanti buttonbar        
        Out::hide($this->nameForm . '_Aggiungi');
        Out::show($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divParametriRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::show($this->nameForm . '_Cancella');
        Out::show($this->nameForm . '_Importa');
        Out::show($this->nameForm . '_Scarica');
        Out::show($this->nameForm . '_Apri');

        Out::show($this->nameForm . '_REC[NATURA]');
        Out::show($this->nameForm . '_REC[NATURA]_lbl');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]_lbl');        
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_DURCAL'){
            Out::hide($this->nameForm . '_REC[NATURA]');
            Out::hide($this->nameForm . '_REC[NATURA]_lbl');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_SOGGAL'){
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        // Carica record corrente
//        $status = $this->caricaCurrentRecord();   fatto sopra
        if (!$status) {
            Out::msgStop("Errore", "Errore caricamento allegato");
            return;
        }
        $this->controlliAuditDettaglio(itaModelService::OPERATION_UPDATE);

        // Valorizza campi da current record
        $this->popolaCampiDettaglioDaCurrentRecord();

        Out::enableContainerFields($this->nameForm . "_workSpace");
        Out::disableContainerFields($this->nameForm . "_divComponentHeader");
        
        Out::showLayoutPanel($this->nameForm . '_buttonBar');
        
        $this->detailView = true;
    }

    private function visualizza() {
        // Imposta div
        Out::hide($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divGestione');
        Out::hide($this->nameForm . '_divRisultato');

        // Imposta pulsanti buttonbar        
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::show($this->nameForm . '_Torna');
        Out::hide($this->nameForm . '_divParametriRicerca');
        Out::hide($this->nameForm . '_Nuovo');
        Out::hide($this->nameForm . '_Cancella');
        Out::hide($this->nameForm . '_Importa');
        Out::show($this->nameForm . '_Scarica');
        Out::show($this->nameForm . '_Apri');

        // Carica record corrente
        $status = $this->caricaCurrentRecord();
        if ($status) {
            Out::show($this->nameForm . '_REC[NODE_UUID]');
            Out::show($this->nameForm . '_REC[NODE_UUID]_lbl');

            $this->controlliAuditDettaglio(itaModelService::OPERATION_OPENRECORD);

            // Valorizza campi da current record
            $this->popolaCampiDettaglioDaCurrentRecord();

            Out::disableContainerFields($this->nameForm . "_workSpace");
        }
        Out::show($this->nameForm . '_REC[NATURA]');
        Out::show($this->nameForm . '_REC[NATURA]_lbl');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]');
        Out::show($this->nameForm . '_REC[ST_EXP_WEB]_lbl');        
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_DURCAL'){
            Out::hide($this->nameForm . '_REC[NATURA]');
            Out::hide($this->nameForm . '_REC[NATURA]_lbl');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        if ($this->datiProvenienza['CONTESTO'] == 'BTA_SOGGAL'){
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]');
            Out::hide($this->nameForm . '_REC[ST_EXP_WEB]_lbl');
        }
        
        Out::showLayoutPanel($this->nameForm . '_buttonBar');
        
        $this->detailView = true;
    }

    private function aggiungi() {
        $this->formDataToCurrentRecord();
        $esito = $this->libAllegati->salvaAllegato($this->CURRENT_RECORD, $this->datiProvenienza['METADATI']);
        if (!$esito) {
            Out::msgStop("Errore", $this->libAllegati->getErrorDescription());
            return;
        } else {
            $this->elenco();
        }
    }

    private function cancella() {
        $status = $this->caricaCurrentRecord();
        if ($status) {
            $msg = 'Confermi la Cancellazione?';
            $esito = $this->preCancella($msg);
            if($esito){
                Out::msgQuestion("Cancellazione", $msg, array(
                    'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                    'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
                ));
            } else {
                Out::msgInfo("Attenzione!", $msg);
            }
        }
    }
    
    private function preCancella(&$msg) {
        $esito = true;
        $test = $this->testRecord();
        if ($test == 1){
            $msg = 'Allegato proveniete da una procedura esterna, non cancellare.';
            $esito = false; // non cancellabile
        }
        if ($test == 2)
            $msg = 'Allegato proveniete da una procedura esterna, non cancellare. Vuoi cancellare ugualmente ?';
        
        return $esito;
    }
    
    private function preModifica() {
        $esito = true;
        $test = $this->testRecord();
        if ($test > 0){
            $msg = 'Allegato proveniete da una procedura esterna, non modificare.';
            Out::msgInfo("Attenzione!", $msg);
            if ($test == 1)
                $esito = false; // non modificabile
        }
        
        return $esito;
    }
    
    private function testRecord() {
        $test = 0; // allegato gestibile
        if ($this->CURRENT_RECORD['CONTESTO'] == 'FES_DOCALL'){
            if (!empty($this->CURRENT_RECORD['METADATI']['TIPO_ALLEG'])){
                $msg = 'Allegato proveniete da una procedura esterna, non modificare.';
                $test = 1; // allegato non gest
                $authHelper = new cwbAuthHelper();
                $autorLevelFFA2 = $authHelper->checkAuthAutute(cwbParGen::getUtente(), 'FFA', 2); // 'FFA' 2
                if ($autorLevelFFA2 == 'M')
                    $test = 2;  // avviso non gest ma aut gest
            }
        }
        return $test;
    }

    private function confermaCancella() {
        // Carica record corrente
        $this->caricaCurrentRecord();
        $esito = $this->libAllegati->eliminaAllegato($this->CURRENT_RECORD);
        if (!$esito) {
            Out::msgStop("Errore", $this->libAllegati->getErrorDescription());
            return;
        } else {
            $this->elenco();
        }
    }

    private function aggiorna() {
        $this->formDataToCurrentRecord();
        Out::show($this->nameForm . '_REC[NODE_UUID]');
        Out::show($this->nameForm . '_REC[NODE_UUID]_lbl');
        $esito = $this->libAllegati->salvaAllegato($this->CURRENT_RECORD, $this->datiProvenienza['METADATI']);
        if (!$esito) {
            Out::msgStop("Errore", $this->libAllegati->getErrorDescription());
            return;
        } else {
            $this->elenco();
        }
    }

    private function controlliAuditDettaglio($tipoOperazione) {
        // Mostra/nasconde audit in funzione del tipo di operazione
        $tipoOperazione === itaModelService::OPERATION_INSERT ? Out::hide($this->nameForm . '_divAudit') : Out::show($this->nameForm . '_divAudit');

        // Inserimento
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[CODUTEINS]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[DATAINSER]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[TIMEINSER]', 'readonly', '0');

        // Ultima modifica        
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[CODUTE]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[DATAOPER]', 'readonly', '0');
        Out::attributo($this->nameForm . '_' . $this->TABLE_NAME . '[TIMEOPER]', 'readonly', '0');
    }

    private function importa() {
        Out::msgInput('Carica documento', array(
            array(
                'label' => array(
                    'value' => 'Caricare il documento',
                    'style' => 'width: 220px; display: block; float: left; padding: 0 5px 0 0; text-align: right;'
                ),
                'id' => $this->nameForm . '_CaricaDocumento',
                'name' => $this->nameForm . '_CaricaDocumento',
                'type' => 'type',
                'class' => 'ita-edit-upload',
                'size' => '30'
            )
                ), array(
            'Conferma' => array(
                'id' => $this->nameForm . '_ConfermaUpload',
                'model' => $this->nameForm
            ),
            'Annulla' => array(
                'id' => $this->nameForm . '_AnnullaUpload',
                'model' => $this->nameForm
            ),
                ), $this->nameForm, 'auto', 'auto', 'false'
        );
        Out::activateUploader($this->nameForm . '_CaricaDocumento_upld_uploader');
    }

    private function scarica() {        
        // Carica record corrente
        $status = $this->caricaCurrentRecord($this->CURRENT_RECORD['RIGA']);
        if (!$status) {
            Out::msgStop("Errore", "Errore caricamento allegato. Impossibile procedere con l'aggiornamento.");
            return;
        }        

        $this->formDataToCurrentRecord();

        $uriOneShot = $this->libAllegati->scarica($this->CURRENT_RECORD);
        if (!$uriOneShot) {                    
            Out::msgStop("Errore", $this->libAllegati->getErrorDescription());
            return;
        }
    }

    private function apri() {        
        // Carica record corrente
        $status = $this->caricaCurrentRecord($this->CURRENT_RECORD['RIGA']);
        if (!$status) {
            Out::msgStop("Errore", "Errore caricamento allegato. Impossibile procedere con l'aggiornamento.");
            return;
        }
        
        $this->formDataToCurrentRecord();
        $esito = $this->libAllegati->apri($this->CURRENT_RECORD);
        if (!$esito) {
            Out::msgStop("Errore", $this->libAllegati->getErrorDescription());
        }
    }

    public function getDatiProvenienza() {
        return $this->datiProvenienza;
    }
    
    public function setViewMode($viewMode){
        $this->viewMode = $viewMode;
    }

    public function setDatiProvenienza($datiProvenienza) {
        $this->datiProvenienza = $datiProvenienza;
    }

    private function caricaDocumento() {
        // Controlla se l'upload ha dato esito negativo
        if ($_POST['response'] == 'error') {
            Out::msgStop("Errore", "Impossibile caricare il file, verificare che le dimesioni non superino " . ini_get('upload_max_filesize'));
            return;
        }
        
        $origFile = $_POST['file'];
        $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];

        cwbParGen::setFormSessionVar($this->nameForm, 'origFile', $origFile);
        cwbParGen::setFormSessionVar($this->nameForm, 'uplFile', $uplFile);

        Out::valore($this->nameForm . '_CaricaDocumento', $origFile);
    }

    private function annullaUpload() {
        cwbParGen::removeFormSessionVar($this->nameForm, 'uplFile');
        cwbParGen::removeFormSessionVar($this->nameForm, 'origFile');
    }

    private function confermaUpload() {
        $fileURI = cwbParGen::getFormSessionVar($this->nameForm, 'uplFile');
        $origFile = cwbParGen::getFormSessionVar($this->nameForm, 'origFile');
        $this->CURRENT_RECORD['CORPO'] = file_get_contents($fileURI);
        Out::valore($this->nameForm . '_REC[NOME]', $origFile);
        
        // Se la descrizione non è valorizzata, la imposta con lo stesso valore del nome
        $descrizione = $_POST[$this->nameForm . '_REC']['DESCRIZIONE'];        
        if (strlen(trim($descrizione)) === 0) {
            Out::valore($this->nameForm . '_REC[DESCRIZIONE]', $origFile);
        }
                
        Out::valore($this->nameForm . '_REC[FLAG_DIS]', false);
    }

}
