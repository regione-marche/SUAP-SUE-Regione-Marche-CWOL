<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibBta.class.php';
include_once ITA_LIB_PATH . '/itaPHPOmnis/itaOmnisClient.class.php';

function cwbBtaSoggRicalcolaNomeRic() {
    $cwbBtaSoggRicalcolaNomeRic = new cwbBtaSoggRicalcolaNomeRic();
    $cwbBtaSoggRicalcolaNomeRic->parseEvent();
    return;
}

class cwbBtaSoggRicalcolaNomeRic extends itaFrontControllerCW {
    private $oldTimeout;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaSoggRicalcolaNomeRic';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        
        parent::__construct($nameFormOrig, $nameForm);
        $this->oldTimeout = cwbParGen::getFormSessionVar($this->nameForm, 'oldTimeout');
        
        $this->libDB = new cwbLibDB_BTA_SOGG();
        $this->connettiDB();
        
        $cwbAuthHelper = new cwbAuthHelper();
        $autorizzazione = $cwbAuthHelper->checkAuthAutute(cwbParGen::getUtente(), 'BTA', 19);
        $autorizzazione = trim($autorizzazione);            
        if ($autorizzazione != 'G' && $autorizzazione != 'C'){
            $this->close();
            Out::msgInfo('ATTENZIONE', 'Non si dispone delle autorizzazioni necessarie per accedere alla finestra.');
        }
    }
    
    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            // Salvo in Sessione il Valore per successivi ingressi non POST
            cwbParGen::setFormSessionVar($this->nameForm, 'fattura', $this->oldTimeout);
        }
    }
    
    public function parseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_btnRicalcola':  // entra in modifica
//                        Out::msgInfo("Info", "Attenzione !!! L'elaborazione potrebbe essere molto lunga non chiudere la sessione prima del messaggio di fine.");
                        $this->ricalcola();
                        break;
                    
                }
                break;
            
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_FL_BTASOGG':
                        if ($this->formData[$this->nameForm . '_FL_BTASOGG'] == 1)
                            Out::valore($this->nameForm . '_FL_BTASOGGST', 1);
                        break;
                    
                }
                break;
        }
    }
    
    private function ricalcola() {
        $flBtaSogg = $this->formData[$this->nameForm . '_FL_BTASOGG'];
        $flBtaSoggst = $this->formData[$this->nameForm . '_FL_BTASOGGST'];
        if (empty($flBtaSogg) && empty($flBtaSoggst)){
            Out::msgStop("Errore", "Selezionare l'archivio che si intende elaborare");
        } else {
            try {
                $this->oldTimeout = ini_get('max_execution_time');
                ini_set('max_execution_time', 30000);
                
                if (!empty($flBtaSogg)){    // Aggiorna il Soggetto
                    $soggElab = $this->aggiornaBtasogg();
                } else {
                    if (!empty($flBtaSoggst)) // Aggiorna solo lo storico Soggetto
                        $soggElab = $this->aggiornaBtasoggst();                
                }
                
                ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
                Out::msgInfo("Info", "L'elaborazione è terminata con successo. Soggetti aggiornati: " . $soggElab . " . E' possibile chiudere la sessione.");
            } catch (ItaException $ex) {
                ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
                Out::msgInfo("Info", "L'elaborazione è terminata con errore!");
                throw $ex;
            } catch (Exception $ex) {
                ini_set('max_execution_time', (isSet($this->oldTimeout) ? $this->oldTimeout : 0));
                Out::msgInfo("Info", "L'elaborazione è terminata con errore!");
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
            }
        }
    }
    
    protected function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }
    
    
//  Aggiorna i Soggetti
    public function aggiornaBtasogg() {
        $soggElab = 0;
        $filtri = array();
        $listaSogg = $this->libDB->leggiGeneric('BTA_SOGG', $filtri, true);
        if (!empty($listaSogg)) {
            foreach($listaSogg as $row){
                $nomeRic = cwbLibCalcoli::calcNomeRic($row['NOME_RIC']);
                $ragsocRic = cwbLibCalcoli::calcNomeRic($row['RAGSOC_RIC']);
                if ($row['NOME_RIC'] != $nomeRic || $row['RAGSOC_RIC'] != $ragsocRic){
                    $this->updateSogg($row, $nomeRic, $ragsocRic);
                    $soggElab++;
                }
                if (!empty($flBtaSoggst))
                    $soggstElab = $this->aggiornaBtasoggst($row);
            }   //  foreach($listaSoggst as $row){
        }   //  if (!empty($listaSogg)) {
        return $soggElab;
    }
    
    
//  Aggiorna lo Storico Soggetto / i   
    public function aggiornaBtasoggst($rowSogg = array()) {
        $soggstElab = 0;
        $filtri = array();
        if (!empty($rowSogg)){
            $filtri['PROGSOGG'] = $rowSogg['PROGSOGG'];
        }
        $listaSoggst = $this->libDB->leggiGeneric('BTA_SOGGST', $filtri, true);
        if (!empty($listaSoggst)) {
            foreach($listaSoggst as $row){
                $nomeRic = cwbLibCalcoli::calcNomeRic($row['NOME_RIC']);
                $ragsocRic = cwbLibCalcoli::calcNomeRic($row['RAGSOC_RIC']);
                if ($row['NOME_RIC'] != $nomeRic || $row['RAGSOC_RIC'] != $ragsocRic){
                    $this->updateSoggst($row, $nomeRic, $ragsocRic);
                    $soggstElab++;
                }
            }   //  foreach($listaSoggst as $row){
        }   //  if (!empty($listaSoggst)) {
        return $soggstElab;
    }
    
    
    public function updateSogg($row, $nomeRic, $ragsocRic) {
        try{
            $this->initModelService();
            $modelService = $this->getModelService();
//            $tableDef = $modelService->newTableDef('BTA_SOGG', $this->CITYWARE_DB);

            $row['NOME_RIC'] = $nomeRic;
            $row['RAGSOC_RIC'] = $ragsocRic;
            $modelService->updateRecord($this->CITYWARE_DB, 'BTA_SOGG', $row, 'Aggiornamento campi ricerca soggetto ' . $row['PROGSOGG']);
        }
        catch(ItaException $ex){
            Out::msgStop("Errore", "Si è presentato un errore durante la fase di aggiornamento dei record:<br>".$ex->getNativeErroreDesc());
            throw $ex;
        }
        catch(Exception $ex){
            Out::msgStop("Errore", "Si è presentato un errore durante la fase di aggiornamento dei record:<br>".$ex->getMessage());
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, $ex->getCode(), $ex->getMessage());
        }
    }
    
    public function updateSoggst($row, $nomeRic, $ragsocRic) {
        try{
            $this->initModelService();
            $modelService = $this->getModelService();
//            $tableDef = $modelService->newTableDef('BTA_SOGGST', $this->CITYWARE_DB);

            $row['NOME_RIC'] = $nomeRic;
            $row['RAGSOC_RIC'] = $ragsocRic;
            $modelService->updateRecord($this->CITYWARE_DB, 'BTA_SOGGST', $row, 'Aggiornamento campi ricerca soggetto storico ' . $row['PROGSOGG']);
        }
        catch(ItaException $ex){
            Out::msgStop("Errore", "Si è presentato un errore durante la fase di aggiornamento dei record Storico :<br>".$ex->getNativeErroreDesc());
        }
        catch(Exception $ex){
            Out::msgStop("Errore", "Si è presentato un errore durante la fase di aggiornamento dei record Storico :<br>".$ex->getMessage());
        }
    }
    
    
    protected function connettiDB() {
        try {
            // Per utilizzare il database 'CITYWARE' senza suffisso, passare come secondo parametro ''
            $this->CITYWARE_DB = ItaDB::DBOpen(cwbLib::getCitywareConnectionName(), '');     // Cityware
        } catch (ItaException $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            $this->close();
            Out::msgStop("Errore connessione db", $e->getMessage(), '600', '600');
        }
    }
    
    
}

?>