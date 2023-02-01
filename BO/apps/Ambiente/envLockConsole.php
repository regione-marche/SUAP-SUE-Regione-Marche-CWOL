<?php
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaModelServiceFactory.class.php');
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';

function envLockConsole() {
    $envLockConsole = new envLockConsole();
    $envLockConsole->parseEvent();
    return;
}

class envLockConsole extends itaFrontController {    
    const TAB_SYSLOCK = 1;
    const TAB_CWLOCK = 2;
    const DATEFORMAT_CW = 'Ymd-His';
    
    public $nameForm = "envLockConsole";
    private $gridSysLock;
    private $gridCWLock;
    private $currentGrid;
    private $checkExpired;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        parent::__construct($nameFormOrig, $nameForm);
        $this->gridSysLock = $this->nameForm . '_gridSysLock';
        $this->gridCWLock = $this->nameForm . '_gridCWLock';
        $this->currentGrid = App::$utente->getKey($this->nameForm . '_currentGrid');        
        if (!$this->currentGrid) {
            $this->currentGrid = self::TAB_SYSLOCK;
        }
        $this->checkExpired = App::$utente->getKey($this->nameForm . '_checkExpired');
        if ($this->checkExpired === null) {
            $this->checkExpired = true;
        }
    }
    
    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currentGrid', $this->currentGrid);            
            App::$utente->setKey($this->nameForm . '_checkExpired', $this->checkExpired);            
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
                    case $this->nameForm . '_Cancella':
                        $this->cancella();
                        break;
                    case $this->nameForm . '_tabSysLock':
                        $this->changeCurrentGrid(self::TAB_SYSLOCK);
                        $this->caricaDati();
                        break;
                    case $this->nameForm . '_tabCWLock':
                        $this->changeCurrentGrid(self::TAB_CWLOCK);
                        $this->caricaDati();
                        break;   
                    case $this->nameForm . '_AnnullaCancella':
                        break;
                    case $this->nameForm . '_ConfermaCancella':
                        $ret = $this->confermaCancella();
                        if ($ret['ESITO']) {
                            Out::msgInfo('Info', 'Lock eliminato');
                        } else {
                            Out::msgStop('Errore', 'Errore cancellazione record lock (' . $ret['MESSAGIO'] . ')');
                        }
                        $this->caricaDati();
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;           
            case 'onClickTablePager':
                $this->caricaDati();
                break;     
            case 'delGridRow':
                $this->cancella();
                break;
        }
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currentGrid');
        App::$utente->removeKey($this->nameForm . '_checkExpired');
        itaLib::closeForm($this->nameForm);
    }    
    
    private function init() {        
        Out::tabSelect($this->nameForm . '_divMainTabs', $this->currentGrid);                
        $this->caricaDati();
    }        
    
    private function caricaDati() {                
        switch ($this->currentGrid) {
            case self::TAB_SYSLOCK:
                $this->caricaDatiSysLock();
                break;
            case self::TAB_CWLOCK:
                $this->caricaDatiCWLock();
                break;
            default:
                break;
        }
    }
        
    private function caricaDatiSysLock() {
        // Legge dati
        $sql = 'SELECT * FROM LOCKTAB';
        $where = 'WHERE';
        if (isset($_POST['ROWID']) && !empty($_POST['ROWID'])) {            
            $sql .= ' ' . $where . ' ROWID=' . $_POST['ROWID'];
            $where = 'AND';
        }
        if (isset($_POST['LOCKRECID']) && !empty($_POST['LOCKRECID'])) {            
            $sql .= ' ' . $where . ' ' . App::$itaEngineDB->strUpper('LOCKRECID') . " LIKE '%" . strtoupper($_POST['LOCKRECID']) . "%'";
            $where = 'AND';
        }  
        if (isset($_POST['LOCKTOKEN']) && !empty($_POST['LOCKTOKEN'])) {            
            $sql .= ' ' . $where . ' ' . App::$itaEngineDB->strUpper('LOCKTOKEN') . " LIKE '%" . strtoupper($_POST['LOCKTOKEN']) . "%'";            
        }            
        $sql .= ' ORDER BY ROWID DESC';
        $results = ItaDB::DBSQLSelect(App::$itaEngineDB, $sql, true);        
        
        // Effettua la cancellazione dei record scaduti
        $this->cancellaLockScadutiSys($results);
        
        // Carica grid
        $grid = new TableView($this->gridSysLock, array(
            'arrayTable' => $this->elaboraRecordsSys($results), 
            'rowIndex' => 'idx')
        );
        $grid->setPageNum(1);
        $grid->setPageRows(9999);
        $grid->setSortIndex(isset($_POST['sidx']) ? $_POST['sidx'] : 'ROWID');
        $grid->setSortOrder(isset($_POST['sord']) ? $_POST['sord'] : 'DESC');
        TableView::enableEvents($this->gridSysLock);
        TableView::clearGrid($this->gridSysLock);
        $grid->getDataPage('json');        
    }
    
    private function elaboraRecordsSys($results) {
        foreach ($results as &$result) {            
            $result['LOCKTIME'] = date('d-m-Y H:i:s', $result['LOCKTIME']);
            $result['LOCKEXP'] = date('d-m-Y H:i:s', $result['LOCKEXP']);            
        }
        return $results;
    }
    
    private function caricaDatiCWLock() {
        // Legge dati
        $libDB = new cwbLibDB_GENERIC();
        $filtri = array();
        if (isset($_POST['ID_LOCK']) && !empty($_POST['ID_LOCK'])) {
            $filtri['ID_LOCK'] = $_POST['ID_LOCK'];
        }
        if (isset($_POST['ID_RECORD']) && !empty($_POST['ID_RECORD'])) {
            $filtri['ID_RECORD_like_upper'] = $_POST['ID_RECORD'];
        }        
        if (isset($_POST['CLASSE']) && !empty($_POST['CLASSE'])) {
            $filtri['CLASSE_like_upper'] = $_POST['CLASSE'];
        }
        if (isset($_POST['UTENTE']) && !empty($_POST['UTENTE'])) {
            $filtri['UTENTE_upper'] = $_POST['UTENTE'];
        }
        if (isset($_POST['CONN_ID']) && !empty($_POST['CONN_ID'])) {
            $filtri['CONN_ID_upper'] = $_POST['CONN_ID'];
        }
        $orderBy = 'DATAORAINI DESC';
        $results = $libDB->leggiGeneric('BWE_RECLCK', $filtri, true, '*', $orderBy);     
        
        // Effettua la cancellazione dei record scaduti
        $this->cancellaLockScadutiCW($results);

        // Carica grid
        $grid = new TableView($this->gridCWLock, array(
            'arrayTable' => $this->elaboraRecordsCW($results), 
            'rowIndex' => 'idx')
        );
        $grid->setPageNum(1);
        $grid->setPageRows(9999);
        $grid->setSortIndex(isset($_POST['sidx']) ? $_POST['sidx'] : 'DATAORAINI');
        $grid->setSortOrder(isset($_POST['sord']) ? $_POST['sord'] : 'DESC');
        TableView::enableEvents($this->gridCWLock);
        TableView::clearGrid($this->gridCWLock);
        $grid->getDataPage('json');        
    }
    
    private function elaboraRecordsCW($results) {
        foreach ($results as &$result) {
            $date = DateTime::createFromFormat(self::DATEFORMAT_CW, $result['DATAORAINI']);            
            $result['DATAORAINI'] = $date->format('d-m-Y H:i:s');
        }
        return $results;
    }
    
    private function cancella() {
        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del lock selezionato?", array(
            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancella', 'model' => $this->nameForm, 'shortCut' => "f5")
        ));
    }
    
    private function confermaCancella($lockId = null) {
        $toReturn = array();
        $toReturn['ESITO'] = true;
        $toReturn['MESSAGGIO'] = '';
            
        switch ($this->currentGrid) {
            case self::TAB_SYSLOCK:
                $lockIndex = ($lockId !== null ? $lockId : $_POST[$this->gridSysLock]['gridParam']['selrow']);        
                $db = App::$itaEngineDB;
                break;
            case self::TAB_CWLOCK:
                $lockIndex = ($lockId !== null ? $lockId : $_POST[$this->gridCWLock]['gridParam']['selrow']);        
                $libDB = new cwbLibDB_GENERIC();
                $db = $libDB->getCitywareDB();
                break;
            default:
                break;
        }
        
        if (!$lockIndex) {
            Out::msgStop('Attenzione', 'Nessun record selezionato per la cancellazione');
            return;
        }
        
        try {            
            ItaDB::DBUnLock($lockIndex, $db);                        
        } catch (Exception $e) {
            $toReturn['ESITO'] = false;
            $toReturn['MESSAGGIO'] = $e->getMessage();
        }
        
        return $toReturn;
    }
        
    private function changeCurrentGrid($currentGrid) {
        $this->currentGrid = $currentGrid;
    }
        
    private function cancellaLockScadutiSys($results) {
        // Effettua il controllo somanente la prima volta
        if (!$this->checkExpired || count($results) === 0) {
            return;
        }
        $this->checkExpired = false;
        
        // Scorre i record lock e cancella tutti quelli scaduti
        $numCancellati = 0;
        $numErrori = 0;
        $now = time();
        foreach ($results as $result) {
            if ($result['LOCKEXP'] < $now) {
                $ret = $this->confermaCancella($result['ROWID'], false);
                if ($ret['ESITO']) {
                    $numCancellati++;
                } else {
                    $numErrori++;
                }
            }             
        }      
        if ($numCancellati > 0 || $numErrori > 0) {
            Out::msgInfo('Info', 'Eliminati ' . $numCancellati . ' lock scaduti (Errori: ' . $numErrori . ')');
        }        
    }
    
    private function cancellaLockScadutiCW($results) {
        // Effettua il controllo somanente la prima volta
        if (!$this->checkExpired || count($results) === 0) {
            return;
        }
        $this->checkExpired = false;
        
        // Scorre i record lock e cancella tutti quelli scaduti
        $numCancellati = 0;
        $numErrori = 0;
        $now = new DateTime();
        foreach ($results as $result) {
            $expired = DateTime::createFromFormat(self::DATEFORMAT_CW, $result["DATAORAINI"]);
            $expired = $this->addSecondToDate($expired, $result["DURATA"]);
            $interval = $now->diff($expired);
            if ($interval->invert != 0) {    
                $ret = $this->confermaCancella($result['ID_LOCK'], false);
                if ($ret['ESITO']) {
                    $numCancellati++;
                } else {
                    $numErrori++;
                }
            }
        }
        if ($numCancellati > 0 || $numErrori > 0) {
            Out::msgInfo('Info', 'Eliminati ' . $numCancellati . ' lock scaduti (Errori: ' . $numErrori . ')');
        }
    }
    
    private function addSecondToDate($date, $sec) {
        return $date->add(new DateInterval('PT' . $sec . 'S'));
    }
    
}
