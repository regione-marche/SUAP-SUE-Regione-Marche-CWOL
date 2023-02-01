<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';

/**
 *
 * Superclasse gestione form treeTable Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Pergolini Lorenzo Massimo Biagioli
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbBpaGenTree extends cwbBpaGenModel {

    const DEFAULT_ROWS = 9999999999999999;  // valore alto per evitare la paginazione della jqGrid che di default è 10
    const KEYTREE_PK = 'KEYTREE';

    protected $treeFields;

    /**
     * array che contiene i campi da controllare per la propagazione ai figli
     * @var array: 'key'=>'value'
     *              'key': nome del campo
     *              'value':    'always' la modifica al campo verrà sempre propagata ai figli oppure
     *                          'funzioneCallback' nome di una funzione dell'oggetto genTree che prende il genitore e per referenza il figlio e lo modifica come necessario.
     */
    protected $propagateToChild;
    protected $excludeFieldsUnderline; // Array di campi da ignorare per la sottolineatura a seguito di una ricerca

    public function postItaFrontControllerCostruct() {
        if(!isSet($this->treeFields)){
            $this->treeFields = array();
        }
        if(!isSet($this->propagateToChild)){
            $this->propagateToChild = array();
        }
        if(!isSet($this->excludeFieldsUnderline)){
            $this->excludeFieldsUnderline = array();
        }
        parent::postItaFrontControllerCostruct();
        if($this->elencaAutoAudit){
            if(!in_array('CODUTE', $this->treeFields)){
                $this->treeFields[] = 'CODUTE';
            }
            if(!in_array('DATATIMEOPER', $this->treeFields)){
                $this->treeFields[] = 'DATATIMEOPER';
            }
        }
        if($this->elencaAutoFlagDis){
            if(isSet($this->treeFields) && !in_array('FLAG_DIS', $this->treeFields)){
                $this->treeFields[] = 'FLAG_DIS';
            }
            if(isSet($this->filtriFissi) && !in_array('FLAG_DIS', $this->filtriFissi)){
                $this->filtriFissi[] = 'FLAG_DIS';
            }
            if(isSet($this->excludeFieldsUnderline) && !in_array('FLAG_DIS', $this->excludeFieldsUnderline)){
                $this->excludeFieldsUnderline[] = 'FLAG_DIS';
            }
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        
        // Se nel metodo preParseEvent è stata impostata la variabile breakEvent, termina l'esecuzione dell'evento
        if ($this->getBreakEvent()) {
            return;
        }
        
        switch ($_POST['event']) {
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->GRID_NAME:
                        $this->caricaTree();
                        break;
                }
                break;

            case 'expandNode':
                $this->apriNodo($_POST['rowid']);
                break;

            case 'collapseNode':
                $this->chiudiNodo($_POST['rowid']);
                break;
        }
    }

    protected function elenca() {
        try {
            $this->setGridFilters();
            if($this->elencaAutoAudit){
                $this->filtraElencaAudit();
            }
            if($this->elencaAutoFlagDis){
                $this->filtraElencaFlagDis();
            }
            
            $this->preElenca();
            $filtri = $this->initFiltriRicerca();
            $this->caricaTree($filtri);
            $this->setVisRisultato();
            TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);

            if ($this->skipAuth != true && !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_DELETE)) {
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'DELETEROW');
                Out::hideElementFromClass($this->nameForm . '_wrapper', 'ita-delgridrow');
            } else {
                Out::gridShowCol($this->nameForm . '_' . $this->GRID_NAME, 'DELETEROW');
                Out::showElementFromClass($this->nameForm . '_wrapper', 'ita-delgridrow');
            }
            if ($this->skipAuth != true && !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_WRITE)) {
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'EDITROW');
                Out::hideElementFromClass($this->nameForm . '_wrapper', 'ita-addgridrow');
                Out::hideElementFromClass($this->nameForm . '_wrapper', 'ita-editgridrow');
            } else {
                Out::gridShowCol($this->nameForm . '_' . $this->GRID_NAME, 'EDITROW');
                Out::showElementFromClass($this->nameForm . '_wrapper', 'ita-addgridrow');
                Out::showElementFromClass($this->nameForm . '_wrapper', 'ita-editgridrow');
            }
            if ($this->skipAuth != true && !$this->authenticator->isActionAllowed(itaAuthenticator::ACTION_READ)) {
                Out::gridHideCol($this->nameForm . '_' . $this->GRID_NAME, 'VIEWROW');
            } else {
                Out::gridShowCol($this->nameForm . '_' . $this->GRID_NAME, 'VIEWROW');
            }
            
            $this->renderSelect();
            
            $this->postElenca();
        } catch (ItaException $e) {
            Out::msgStop("Errore caricamento albero", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore caricamento albero", $e->getMessage(), '600', '600');
        }
    }

    protected function preCaricaTree() {
        
    }

    /**
     * Caricamento iniziale TreeList
     * (L'evento scatta appena si apre la finestra o quando si effettuano ricerche con i filtri 
     * Ricostruisce l'albero a ritroso caricando i nodi estratti dai filtri
     * @param array $filtri Filtri di ricerca
     */
    protected function caricaTree($filtri = array()) {
        $this->preCaricaTree();

        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME);

        // Se non presenti filtri di ricerca, carica solamente i primi livelli,
        // altrimenti effettua la ricerca e per ogni foglia carica l'intero ramo        
        if ($this->getFiltriImpostati($filtri)) {
            $data = $this->caricaAlbero($filtri);
            $data = $this->sottolineaRisultati($data);
            $data = $this->elaboraNodiCaricati($data);
            $level = null;
            $parent = null;
        } else {
            $data = $this->caricaNodiPrimoLivello($filtri);
            $level = 0;
            $parent = '';
        }
        $grd = new TableView($this->nameForm . '_' . $this->GRID_NAME, array('arrayTable' => $this->toTreeModel($data, $level, $parent), 'rowIndex' => 'idx'));
        $grd->setPageRows(self::DEFAULT_ROWS);  // di default la jqGrid pagina a 10 pagine ma nella tree non va fatta la paginazione
        $grd->treeAddChildren();
        
        $this->renderSelect();
        
        $this->postCaricaTree();
    }

    protected function postCaricaTree() {
        
    }

    /**
     * Carica nodi di primo livello della classe specifica 
     * (Effettuare override nelle sottoclassi)
     * @return array Nodi di primo livello
     */
    protected function caricaNodiPrimoLivello() {
        
    }

    /**
     * Carica albero, in funzione dei filtri di ricerca impostati
     * Per ogni elemento trovato, se si tratta di una foglia, ricostruisce tutto il ramo
     * (Effettuare override nelle sottoclassi)
     * @param array $filtri Filtri di ricerca
     * @return array Albero che soddisfa i criteri di ricerca
     */
    protected function caricaAlbero($filtri) {
        return array();
    }

    /**
     * Elabora i nodi caricati (per ogni elemento, carica la gerarchia)
     * @param array $data Nodi caricati
     * @return array Nodi da mostrare a video
     */
    protected function elaboraNodiCaricati($data) {
        try {
            foreach ($data as $row) {
                $this->caricaGerarchiaNodo($row, $data);
            }
            $data = $this->postElaboraNodiCaricati($data);
            return $data;
        } catch (ItaException $e) {
            Out::msgStop("Errore elaborazione nodi albero", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore elaborazione nodi albero", $e->getMessage(), '600', '600');
        }
    }

    protected function postElaboraNodiCaricati($data) {
        return $data;
    }

    /**
     * Carica gerarchia per il nodo corrente
     * @param array $nodo Nodo corrente
     * @param array $albero Insieme dei nodi caricati
     */
    protected function caricaGerarchiaNodo($nodo, &$albero) {
        
    }

    /**
     * Restituisce array con i filtri di ricerca
     * (Effettuare override nelle sottoclassi)
     * @return array Filtri di ricerca
     */
    protected function initFiltriRicerca() {
        return array();
    }

    /**
     * Apertura nodo
     * (L'evento scatta quando si clicca sull'icona di espansione del nodo)
     * @param string $idNodo ID Nodo da aprire
     * @param boolean $force_expanded Se true forza l'assegnazione come espanso su initModelRow
     *                                Utilizzabile ad esempio per Aprire un Nodo "manualmente"
     */
    protected function apriNodo($idNodo,$force_expanded=null) {
        try {
            $this->preApriNodo($idNodo);
            $grd = new TableView($this->nameForm . '_' . $this->GRID_NAME, array('arrayTable' => $this->toTreeModel($this->caricaFigli($idNodo), $this->getLivello($idNodo) + 1, $idNodo, $force_expanded), 'rowIndex' => 'idx'));
            $grd->setPageRows(self::DEFAULT_ROWS);
            $grd->treeAddChildren();
            
            $this->renderSelect();
            
            $this->postApriNodo($idNodo);
        } catch (ItaException $e) {
            Out::msgStop("Errore apertura nodo", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore apertura nodo", $e->getMessage(), '600', '600');
        }
    }

    protected function preApriNodo($idNodo) {
        
    }

    protected function postApriNodo($idNodo) {
        
    }

    /**
     * Effettua il caricamento dei figli, dato in ingresso l'id del nodo padre
     * (Effettuare override nelle sottoclassi)
     * @param string $idNodoPadre ID Nodo padre
     * @return array Nodi figli relativi al nodo in ingresso
     */
    protected function caricaFigli($idNodoPadre) {
        return array();
    }

    /**
     * Chiusura nodo
     * (L'evento scatta quando si clicca sull'icona di chiusura del nodo)
     * @param string $idNodo ID Nodo da chiudere
     */
    protected function chiudiNodo($idNodo) {
        try {
            $this->preApriNodo($idNodo);
            $this->eliminaFigli($idNodo);
            $this->postChiudiNodo($idNodo);
        } catch (ItaException $e) {
            Out::msgStop("Errore chiusura nodo", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore chiusura nodo", $e->getMessage(), '600', '600');
        }
    }

    protected function preChiudiNodo($idNodo) {
        
    }

    protected function postChiudiNodo($idNodo) {
        
    }

    /**
     * Elimina i figli del nodo dato in ingresso dalla TableView
     * @param string $idNodoPadre ID Nodo padre
     */
    protected function eliminaFigli($idNodoPadre) {
        try {
            $grd = new TableView($this->nameForm . '_' . $this->GRID_NAME, array('arrayTable' => array(), 'rowIndex' => 'idx'));
            $grd->setPageRows(self::DEFAULT_ROWS);  // di default la jqGrid pagina a 10 pagine ma nella tree non va fatta la paginazione
            $grd->treeRemoveChildren($idNodoPadre);
        } catch (ItaException $e) {
            Out::msgStop("Errore cancellazione figli", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore cancellazione figli", $e->getMessage(), '600', '600');
        }
    }

    /**
     * Adatta dati a modello treelist
     * @param type $data Dati da caricamento database
     * @param type $level Livello
     * @param type $parent ID Nodo padre
     * @param type $force_expanded Se true forza l'assegnazione come espanso su initModelRow
     *                             Utilizzabile ad esempio per Aprire un Nodo "manualmente"
     * @param type $isLeaf 'true' se foglia, altrimenti 'false'
     * @param type $loaded 'true' se caricato, altrimenti 'false'
     * @param type $expanded 'true' se deve essere espanso, altrimenti 'false'
     * @return type
     */
    protected function toTreeModel($data, $level, $parent, $force_expanded=null ) {
        $tm = array();

        
        if($this->elencaAutoAudit){
            $this->renderElencaAudit($data);
        }
        if($this->elencaAutoFlagDis){
            $this->renderElencaFlagDis($data);
        }
        foreach ($data as $row) {
            $modelRow = array();

            foreach ($row as $k => $v) {
                if (in_array($k, $this->treeFields)) {
                    $modelRow[$k] = $this->formatValue($k, $v, $row);
                }
            }

            $this->initModelRow($modelRow, $level, $parent, $row, $data, $force_expanded );
            $key = '';
            if (is_array($this->PK)) {
                $separator = '';
                foreach ($this->PK as $value) {
                    $key .= $separator . $row[$value];
                    $separator = "_";
                }
            } else {
                $key = $row[$this->PK];
            }
            $tm[$key] = $modelRow;
        }

        return $tm;
    }

    /**
     * Sottolinea i risultati della ricerca, esclusi i campi chiave
     * @param type $data Dati da caricamento database    
     * @return type $data lista elaborata
     */
    protected function sottolineaRisultati($data) {
        foreach ($data as $key => $row) {
            foreach ($this->treeFields as $tvalue) {
                // non vanno sottolineati i campi chiave (la chiave potrebbe chiamarsi keytree e non $this->PK in caso di chiave composta)
                if (!in_array($tvalue, $this->excludeFieldsUnderline) && $tvalue != $this->PK && $tvalue != self::KEYTREE_PK) {
                    $data[$key][$tvalue] = '<u>' . trim($row[$tvalue]) . '</u>';
                }
            }
        }

        return $data;
    }

    /**
     * Inizializza model record corrente con gli attributi del nodo
     * @param array $modelRow record da inizializzare
     * @param int $level Livello nodo
     * @param string $parent ID Padre
     * @param array $row Row completa
     * @param array $data Lista completa
     * @param boolean $force_expanded Se true forza l'assegnazione come espanso su initModelRow
     *                                Utilizzabile ad esempio per Aprire un Nodo "manualmente"
     */
    protected function initModelRow(&$modelRow, $level, $parent, $row, $data, $force_expanded=null) {
        $modelRow['level'] = $level != null ? $level : $this->livelloDaNodo($row);
        $modelRow['parent'] = $parent != null ? $parent : $this->parentDaAlbero($row, $data);
        $modelRow['isLeaf'] = $this->getFoglia($row) ? 'true' : 'false';
        $modelRow['loaded'] = 'true';
        if (!empty($force_expanded) && $modelRow['isLeaf'] != 'true') {
            $modelRow['expanded'] = $force_expanded;
        } else {
            $modelRow['expanded'] = $level != null ? 'false' : $this->expandedDaCaricamento($row, $data);
        }
    }

    /**
     * Ricava livello per visualizzazione (base 0) da nodo passato come parametro
     * @param array $nodo Nodo
     * @return int Livello
     */
    protected function livelloDaNodo($nodo) {
        return 0;
    }

    /**
     * Ricava parent da albero caricato in memoria
     * @param array $nodo Dati nodo
     * @param array $albero Albero caricato in memoria
     * @return string ID parent
     */
    protected function parentDaAlbero($nodo, $albero) {
        return '';
    }

    /**
     * Imposta flag expanded da albero caricato in memoria
     * @param array $nodo Dati nodo
     * @param array $albero Albero caricato in memoria
     * @return string ProprietÃ  expanded ('true' o 'false')
     */
    protected function expandedDaCaricamento($nodo, $albero) {
        return false;
    }

    /**
     * Restituisce true se ci sono dei filtri impostati, altrimenti false
     * (Esclude i filtri fissi)
     * @param array $filtri Filtri impostati da ricerca
     */
    protected function getFiltriImpostati($filtri) {
        if(!empty($filtri)){
            foreach ($filtri as $k=>$v) {
                if(!empty($v) && !isSet($this->filtriFissi) || !in_array($k, $this->filtriFissi)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Restituisce il livello del nodo corrispondente all'id passato come parametro
     * (Effettuare override nelle sottoclassi)
     * @param string $id Id nodo
     * @return int Livello nodo
     */
    protected function getLivello($id) {
        return 0;
    }

    /**
     * Restituisce true se il nodo corrente è una foglia, altrimenti false
     * (Effettuare override nelle sottoclassi)
     * @param array $row Record corrente
     * @return boolean true se il nodo è una foglia, altrimenti false
     */
    protected function getFoglia($row) {
        return false;
    }

    /**
     * Formatta valore per essere visualizzato nella griglia
     * @param string $key Chiave
     * @param any $value Valore
     * @param any $row Row
     * @return string Valore trasformato 
     */
    protected function formatValue($key, $value, $row) {
        return $value;
    }

    public function getTreeFields() {
        return $this->treeFields;
    }

    public function setTreeFields($treeFields) {
        $this->treeFields = $treeFields;
    }

    //Carica tutti i figli del ramo selezionato in maniera ricorsiva
    //utilizzato per aggiornametno a cascata dei campi "$propagateToChild"
    private function caricaFigliRicorsivo($dati, $chiavi) {
        $figli = $this->caricaFigli($dati[$chiavi[0]]);
        if(is_array($figli)){
            foreach ($figli as $figlio) {
                $figli = array_merge($figli, $this->caricaFigliRicorsivo($figlio, $chiavi));
            }
        }
        else{
            $figli = array();
        }
        return $figli;
    }

    //Effettua il caricametno dei modelli collegati a quello principale
    protected function caricaCascata() {
        $data = $this->modelData->getData();
        $oldCurrentRecord = $this->getOldCurrentRecord($data['CURRENT_RECORD']['tableName'], $data['CURRENT_RECORD']['tableData']);

        $check = false;
        $figli = array();


        if (is_array($this->propagateToChild)) {
            foreach (array_keys($this->propagateToChild) as $field) {
                if ($oldCurrentRecord[$field] != $data['CURRENT_RECORD']['tableData'][$field]) {
                    $check = true;
                    break;
                }
            }
        }

        if ($check) {
            $chiavi = $this->getModelService()->getPKs($this->MAIN_DB, $this->TABLE_NAME);
            $figli = $this->caricaFigliRicorsivo($data['CURRENT_RECORD']['tableData'], $chiavi);

            if(is_array($figli)){
                foreach ($figli as &$figlio) {
                    $modify = false;
                    foreach ($this->propagateToChild as $field => $mode) {
                        if ($mode == 'always') {
                            if ($figlio[$field] != $data['CURRENT_RECORD']['tableData'][$field]) {
                                $modify = true;
                                $figlio[$field] = $data['CURRENT_RECORD']['tableData'][$field];
                            }
                        } else {
                            $action = $this->$mode($data['CURRENT_RECORD']['tableData'], $figlio);
                            if ($action) {
                                $modify = true;
                            }
                        }
                    }
                    if ($modify === false) {
                        unset($figlio);
                    }
                }
            }
        }
        return $figli;
    }

}

