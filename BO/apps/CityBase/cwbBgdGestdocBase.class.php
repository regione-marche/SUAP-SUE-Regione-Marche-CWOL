<?php

include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDocumentale.class.php';

/**
 * Superclasse archiviazione documenti
 *
 * @author m.biagioli
 */
abstract class cwbBgdGestdocBase {

    const TIPO_ALFCITY = 'ALFCITY';
    const TIPO_DOCERCITY = 'DOCERCITY';

    protected $DB;
    private $errCode;
    private $errMsg;
    private $recBgdParott;
    private $recBgdAdassm;
    private $documentale;
    private $libDB_BGD;
    private $codiceEnte;
    private $codiceAOO;
    private $fileName;
    private $fileMimetype;
    private $fileContent;
    private $place;

    public function __construct() {
        $this->libDB_BGD = new cwbLibDB_BGD();
    }

    /**
     * Restituisce stringa sql per caricamento dati
     * @param array $filtri Filtri di selezione
     * @return string Stringa sql per caricamento dati
     */
    public abstract function getSqlCarica($filtri);

    /**
     * Carica un record tramite la key
     * @param String $key La chiave da caricare
     * @return array il record 
     */
    public abstract function caricaRecord($key);

    /**
     * Elabora risultati jqGrid
     * @param object $grid Grid
     */
    public function elaboraGrid($grid) {
        $Result_tab_tmp = $grid->getDataArray();
        $Result_tab = $this->elaboraRecords($Result_tab_tmp);

        return $Result_tab;
    }

    /**
     * Riversa i documenti
     * @param array $keys Le chiavi dei documenti selezionati da riversare
     */
    public function riversaDocumenti($keys) {

        //TODO Gestione errori
        //TODO Gestione array operazioni effettuate ed esiti

        $this->setErrCode(0);
        $this->setErrMsg('');

        $this->createObjDocumentale();
        $listaMetDoc = $this->libDB_BGD->leggiBgdMetdoc(array(
            'IDTIPDOC' => $this->recBgdAdassm['IDTIPDOC']
        ));

        foreach ($keys as $key) {
            $this->riversaDocumento($key, $listaMetDoc);
        }
    }

    /**
     * Riversa il documento
     * @param String $key chiave del documento selezionato da riversare
     * @param array $listaMetDoc metadati del documento
     */
    public function riversaDocumento($key, $listaMetDoc) {
        $this->apriDB();
        $rec = $this->caricaRecord($key);
        $doc_type = '';
        $props = array();
        $aspects = array();

        // Valorizza proprietà
        foreach ($listaMetDoc as $key => $value) {
            if (!$doc_type) {
                $doc_type = $value['ALIAS'];
            }
            $props[$value['CHIAVE']] = $rec[$value['PHP_DATANAME']];    //TODO: togliere il tolower
        }

        $props['COD_AOO'] = $this->getCodiceAOO(); // metadato obbligatorio
        
        // Valorizza proprietà custom
        $this->customSetProps($props);

        // Valorizza dati specifici
        $this->setDataInsert($rec);

        // Effettua inserimento su documentale
        $res = $this->documentale->insertDocument($this->getCodiceEnte(), 
                $doc_type, $this->getPlace(), $this->getFileName(), 
                $this->getFileMimetype(), $this->getFileContent(), $aspects, $props);

        if (!res) {
            // TODO Gestione errore inserimento su documentale
            return;
        }

        // Effettua aggiornamento su database
        $updated = $this->updateDBFields($res, $rec);
        if (!updated) {
            // TODO Gestione errore scrittura su database
            return;            
        }        
    }

    private function createObjDocumentale() {
        switch ($this->recBgdParott['F_GESTDOC']) {
            case 3:
                $this->documentale = new itaDocumentale(self::TIPO_DOCERCITY);
                break;
            case 4:
            default:
                $this->documentale = new itaDocumentale(self::TIPO_ALFCITY);
                break;
        }
    }

    protected function apriDB() {
        if (!$this->DB) {
            try {
                $rec = $this->getRecBgdAdassm();
                $this->DB = ItaDB::DBOpen($rec['DBNAME'], strlen($rec['DBSUFFIX']) == 0 ? $rec['DBSUFFIX'] : '');
            } catch (Exception $e) {
                Out::msgStop("ERRORE CONNESSIONE DB", $e->getCode() . ' - ' . $e->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * Elabora dati provenienti dalla grid
     * @param array $results Risultati da elaborare
     */
    public abstract function elaboraRecords($results);

    /**
     * Serve per settare i campi utili alla insertDocument (nomeFile, mimetype, content)
     * @param array $record il recod in fase di inserimento
     */
    public abstract function setDataInsert($record);

    /**
     * Valorizza proprietà custom
     * @param array $data Parametri da valorizzare
     */
    public abstract function customSetProps(&$data);
    
    /**
     * Effettua aggiornamento dei campi su database
     * @param $uuid UUID Documentale
     * @param $rec Record
     * @return boolean Esito operazione (true/false)
     */
    public abstract function updateDBFields($uuid, $rec);
    
    /**
     * Restituisce il model utilizzato per la visualizzazione della griglia
     * @return string Modello utilizzato per la visualizzazione della griglia
     */
    public function getModel() {
        return 'cwbBgdArcDocDefault';
    }

    public function getDB() {
        return $this->DB;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMsg() {
        return $this->errMsg;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMsg($errMsg) {
        $this->errMsg = $errMsg;
    }

    public function getRecBgdParott() {
        return $this->recBgdParott;
    }

    public function setRecBgdParott($recBgdParott) {
        $this->recBgdParott = $recBgdParott;
    }

    public function getRecBgdAdassm() {
        return $this->recBgdAdassm;
    }

    public function setRecBgdAdassm($recBgdAdassm) {
        $this->recBgdAdassm = $recBgdAdassm;
    }

    public function getCodiceEnte() {
        return $this->codiceEnte;
    }

    public function getCodiceAOO() {
        return $this->codiceAOO;
    }

    public function setCodiceEnte($codiceEnte) {
        $this->codiceEnte = $codiceEnte;
    }

    public function setCodiceAOO($codiceAOO) {
        $this->codiceAOO = $codiceAOO;
    }

    public function getFileName() {
        return $this->fileName;
    }

    public function getFileMimetype() {
        return $this->fileMimetype;
    }

    public function getFileContent() {
        return $this->fileContent;
    }

    public function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    public function setFileMimetype($fileMimetype) {
        $this->fileMimetype = $fileMimetype;
    }

    public function setFileContent($fileContent) {
        $this->fileContent = $fileContent;
    }

    public function getPlace() {
        return $this->place;
    }

    public function setPlace($place) {
        $this->place = $place;
    }

}
