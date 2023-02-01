<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/AppUtility.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

class accEncryptPasswords extends itaFrontController {
    private $secureMethod;
    private $dbITALWEBDB;
    
    protected function postItaFrontControllerCostruct() {
        $this->secureMethod = cwbParGen::getFormSessionVar($this->nameForm, 'secureMethod');
        $this->dbITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
    }
    
    public function parseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initComboboxes();
                $this->initForm();
                $this->checkSessions();
                break;
            case 'onClickTablePager':
                $this->checkSessions();
                break;
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_upd_blocco_btn':
                        $this->bloccaConfirm();
                        break;
                    case $this->nameForm . '_modal_conferma':
                        $this->blocca($_POST[$this->nameForm . '_modal_inputText']);
                        break;
                    case $this->nameForm . '_upd_sblocco_btn':
                        $this->sblocca();
                        break;
                    case $this->nameForm . '_Backup':
                        $this->backup();
                        break;
                    case $this->nameForm . '_Restore':
                        $this->uploadDialog();
                        break;
                    case $this->nameForm . '_ARCHIVIO_upld':
                        $this->caricaArchivio();
                        break;
                    case $this->nameForm . '_annullaUpload':
                        $this->cleanUpload();
                        break;
                    case $this->nameForm . '_Upload':
                        $this->restoreArchivio();
                        break;
                    case $this->nameForm . '_Convert':
                        $this->convert();
                        break;
                    case $this->nameForm . '_confermaConvert':
                        $this->confermaConvert();
                        break;
                }
                break;
        }
    }
    
    private function initComboboxes(){
        Out::html($this->nameForm . '_securePassword', '');
        Out::select($this->nameForm . '_securePassword', 1, 'none', false, 'Nessuna');
        Out::select($this->nameForm . '_securePassword', 1, 'md5', false, 'md5');
        Out::select($this->nameForm . '_securePassword', 1, 'sha1', false, 'sha1');
        Out::select($this->nameForm . '_securePassword', 1, 'sha256', false, 'sha256');
        
        Out::html($this->nameForm . '_secureConvert', '');
        Out::select($this->nameForm . '_secureConvert', 1, 'none', false, 'Nessuna');
        Out::select($this->nameForm . '_secureConvert', 1, 'md5', false, 'md5');
        Out::select($this->nameForm . '_secureConvert', 1, 'sha1', false, 'sha1');
        Out::select($this->nameForm . '_secureConvert', 1, 'sha256', false, 'sha256');
    }
    
    private function initForm(){
        $secureMethod = App::getConf('security.secure-password');
        $this->secureMethod = empty($secureMethod) ? 'none' : $secureMethod;
        cwbParGen::setFormSessionVar($this->nameForm, 'secureMethod', $this->secureMethod);
        
        Out::valore($this->nameForm . '_securePassword', $this->secureMethod);
        
        if($this->secureMethod == 'none'){
            Out::show($this->nameForm . '_boxConvert');
        }
        else{
            Out::hide($this->nameForm . '_boxConvert');
        }
        
        $this->initUpd();
    }
    
    private function getConnectionsArray(){
        $return = array();
        
        $domini = ItaDB::DBSelect($this->dbITALWEBDB, 'DOMAINS');
        foreach($domini as $dominio){
            $return[$dominio['CODICE']] = ItaDB::DBOpen('ITW', $dominio['CODICE']);
        }
        
        return $return;
    }
    
    private function backup(){
        $path = itaLib::createAppsTempPath('dbBackup');
        $path .= '/' . md5(time().rand(0, 1000)) . '.zip';
        
        $zipArchive = new ZipArchive();
        $zipArchive->open($path, ZipArchive::CREATE);
        
        foreach($this->getConnectionsArray() as $dominio=>$dbITW){
            $utenti = ItaDB::DBSelect($dbITW, 'UTENTI');
            
            if(!empty($utenti)){
                $csv = implode(';', array_keys($utenti[0])) . "\r\n";
                foreach($utenti as $row){
                    $csv .= implode(';', $row) . "\r\n";
                }
                
                $zipArchive->addFromString('BACKUP_UTENTI_'.$dominio.'.csv', $csv);
            }
        }
        
        $zipArchive->close();
            
        $otr = utiDownload::getOTR('BACKUP_UTENTI.zip', $path, true, true);
        Out::openDocument($otr);
    }
    
    private function bloccaConfirm() {
        Out::msgInputText($this->nameForm, "Conferma blocco applicativo", "Questa operazione bloccherà l'uso dell'applicativo per tutti gli utenti."
                . "Se si è sicuri di voler proseguire inserire il messaggio che verrà visualizzato fintanto che il blocco sarà attivo.");
    }

    private function blocca($msg) {
        AppUtility::setApplicationLock(true, $msg);

        $this->initUpd();
    }

    private function sblocca() {
        AppUtility::setApplicationLock(false);
        
        $this->initUpd();
    }
    
    private function initUpd() {
        if (AppUtility::getApplicationLock()) {
            Out::hide($this->nameForm . '_upd_blocco_div');
            Out::show($this->nameForm . '_upd_sblocco_div');
        } else {
            Out::show($this->nameForm . '_upd_blocco_div');
            Out::hide($this->nameForm . '_upd_sblocco_div');
        }
    }
    
    private function convert(){
        if (AppUtility::getApplicationLock()) {
            $secureMode = $_POST[$this->nameForm . '_secureConvert'];
            
            if($secureMode != 'none'){
                $messaggio = 'Confermi la conversione delle password in formato '.$_POST[$this->nameForm . '_secureConvert'].'? E\' fortemente consigliato fare un backup delle tabelle utenti';
                $bottoni = array(
                    'Annulla' => array('id' => $this->nameForm . '_annullaConvert', 'model' => $this->nameForm, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover'),
                    'Conferma' => array('id' => $this->nameForm . '_confermaConvert', 'model' => $this->nameForm, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover')
                );

                Out::msgQuestion('Conferma conversione password', $messaggio, $bottoni, 'auto', 'auto', 'true', false, true, false, "ItaForm", 'true', $this->nameForm);
            }
            else{
                Out::msgStop('Errore', 'La password è già salvata in chiaro.');
            }
        }
        else{
            Out::msgStop('Errore', 'E\' necessario attivare il blocco dell\'applicativo per procedere alla conversione delle password');
        }
    }
    
    private function confermaConvert(){
        if (AppUtility::getApplicationLock()) {
            $secureMode = $_POST[$this->nameForm . '_secureConvert'];
            
            if(in_array($secureMode, array('md5', 'sha1', 'sha256'))){
                $ini = file_get_contents(ITA_CONFIG_PATH . '/config.ini');
                $ini = preg_replace('/^\s*(secure-password\s*=\s*)(.*)$/m', '$1'.$secureMode, $ini);
                file_put_contents(ITA_CONFIG_PATH . '/config.ini', $ini);
                
                foreach($this->getConnectionsArray() as $dominio=>$dbITW){
                    $utenti = ItaDB::DBSelect($dbITW, 'UTENTI');

                    if(!empty($utenti)){
                        foreach($utenti as $row){
                            $update = $row;
                            $update['UTEPAS'] = hash($secureMode, $row['UTEPAS']);
                            ItaDB::DBUpdate($dbITW, 'UTENTI', 'ROWID', $update, $row);
                        }
                    }
                    
                    Config::loadConfig();
                    $this->initForm();
                }
            }
            else{
                Out::msgStop('Errore', 'E\' stato selezionata una modalità di codifica delle password non supportata');
            }
        }
        else{
            Out::msgStop('Errore', 'E\' necessario attivare il blocco dell\'applicativo per procedere alla conversione delle password');
        }
    }
    
    private function uploadDialog(){
        Out::msgInput('Carica archivio tabelle utenti',
            array(
                array(
                    'label' => array(
                        'value'=>'Archivio tabelle utenti:',
                        'style'=>'width: 120px; text-align: left;'
                    ),
                    'id' => $this->nameForm . '_ARCHIVIO',
                    'name' => $this->nameForm . '_ARCHIVIO',
                    'type' => 'type',
                    'class' => 'ita-edit-upload',
                    'size' => '30'
                )
            ),
            array(
                'Conferma' => array(
                    'id' => $this->nameForm . '_Upload',
                    'model' => $this->nameForm
                ),
                'Annulla' => array(
                    'id' => $this->nameForm . '_annullaUpload',
                    'model' => $this->nameForm
                ),
            ),
            $this->nameForm
        );
        Out::activateUploader($this->nameForm . '_ARCHIVIO_upld_uploader');
        Out::disableButton($this->nameForm . '_Upload');
    }
    
    private function caricaArchivio(){
        $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
        
        if($this->checkZipArchive($uplFile)){
            cwbParGen::setFormSessionVar($this->nameForm, 'uplFile', $uplFile);

            Out::valore($this->nameForm . '_ARCHIVIO', $_POST['file']);
            Out::enableButton($this->nameForm . '_Upload');
        }
        else{
            Out::msgStop('Errore', 'Archivio tabelle utenti non valido');
            Out::valore($this->nameForm . '_ARCHIVIO', '');
            Out::disableButton($this->nameForm . '_Upload');
        }
    }
    
    private function checkZipArchive($file){
        $zip = new ZipArchive();
        
        if($zip->open($file) === true){
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if(!preg_match('/^BACKUP_UTENTI_(.*?).csv$/', $filename)){
                    return false;
                }
            }
            return true;
        }
        else{
            return false;
        }
    }
    
    private function cleanUpload(){
        $uplFile = cwbParGen::getFormSessionVar($this->nameForm, 'uplFile');
        
        if(file_exists($uplFile)){
            unset($uplFile);
        }
        cwbParGen::removeFormSessionVar($this->nameForm, 'uplFile');
    }
    
    private function restoreArchivio(){
        $uplFile = cwbParGen::getFormSessionVar($this->nameForm, 'uplFile');
        
        $zip = new ZipArchive();
        $zip->open($uplFile);
        
        $connections = $this->getConnectionsArray();
        
        $filenames = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filenames[] = $zip->getNameIndex($i);
        }
        
        $check = true;
        foreach(array_keys($connections) as $ente){
            if(!in_array('BACKUP_UTENTI_'.$ente.'.csv', $filenames)){
                $check = false;
                break;
            }
        }
        
        if($check){
            foreach($connections as $ente=>$connection){
                $csvHandle = $zip->getStream('BACKUP_UTENTI_'.$ente.'.csv');
                $header = fgetcsv($csvHandle, 0, ';');
                while($values = fgetcsv($csvHandle, 0, ';')){
                    $row = array();
                    for($i=0; $i<count($header); $i++){
                        $row[$header[$i]] = $values[$i];
                    }
                    
                    ItaDB::DBUpdate($connection, 'UTENTI', 'ROWID', $row);
                }
            }
            Out::msgInfo('Tabelle ripristinate', 'Tabelle utenti ripristinate con successo. Se è cambiato il sistema di memorizzazione della password modificare manualmente il file config.ini alla voce "secure-password"');
        }
        else{
            Out::msgStop('Errore', 'Nell\'archivio non sono presenti tutti gli enti in uso sull\'installazione');
        }
        $this->cleanUpload();
    }
    
    private function checkSessions(){
        $data = array();
        
        $query = "  SELECT
                        TOKEN.TOKCOD,
                        TOKEN.TOKFIL__1,
                        UTENTI.UTELOG,
                        UTENTI.UTEFIL__2
                    FROM TOKEN
                    JOIN UTENTI ON TOKEN.TOKUTE = UTENTI.UTECOD
                    WHERE TOKEN.TOKNUL = 0
                        AND (TOKEN.TOKFIL__1 + UTENTI.UTEFIL__2) > ". (float) time() / 60;
        
        foreach($this->getConnectionsArray() as $dominio=>$dbITW){
            $sessioni = ItaDB::DBSQLSelect($dbITW, $query);
            
            foreach($sessioni as $sessione){
                $data[] = array(
                    'ENTE'=>$dominio,
                    'UTENTE'=>$sessione['UTELOG'],
                    'TOKEN'=>$sessione['TOKCOD'],
                    'REFRESH'=>date('d/m/Y - H:i:s', round($sessione['TOKFIL__1']*60, 0)),
                    'SCADENZA'=>date('d/m/Y - H:i:s', round(($sessione['TOKFIL__1']+$sessione['UTEFIL__2'])*60, 0))
                );
            }
        }
        
        TableView::clearGrid($this->nameForm . '_gridSessioni');

        $ita_grid01 = new TableView($this->nameForm . '_gridSessioni', array('arrayTable'=>$data));
        $ita_grid01->setPageNum(isset($_POST['page']) ? $_POST['page'] : 1);
        $pageRows = (isset($_POST['rows']) ? $_POST['rows'] : $_POST[$this->nameForm . '_gridSessioni']['gridParam']['rowNum']);
        $ita_grid01->setPageRows($pageRows ? $pageRows : 25);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_gridSessioni');
    }
}
