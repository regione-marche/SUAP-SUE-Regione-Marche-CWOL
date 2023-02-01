<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';
include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FES.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibSoggetti.class.php';

function cwbBtaSoginv() {
    $cwbBtaSoginv = new cwbBtaSoginv();
    $cwbBtaSoginv->parseEvent();
    return;
}

class cwbBtaSoginv extends cwbBpaGenTab {
    
    private $libSoggetti;
    
    private $tipo_invio = array('Cartaceo', 'E-mail');
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaSoginv';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    /*
     * ELENCO CAMPI SU TABELLA MA NON GESTITI PERCHE' OBSOLETI O INUTILIZZATI:
     */
    protected function initVars() {
        $this->TABLE_NAME = 'BTA_SOGINV'; // Non Servirebbe
        $this->GRID_NAME = 'gridBtaSoginv';
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 19; // Gestione Soggetti 
        
        $this->libDB = new cwbLibDB_BTA();
        $this->libDB_FES = new cwfLibDB_FES();
        $this->libSoggetti = new cwbLibSoggetti(); // Invio Comunicazione
        
        $this->EXT_PROGSOGG = null;    // Dati Passati dal Chiamante
        if (isSet($_POST['external_PROGSOGG'])) {
            $this->EXT_PROGSOGG = $_POST['external_PROGSOGG']; // Acquisisco da Parametro Passato
        } else {
                // Chiamate External
            $this->EXT_PROGSOGG = cwbParGen::getFormSessionVar($this->nameForm, 'post_progsogg');
        }
        
        $this->searchOpenElenco = true;
        $this->openDetailFlag = true;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;

        // Indica quali schermate aprire dopo aver creato, modificato o cancellato un elemento
        $this->actionAfterNew = self::GOTO_LIST; 
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->errorOnEmpty = false;
        
        $this->skipAuth = false;
        
        $this->prefisso_cliccabili = "span_custominfo_"; // prefisso per Icone Cliccabili per Informazioni
        $this->prefisso_idmail = 'MAIL_'; // Prefisso per le MAIL cliccabili nella Grid
        $this->prefisso_VISDOCINV = 'VISDOCINV_'; // Prefisso per VISDOCINV cliccabile nella Grid
        
    }


    protected function preDestruct() {
        if ($this->close != true) {
                // Chiamate External
            cwbParGen::setFormSessionVar($this->nameForm, 'post_progsogg', $this->EXT_PROGSOGG);
        }
    }

    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                // Inizializzo oggetti
                $this->initComboboxes();
                $this->initTable();
                $this->initForm();
                break;
            default:
                break;
        }
    }
    
    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                break;
            case 'onClick':
                switch($_POST['id']){
                    // Apre Mail in Visualizzazione su Edit
                    case $this->nameForm . '_edit_APRI_IDMAIL_butt':
                        $rowid = trim($_POST[$this->nameForm . '_BTA_SOGINV']['IDMAIL']);
                        if (!empty($rowid)) {
                            $this->libSoggetti->apriVisualizzatoreEmail($rowid);
                        } else {
                            Out::msgInfo('Email', 'Nessuna E-mail consultabile.');
                        }
                        break;
                    
                    // Edit Lookup del Soggetto
                    case $this->nameForm . '_ricerca_PROGSOGG_butt':
                        $this->apriFinestraBtaSogg(trim($_POST[$this->nameForm . '_ricerca_PROGSOGG']), 'PROGSOGG', 'return_ricercaProgsogg');
                        break;
                    case $this->nameForm . '_BTA_SOGINV[PROGSOGG]_butt':
                        $this->apriFinestraBtaSogg(trim($_POST[$this->nameForm . '_BTA_SOGINV']['PROGSOGG']), 'PROGSOGG', 'return_editProgsogg');
                        break;
                    
                    // Edit Lookup della Natura Comunicazione
                    case $this->nameForm . '_ricerca_NATURACOMU_butt':
                        $this->apriFinestraBtaNtnote(trim($_POST[$this->nameForm . '_ricerca_NATURACOMU']), 'NATURACOMU', 'return_ricercaNaturacomu');
                        break;
                    case $this->nameForm . '_BTA_SOGINV[NATURACOMU]_butt':
                        $this->apriFinestraBtaNtnote(trim($_POST[$this->nameForm . '_BTA_SOGINV']['NATURACOMU']), 'NATURACOMU', 'return_editNaturacomu');
                        break;
                    
                    default:
                        // ID Particolari (elemento cliccabile) Controllo Icona
                            // Pulisco dell'eventuale: $this->nameForm
                        $nome_pulito = strtoupper(str_replace($this->nameForm . '_','',$_POST['id']));
                        $len_pref_clicc = strlen($this->prefisso_cliccabili);
                        if ( strlen($nome_pulito) > $len_pref_clicc ) {
                            $key = str_replace(substr($nome_pulito,0,$len_pref_clicc),"",$nome_pulito);

                       // Qui ci sono quelli non Informazioni (es. vis. FES_DOCINV)
                            if ( strlen($key) > strlen($this->prefisso_VISDOCINV) && substr($key,0,strlen($this->prefisso_VISDOCINV))==$this->prefisso_VISDOCINV) {
                                $idSoginv = str_replace(substr($key,0,strlen($this->prefisso_VISDOCINV)),"",$key);
                                if(!empty($idSoginv)){
                                    $this->visComDocinv($idSoginv);
                                } else {
                                    Out::msgStop("Errore", "Comunicazione non correttamente selezionata");
                                    return;
                                }
                            } else {
                                // Qui ci sono quelli non Informazioni (es. Email)
                                if ( strlen($key) > strlen($this->prefisso_idmail) && substr($key,0,strlen($this->prefisso_idmail))==$this->prefisso_idmail) {
                                    // Chiave della Email
                                    $rowid = str_replace(substr($key,0,strlen($this->prefisso_idmail)),"",$key);
                                    $this->libSoggetti->apriVisualizzatoreEmail($rowid);
                                }                                
                            }  
                            
                        }
                        break;          
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    // Codice del Soggetto
                    case $this->nameForm . '_ricerca_PROGSOGG':
                        $this->setProgsoggFromDB(trim($_POST[$this->nameForm . '_ricerca_PROGSOGG']), '_ricerca_PROGSOGG' );
                        break;
                    case $this->nameForm . '_BTA_SOGINV[PROGSOGG]':
                        $this->setProgsoggFromDB(trim($_POST[$this->nameForm . '_BTA_SOGINV']['PROGSOGG']), '_BTA_SOGINV[PROGSOGG]', '_edit' );
                        break;
                    
                    // Codice della Natura Comunicazione
                    case $this->nameForm . '_ricerca_NATURACOMU':
                        $this->setBtaNtnoteFromDB(trim($_POST[$this->nameForm . '_ricerca_NATURACOMU']), '_ricerca_NATURACOMU' );
                        break;
                    case $this->nameForm . '_BTA_SOGINV[NATURACOMU]':
                        $this->setBtaNtnoteFromDB(trim($_POST[$this->nameForm . '_BTA_SOGINV']['NATURACOMU']), '_BTA_SOGINV[NATURACOMU]', '_edit' );
                        break;
                    
                    // Ricerca: Data Invio congrua
                    case $this->nameForm . '_ricerca_DATAINVIO_INI':
                    case $this->nameForm . '_ricerca_DATAINVIO_FIN':
                        $DATIN = trim($_POST[$this->nameForm . '_ricerca_DATAINVIO_INI']);
                        $DATFI = trim($_POST[$this->nameForm . '_ricerca_DATAINVIO_FIN']);
                        if (!empty($DATIN) && !empty($DATFI)) {
                            if (date('Ymd',strtotime($DATIN)) > date('Ymd',strtotime($DATFI))) {
                                Out::msgInfo('Date incongruenti', 'Data Finale antecedente a quella Iniziale.<br />Reinserire data Finale corretta.');
                                Out::valore($this->nameForm . '_ricerca_DATAINVIO_FIN', null); // Azzero la finale
                            }
                        }
                        break;
                }
                break;
            case 'onBlur':
                break;
            case 'addGridRow':
                break;
            case 'delGridRow':
                break;
            
            // Rientri da Lookup
            case 'return_ricercaProgsogg':
                $this->setProgsogg($this->formData['returnData'], '_ricerca_PROGSOGG');
                break;
            case 'return_editProgsogg':
                $this->setProgsogg($this->formData['returnData'], '_BTA_SOGINV[PROGSOGG]', '_edit');
                break;
            case 'return_ricercaNaturacomu':
                $this->setBtaNtnote($this->formData['returnData'], '_ricerca_NATURACOMU');
                break;
            case 'return_editNaturacomu':
                $this->setBtaNtnote($this->formData['returnData'], '_BTA_SOGINV[NATURACOMU]', '_edit');
                break;
        }
    }

        
    private function visComDocinv($idSoginv){   // comDocinv**   tutto il metodo
        $filtri = array(
            'ID_SOGINV'=>$idSoginv
        );
        $fesDocinv = $this->libDB_FES->leggiFesDocinv($filtri);   // comCort**
        
        if (count($fesDocinv)>0){
            $externalFilter = array();
            $externalFilter['E_S'] = 'E'; // Solo Entrata
            $externalFilter['ID_SOGINV'] = $idSoginv;
            $this->apriListaFatture($externalFilter);
        } else {
            Out::msgInfo("Info", "Non ci sono documenti collegati alla comunicazione da visualizzare.");   
        }
    }
    
    /*
     * Apre Finestra Testata Documenti
     */
    private function apriListaFatture($filtri_externalParams) {
        // Parametri da Passare
        $external_Params = $filtri_externalParams; // Filtri Passati per Selezione
            // Finora in tutte le chiamate c'e' un filtro su DATADOCUM o
            // DATASCADE o ANNNORIF_lt per cui lo assegno; se poi serve una
            // Lista con ANNORIF=Anno Contabile, allora andra' tolto.
        $external_Params['EVITA_FILTRO_ANNORIF'] = true; // Evito Filtro Fisso ANNORIF
            // Se Spesa mette fisso FLAG_TIPMO < 3 per cui non metto nulla
            // mentre per le Entrate ha il filtro diverso.

        //  8: non include i Provvisori
        // 11: deve includere i Provvisori
        $external_Params['INCLUDI'] = 8; // (FES_DOCTES_V01.FLAG_TIPDO < 3 OR FES_DOCTES_V01.FLAG_TIPDO = 7 OR FES_DOCTES_V01.PROG_TIPMO <> 0) AND FES_DOCTES_V01.FLAG_MOVPR = 0
        
        $postData = array('external_E_S' => 'E'); // E_S Obbligatoria
        cwbLib::apriFinestraRicerca('cwfFesDoctes', $this->nameForm, '', '', true, $external_Params, $this->nameFormOrig, '', $postData);
    }
    
    /* 
     * Inizializzo le ComboBox Fisse (solo all'inizio)
     */
    private function initComboboxes(){
            // Tipo Invio
        Out::html($this->nameForm . '_BTA_SOGINV[TIPO_INVIO]','');
        Out::select($this->nameForm . '_BTA_SOGINV[TIPO_INVIO]', 1, 0, 0, $this->tipo_invio[0]);
        Out::select($this->nameForm . '_BTA_SOGINV[TIPO_INVIO]', 1, 1, 0, $this->tipo_invio[1]);
        
            // Tipo Invio
        Out::html($this->nameForm . '_ricerca_TIPO_INVIO','');
        Out::select($this->nameForm . '_ricerca_TIPO_INVIO', 1, 0, 1, 'Tutti');
        Out::select($this->nameForm . '_ricerca_TIPO_INVIO', 1, 1, 0, 'Solo '.$this->tipo_invio[0]);
        Out::select($this->nameForm . '_ricerca_TIPO_INVIO', 1, 2, 0, 'Solo '.$this->tipo_invio[1]);
    }
    
    /* 
     * Inizializzo la Grid (Intestazione Colonne ed Allineamento) (solo all'inizio)
     */
    private function initTable(){
    }
    
    /*
     * Inizializzo il Form (solo la prima volta)
     */
    private function initForm(){
            // Select Filtro su Colonna 
        $html = '<select id="'.$this->nameForm.'_gs_TIPO_INVIO" name="TIPO_INVIO" style="width:100%"></select>';
        Out::gridSetColumnFilterHtml($this->nameForm, $this->GRID_NAME, 'TIPO_INVIO', $html);
        Out::select($this->nameForm.'_gs_TIPO_INVIO', 1, 0, 1, 'Tutti');
        Out::select($this->nameForm.'_gs_TIPO_INVIO', 1, 1, 0, 'Solo '.$this->tipo_invio[0]);
        Out::select($this->nameForm.'_gs_TIPO_INVIO', 1, 2, 0, 'Solo '.$this->tipo_invio[1]);
    }
    
    private function azzeraForm(){
        // Nascondo Flag Disabilitato
        Out::hide($this->nameForm . '_edit_divFlag_Dis');
    }

    /*
     * Predispongo Form per Nuovo Elemento
     */
    protected function preNuovo() {
        $this->azzeraForm();
    }
    /*
     * Predispongo Form per Modifica Elemento
     */
    protected function preDettaglio($index, &$sqlDettaglio = null) {
        $this->azzeraForm();
        $this->viewMode = true; // Mai modificabile
    }
    
    /*
     * Visualizzo/Nascondo ed assegno decodifiche lookup
     */
    protected function postDettaglio($index, &$sqlDettaglio = null) {
        // Visualizzo Flag Disabilitato
        Out::show($this->nameForm . '_edit_divFlag_Dis');
        
        $this->setProgsoggFromDB($this->CURRENT_RECORD['PROGSOGG'], '_BTA_SOGINV[PROGSOGG]', '_edit');
        $this->setBtaNtnoteFromDB($this->CURRENT_RECORD['NATURACOMU'], '_BTA_SOGINV[NATURACOMU]', '_edit');
    }

    /* 
     * Costruzione della Query di Selezione (dopo la Ricerca)
     */
    protected function postSqlElenca($filtri, &$sqlParams = array()) {
        
        // FILTRI EXTERNAL (HANNO PRIORITA')
        if (!empty($this->EXT_PROGSOGG)) {
            $filtri['PROGSOGG'] = $this->EXT_PROGSOGG;
        }
        
        if(empty($filtri['PROGSOGG'])){
            if(!empty($this->formData[$this->nameForm.'_ricerca_PROGSOGG']) && $this->formData[$this->nameForm.'_ricerca_PROGSOGG'] != '0' ){
                $filtri['PROGSOGG'] = trim($this->formData[$this->nameForm.'_ricerca_PROGSOGG']);
            }
        }
        
        if(empty($filtri['TIPO_INVIO'])){
            if(!empty($this->formData[$this->nameForm.'_ricerca_TIPO_INVIO'])){
                $filtri['TIPO_INVIO'] = (($this->formData[$this->nameForm.'_ricerca_TIPO_INVIO']) - 1);
            }
        }
        
        if(empty($filtri['NATURACOMU'])){
            if(!empty($this->formData[$this->nameForm.'_ricerca_NATURACOMU'])){
                $filtri['NATURACOMU'] = trim($this->formData[$this->nameForm.'_ricerca_NATURACOMU']);
            }
        }
        
        // DATA INSERT: E' UTILIZZATA ANCHE COME DATA INVIO:
        if (empty($filtri['DATAINSER_DA'])) {
            if(!empty($this->formData[$this->nameForm.'_ricerca_DATAINVIO_INI'])){
                $filtri['DATAINSER_DA'] = trim($this->formData[$this->nameForm.'_ricerca_DATAINVIO_INI']);
            }
        }
        if (empty($filtri['DATAINSER_A'])) {
            if(!empty($this->formData[$this->nameForm.'_ricerca_DATAINVIO_FIN'])){
                $filtri['DATAINSER_A'] = trim($this->formData[$this->nameForm.'_ricerca_DATAINVIO_FIN']);
            }
        }
        
        $filtri['TABLENOTE'] = $this->TABLE_NAME;
        
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaSoginv($filtri, false, $sqlParams);

    }

    /* 
     * Costruzione della Query del Singolo Record (andra' in Modifica)
     */
    protected function sqlDettaglio($index, &$sqlParams) {
        $filtri = array();
        $filtri['ID_SOGINV'] = $index;
        $filtri['TABLENOTE'] = $this->TABLE_NAME;
             // Anche quelli Disabilitati
        $this->SQL = $this->libDB->getSqlLeggiBtaSoginv($filtri, false, $sqlParams);
    }

    /* 
     * Elaborazione dei Record prima di visualizzarli nella Grid
     */
    protected function elaboraRecords($Result_tab) {
        if(is_array($Result_tab)){
            foreach ($Result_tab as $key => $Result_rec) {
                // Soggetto
                $Result_tab[$key]['PROGSOGG'] = $this->grassetto($Result_rec['PROGSOGG']).') '.trim($Result_rec['RAGSOC']);
                // Natura Comunicazione
                $Result_tab[$key]['NATURACOMU'] = $this->grassetto($Result_rec['NATURACOMU']).') '.trim($Result_rec['DESNATURA']);
                // Tipo Invio
                $Result_tab[$key]['TIPO_INVIO'] = $this->tipo_invio[$Result_rec['TIPO_INVIO']];
                // ID Mail
                $idmail = '';
                if ($Result_rec['TIPO_INVIO'] == 1) {
                    // Solo per le Email e' Disponibile
                    if ($Result_rec['IDMAIL']) {
                        $idmail = $this->crea_cliccabile($this->prefisso_idmail.$Result_rec['IDMAIL'],'Consulta la Email Inviata','ui-icon-email');
                    } else {
                        $idmail = '<span title="Email errata o mancante" style="color:red; font-size: 18px;" class="ui-icon ui-icon-email"></span>';
                    }
                }
                $Result_tab[$key]['IDMAIL'] = $idmail;
                // Data/Ora Invio prende quello di Inserimento
                $data_invio = date('d-m-Y',strtotime($Result_rec['DATAINSER'])).' ore '.$Result_rec['TIMEINSER'];
                $Result_tab[$key]['DATA_INVIO'] = $data_invio;
                
                $icone = $this->elaboraIcone($Result_rec);
                $filtri = array(
                    'TIPO_COM'=>$Result_rec['TIPO_COM']
                );
                $btaTipcom = $this->libDB->leggiGeneric('BTA_TIPCOM', $filtri, false);
                $flagTipco = $btaTipcom['FLAG_TIPCO'] ? ' (' . $btaTipcom['FLAG_TIPCO'] . ')' : '';
                $Result_tab[$key]['FLAG_TIPCO_DES'] = $btaTipcom['DES_TIPCOM'] . $flagTipco;
//          Icona comunicazioni inviate
                $Result_tab[$key]['FLAG_TIPCO_DES'] .= $icone['ICO_DOCINV'];    
            }
        }
        return $Result_tab;
    }
    
        
    private function elaboraIcone($row){
        $return = array();
        $return['ICO_DOCINV'] = '';
        
    // Ci sono delle comunicazioni inviate ? 
        $des_docinv = '';
        $docinv = '';
        $alte_docinv = '';
        $filtri = array(
            'ID_SOGINV'=>$row['ID_SOGINV']
        );
        $fesDocinv = $this->libDB_FES->leggiFesDocinv($filtri);   // comCort**
        if (!empty($fesDocinv) && count($fesDocinv)>0){
            $rowInv = $fesDocinv[0];
            $dataoper = date('d-m-Y', strtotime($rowInv['DATAOPER']));
            $des_docinv .= count($fesDocinv)>1 ? (count($fesDocinv) . ' documenti comunicati ') : 'Un documento comunicato il ' . $dataoper;
//            $des_docinv .= $rowInv['TIPO_INVIO']? ' inviata via e-mail il ' : ' cartacea';
            $des_docinv .= '. Click per consultare ';
            $docinv = $this->crea_cliccabile($this->prefisso_VISDOCINV . $row['ID_SOGINV'], $des_docinv, 'ui-icon-note');
            $alte_docinv = 'max-height:18px;'; 
            $return['ICO_DOCINV'] = '<div title="'.$des_docinv.'" style="text-align:left;float:left;'.$alte_docinv.'"> '.$docinv.' </div>';
        }
        
        return $return;
    }
    
    
    /*
     * Quando esegue "Altra Ricerca"
     * Prima di un altra ricerca abblenco filtri su Colonne della jQGrid
     */
    protected function preAltraRicerca() {
        Out::gridCleanFilters($this->nameForm, $this->GRID_NAME);
    }
    
    /*
     * Settaggio dei Filtri sulle Colonne della jqGrid
     */
    protected function setGridFilters() {
        $this->gridFilters = array();
        
        // Soggetto: Se Intero valido sul Progressivo alrimenti sempre sulla Descrizione
        $soggetto = trim($_POST['PROGSOGG']);
        if(!empty($soggetto)){
            if( is_numeric($soggetto) && !is_float($soggetto) && intval($soggetto) > 0 ){
                // Numero Intero valido: Controllo il Progressivo
                $this->gridFilters['PROGSOGG'] = $soggetto;
            } else {
                // Testo: Controllo la Descrizione
                $this->gridFilters['RAGSOC'] = $soggetto;
            }
        }
        
        if (!empty($_POST['TIPO_INVIO'])) {
            $this->gridFilters['TIPO_INVIO'] = ($_POST['TIPO_INVIO'] - 1);
        }
        
        if (!empty($_POST['NATURACOMU'])) {
            $this->gridFilters['NATURACOMU'] = $_POST['NATURACOMU'];
        }
        
    }
    
    /**
     * Ci sono Colonne con piu' Campi ed il nome colonna non corrisponde ad
     * alcun campo; qui provvedo alla sistemazione corretta dei campi
     * da riordinare (dopo click sulla Colonna)
     */
    protected function initializeTable($sqlParams, &$sortIndex, &$sortOrder) {
        switch($sortIndex){
            case 'DATA_INVIO':
                $sortIndex = array();
                $sortIndex[] = 'DATAINSER';
                $sortIndex[] = 'TIMEINSER';
                break;
        }
        // Default e' sempre Ascendente
        if(empty($sortOrder)){
            $sortOrder = 'asc';
        }
        // Ora richiama la modalita' Standard
        return parent::initializeTable($sqlParams, $sortIndex, $sortOrder);
    }
    
    /*
     * Apre Finestra dei Soggetti (solo Finanziaria)
     */
    private function apriFinestraBtaSogg($progsogg, $campo, $return){
        $apro_lista = false;
        $postData = array();
//        $postData = array(
//            'soggOrigin_CLIFOR'=>true // Per la sola Finanziaria
//        );
        $externalFilter = array();
//        $externalFilter['QUALIVEDO'] = array();
//        $externalFilter['QUALIVEDO']['PERMANENTE'] = false;
//        $externalFilter['QUALIVEDO']['VALORE'] = 4;
        if(!empty($progsogg)){
            $externalFilter['PROGSOGG'] = array();
            $externalFilter['PROGSOGG']['PERMANENTE'] = false;
            $externalFilter['PROGSOGG']['VALORE'] = $progsogg;
            $apro_lista = true;
        }
        
        cwbLib::apriFinestraRicerca('cwbBtaSogg', $this->nameForm, $return, $campo, $apro_lista, $externalFilter, $this->nameFormOrig, '', $postData);
    }
    
    private function setProgsogg($data, $campo='', $prefi=''){
        Out::valore($this->nameForm . $campo, $data['PROGSOGG']);
        Out::valore($this->nameForm . $prefi.$campo.'_DESC', (trim($data['RAGSOC'])) );
    }
    
    private function setProgsoggFromDB($progsogg, $campo='', $prefi=''){
        $data = null;
        if(!empty($progsogg)){
            $filtri = array(
                'PROGSOGG'=>$progsogg
            );
            $data = $this->libDB->leggiBtaSogg($filtri, false);
        }
        $this->setProgsogg($data, $campo, $prefi);
    }
    
    /*
     * Apre Finestra della Natura Comunicazione
     */
    private function apriFinestraBtaNtnote($natura_comun, $campo, $return){
        $apro_lista = false;
        
        $postData = array();
        
        $externalFilter = array();
        if(!empty($natura_comun)){
            $externalFilter['NATURANOTA'] = array();
            $externalFilter['NATURANOTA']['PERMANENTE'] = false;
            $externalFilter['NATURANOTA']['VALORE'] = $natura_comun;
            $apro_lista = true;
        }
        
        cwbLib::apriFinestraRicerca('cwbBtaNtnote', $this->nameForm, $return, $campo, $apro_lista, $externalFilter, $this->nameFormOrig, '', $postData);
    }
    
    private function setBtaNtnote($data, $campo='', $prefi=''){
        Out::valore($this->nameForm . $campo, $data['NATURANOTA']);
        Out::valore($this->nameForm . $prefi.$campo.'_DESC', (trim($data['DESNATURA'])) );
    }
    
    private function setBtaNtnoteFromDB($naturacomu, $campo='', $prefi=''){
        $data = null;
        if(!empty($naturacomu)){
            $filtri = array(
                'NATURANOTA'=>$naturacomu,
                'TABLENOTE'=>$this->TABLE_NAME
            );
            $data = $this->libDB->leggiBtaNtnote($filtri, false);
        }
        $this->setBtaNtnote($data, $campo, $prefi);
    }

    /**
     * Crea Icona INFO (o testo) Cliccabile
     */
    private function crea_cliccabile($key, $tooltip='Visualizza Dati', $icona='ui-icon-info') {
        $img="<span class='ui-icon ".$icona."'></span>"; // Icona standard UI
        $id = $this->nameForm . "_" .$this->prefisso_cliccabili.$key;
        $html = "<span id='".$id."'>".$img."</span>";
        $icona_cliccabile = cwbLibHtml::getHtmlClickableObject($this->nameForm, $id, $img, array(),$tooltip);
        return $icona_cliccabile;
    }
    /**
     * Varie utilita
     */
    private function corsivo($testo){
        return "<span style='font-style: italic;'>" . $testo . "</span>";
    }
    
    private function grassetto($testo){
        return "<span style='font-weight: bold;'>" . $testo . "</span>";
    }
    
}

?>