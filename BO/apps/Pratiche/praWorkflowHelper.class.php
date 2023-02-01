<?php
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibPasso.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praPasso.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFunzionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDatiWorkFlow.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibAssegnazionePassi.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praDiagrammaViewer.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

class praWorkflowHelper {
    
    private $praLib;
    private $praLibPasso;
    private $proLib;
    private $praPasso;  // N.B.: Utilizzato solamente per operazioni CRUD (insertRecord, updateRecord, deleteRecord)

    public function __construct() {
        $this->praLib = new praLib();
        $this->praLibPasso = new praLibPasso();
        $this->proLib = new proLib();   
        $this->praPasso = new praPasso();
    }

    /**
     * Innesca nuova istanza di workflow
     * @param array $datiInnesco
     *      - GESPRO            : Codice procedimento per statistiche
     *      - GESWFPRO          : Cocice procedimento workflow 
     *      - GESCODPROC        : Codice procedura esterna (facoltativo)
     *      - GESRES            : Codice responsabile procedimento 
     *      - GESDRE            : Data registrazione  
     *      - ITEEVT            : Codice evento iter
     *      - DATI_AGGIUNTIVI   : Dati aggiuntivi pratica
     *      - DATI_ASSEGNAZIONE : Dati prima assegnazione
     *          CHIAVE_PASSO =>
     *              array(array( 
     *                  - SOGGETTO_INTERNO          : se empty, viene assegnato a tutto l'ufficio e ruolo in attesa di acquisizione 
     *                  - UFFICIO                   : ufficio/unità organizzativa
     *                  - RUOLO                     : ruolo (facoltativo)
     *                  - DATA_TERMINE_RICHIESTA    : data oltre la quale l'assegnazione è scaduta (promemoria)
     *              ))
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Errore di validazione
     *          2 = Errore di validazione e preparazione dati aggiuntivi
     *          3 = Errore richiesta acquisizione pratica
     *          4 = Assegnazione passi pratica fallita
     *      - MESSAGGIO: eventuale messaggio di errore
     *      - DATI: dati richiesta acquisizione pratica
     */
    public function innescaWorkflow($datiInnesco) {
        // Inizializza strutura dati di ritorno
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );

        // Effettua validazione dati innesco
        $esitoValidazione = $this->validaDatiInnesco($datiInnesco);
        if ($esitoValidazione['ESITO'] !== 0) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = $esitoValidazione['MESSAGGIO'];
            return $toReturn;
        }

        // Normalizza dati richiesta
        $arrDatiRichiesta = $this->getDatiAcquisizioneRichiesta($datiInnesco);
        if ($arrDatiRichiesta === false) {
            $toReturn['ESITO'] = 2;
            $toReturn['MESSAGGIO'] = "Dati Aggiuntivi dichiarati non validi per il procedimento";
            return $toReturn;
        }
        // Effettua acquisizione richiesta
        $modelObj = itaModel::getInstance("praGest2");
        $praLibPratica = praLibPratica::getInstance();
        $retAcq = $praLibPratica->acquisizioneRichiesta($arrDatiRichiesta, $modelObj);

        // Imposta dati di ritorno in funzione dell'esito acquisizione richiesta
        if ($retAcq['Status'] == "-1") {
            $toReturn['ESITO'] = 3;
            $toReturn['MESSAGGIO'] = "Inserimento fallito: " . $retAcq['Message'];
        } else {
            $toReturn['DATI'] = $retAcq;
            // Assegnazione passi
            if (isset($datiInnesco['DATI_ASSEGNAZIONE']) && count($datiInnesco['DATI_ASSEGNAZIONE'] > 0)) {
                $resultAssegnaPassi = $this->assegnaPassi($datiInnesco['DATI_ASSEGNAZIONE'], $retAcq['GESNUM']);
                if ($resultAssegnaPassi['ESITO'] !== 0) {
                    $toReturn['ESITO'] = 4;
                    $toReturn['MESSAGGIO'] = "Assegnazione passi pratica fallita. ( " . $resultAssegnaPassi['MESSAGGIO'] . " )";
                }
            }
        }

        return $toReturn;
    }    

    /**
     * Apri finestra di gestione passo
     * @param obj $objParent oggetto richiamante (es. scrivania)
     * @param array $arcite_rec record 
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Esito fallito - Classe destinataria non implementa praWorkflowInterface
     *      - MESSAGGIO: eventuale messaggio di errore
     *      - DATI: dati richiesta acquisizione pratica
     */
    public function apriGestionePassoDaAssegnazione($objParent, $arcite_rec) {        
        $propas_rec = $this->praLib->GetPropas($arcite_rec['ITEPRO'], "paspro", false, $arcite_rec['ITEPAR']);
        return $this->apriGestionePasso($objParent, $propas_rec);
    }

    /**
     * Apri finestra di gestione passo
     * @param obj $objParent oggetto richiamante (es. scrivania)
     * @param array $propas_rec record di PROPAS
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Esito fallito - Classe destinataria non implementa praWorkflowInterface
     *      - MESSAGGIO: eventuale messaggio di errore
     *      - DATI: dati richiesta acquisizione pratica
     */
    public function apriGestionePasso($objParent, $propas_rec) {
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );        
        $funzionePasso = $this->praLibPasso->getFunzionePassoBO($propas_rec);
        if ($funzionePasso['FUNZIONE'] == praFunzionePassi::FUN_GEST_DIP) {
            $model = $funzionePasso['DATA']['CLASSE'];
            itaLib::openForm($model);
            $objModel = itaFrontController::getInstance($model);
            if (!$objModel instanceof praWorkflowInterface) {
                $toReturn = array(
                    'ESITO' => 1,
                    'MESSAGGIO' => 'La classe ' . $model . ' non implementa l\'interfaccia praWorkflowInterface'
                );
                return $toReturn;
            }

            $workflowData = array("PASSO" => $propas_rec, "OPTIONS" => array());

            $objModel->setExternalRefKey('WORKFLOW', $workflowData);
            $objModel->setReturnModel($objParent->nameForm);
            $objModel->apriDaWorkflow($workflowData);
        } else {
            $model = 'praPasso';
            itaLib::openForm($model);
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent('openform');
            $objModel->setReturnModel($objParent->nameForm);
            $_POST['rowid'] = $propas_rec['ROWID'];
            $_POST['daTrasmissioni'] = true;
            $_POST['modo'] = "edit";
            $_POST['perms'] = $objParent->getPerms();
            $objModel->parseEvent();
        }

        $toReturn['DATI'] = $propas_rec;

        return $toReturn;
    }
    
    /**
     * Salva i dati aggiuntivi di pratica e di passo, chiude il passo, cambia l'assegnazione.
     * @param array $parametriSincronizzazione array in forma
     *      - PRATICA
     *          - ID
     *          - DATI
     *      - PASSO
     *          - ID
     *          - DATI
     *          - DATA_CHIUSURA
     *      - DATI_ASSEGNAZIONE
     *          - SOGGETTO_INTERNO
     *          - UFFICIO
     *          - RUOLO                 
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Errore modifica dati aggiuntivi pratica
     *          2 = Errore modifica dati aggiuntivi passo
     *          3 = Errore chiusura passo
     *          4 = Errore avanzamento passo
     *          5 = Errore assegnazione passo
     *      - MESSAGGIO: eventuale messaggio di errore     
     */
    public function sincronizzaWorkflow($parametriSincronizzazione) {
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );
        
        // Effettua validazione dei dati di sincronizzazione
        $esitoValidazione = $this->validaDatiSync($parametriSincronizzazione);
        if ($esitoValidazione['ESITO'] !== 0) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = $esitoValidazione['MESSAGGIO'];
            return $toReturn;
        }
        
        // Modifica dati aggiuntivi di pratica
        if(!empty($parametriSincronizzazione['PRATICA']['DATI'])){
            $result = $this->modificaDatiAggiuntivi($parametriSincronizzazione['PRATICA']['ID'], $parametriSincronizzazione['PRATICA']['DATI']);
            if ($result['ESITO'] !== 0) {
                $toReturn['ESITO'] = 1;
                $toReturn['MESSAGGIO'] = 'Errore modifica dati aggiuntivi pratica (' . $toReturn['MESSAGGIO'] . ')';
                return $toReturn;
            }
        }
        
        // Modifica dati aggiuntivi del passo
        if(!empty($parametriSincronizzazione['PASSO']['DATI'])){
            $result = $this->modificaDatiAggiuntivi($parametriSincronizzazione['PASSO']['ID'], $parametriSincronizzazione['PASSO']['DATI']);
            if ($result['ESITO'] !== 0) {
                $toReturn['ESITO'] = 2;
                $toReturn['MESSAGGIO'] = 'Errore modifica dati aggiuntivi passo (' . $toReturn['MESSAGGIO'] . ')';
                return $toReturn;
            }
        }
        
        // Chiusura passo
        if(!empty($parametriSincronizzazione['PASSO']['DATA_CHIUSURA'])){
            $result = $this->chiudiPasso($parametriSincronizzazione['PASSO']['ID'], $parametriSincronizzazione['PASSO']['DATA_CHIUSURA']);
            if (!$result) {
                $toReturn['ESITO'] = 3;
                $toReturn['MESSAGGIO'] = 'Errore chiusura passo';
                return $toReturn;
            }
            $passoSuccessivoRow = $this->avanzaPasso($parametriSincronizzazione['PRATICA']['ID'], $parametriSincronizzazione['PASSO']['DATA_CHIUSURA']);
            if ($passoSuccessivoRow === false) {
                $toReturn['ESITO'] = 4;
                $toReturn['MESSAGGIO'] = 'Errore avanzamento passo';
                return $toReturn;
            }
            $passoSuccessivo = $passoSuccessivoRow['PROPAK'];
            
            // Assegnazione passo
            if(!empty($parametriSincronizzazione['DATI_ASSEGNAZIONE'])){
                $result = $this->assegnaPassi(array($passoSuccessivo=>$parametriSincronizzazione['DATI_ASSEGNAZIONE']));
                if ($result['ESITO'] !== 0) {
                    $toReturn['ESITO'] = 5;
                    $toReturn['MESSAGGIO'] = 'Errore assegnazione passo (' . $toReturn['MESSAGGIO'] . ')';
                    return $toReturn;
                }
            }
        }
        
        return $toReturn;        
    }

    /**
     * Recupera dati innesco dato il contesto applicativo
     * (Effettua ricerca nella tabella BGE_WFPARAMS)
     * @param string $contestoApp Chiave del contesto applicativo
     * @return mixed Dati innesco se esito positivo, altrimenti false
     */
    public function getDatiInnescoDaContestoApp($contestoApp) {
        $libDB = new cwbLibDB_GENERIC();
        $return = $libDB->leggiGeneric('BGE_WFPARAMS', array('CONTESTO_APP' => $contestoApp), false);
        if(!$return){
            return false;
        }
        $return['GESVFPRO'] = str_pad($return['GESVFPRO'], 6, '0', STR_PAD_LEFT);
        $return['GESPRO'] = str_pad($return['GESPRO'], 6, '0', STR_PAD_LEFT);
        
        return $return;
    }
    
    /**
     * Recupera contesto workflow
     * @param int $gesnum Chiave pratica
     * @return \praLibDatiWorkFlow
     */
    public function getContestoWorkflow($gesnum) {
        $datiWf = new praLibDatiWorkFlow($gesnum);
        return $datiWf;
    }
    
    /**
     * Visualizza diagramma workflow
     * @param \praLibDatiWorkFlow Contesto workflow
     */
    public function visualizzaDiagramma($contesto){
        itaLib::openForm('praDiagrammaViewer');
        $obj = itaModel::getInstance('praDiagrammaViewer', 'praDiagrammaViewer');
        $obj->setDatiWF($contesto);
        $obj->setEvent('openform');
        $obj->parseEvent();
    }
    
    /**
     * Effettua assegnazione dei passi della pratica
     * @param array $datiAssegnazione
     *          CHIAVE_PASSO => //Attualmente viene preso un solo passo, in futuro potrebbe venir prevista l'assegnazione di più passi contemporaneamente
     *              array(array( 
     *                  - SOGGETTO_INTERNO          : Rif: PROT.ANAMED.MEDCOD (se empty, viene assegnato a tutto l'ufficio e ruolo in attesa di acquisizione)
     *                  - UFFICIO                   : Rif: PROT.ANAUFF.UFFCOD 
     *                  - RUOLO                     : Rif: PROT.ANARUOLI.RUOCOD 
     *                  - DATA_TERMINE_RICHIESTA    : data oltre la quale l'assegnazione è scaduta (promemoria)
     *              ))
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Passo da Assegnare non trovato alla posizione n
     *          2 = Errore inserimento su Anapro
     *          3 = Errore reset Destinatari Interni
     *          4 = Errore inserimento assegnazioni
     *          5 = Errore sincronizzazione iter di assegnazione
     *      - MESSAGGIO: eventuale messaggio di errore
     *      - DATI: dati richiesta acquisizione pratica
     */
    private function assegnaPassi($datiAssegnazione, $pratica = null) {
        // Inizializza strutura dati di ritorno
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );
        if ($pratica) {
            $propas_tab = array_reverse($this->praLib->GetPropassi($pratica, '', 'codice', true));
        }

        // Scorre tutti i passi dell'assegnazione
        foreach ($datiAssegnazione as $keyPasso => $assegnazioni) {
            // Inserisce testata
            if (strpos(trim($keyPasso), "#") === 0) {
                list($skip, $index) = explode("#", trim($keyPasso));
                if (isset($propas_tab[$index])) {
                    $keyPasso = $propas_tab[$index]['PROPAK'];
                } else {
                    $toReturn['ESITO'] = 1;
                    $toReturn['MESSAGGIO'] = "Passo da Assegnare non trovato alla posizione $index";
                    return $toReturn;
                }
            }
            $result = $this->inserisciAnaproPasso($keyPasso);
            if ($result['Status'] !== "0") {
                $toReturn['ESITO'] = 2;
                $toReturn['MESSAGGIO'] = $result['Message'];
                return $toReturn;
            }
            $anapro_rec = $result['anapro_rec'];
            /*
             * Verificare Con @Alessandro Mucci se necessario implemetare
             * 
             */
            App::requireModel('proProtocolla.class');
            $protObj = new proProtocolla();
            $protObj->registraSave("Aggiornamento Passo per assegnazione", $anapro_rec['ROWID'], 'rowid');

            /*
             * Gestione Assegnatari (Inserimento)
             */
            $anades_tab = $this->proLib->GetAnades($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], '');
            if ($anades_tab) {
                foreach ($anades_tab as $key => $anades_rec) {
                    if (!$this->praPasso->deleteRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec['ROWID'], '', 'ROWID', false)) {
                        $toReturn['ESITO'] = 3;
                        $toReturn['MESSAGGIO'] = 'Errore reset Destinatari Interni';
                        return $toReturn;
                    }
                }
            }
            foreach ($assegnazioni as $assegnazione) {
                /*
                 * Inserisco ANADES
                 */
                $anades_rec = array();
                $anades_rec['DESNUM'] = $anapro_rec['PRONUM'];
                $anades_rec['DESPAR'] = $anapro_rec['PROPAR'];
                $anades_rec['DESCOD'] = $assegnazione['SOGGETTO_INTERNO'];
                $anades_rec['DESNOM'] = '';
                $anades_rec['DESIND'] = '';
                $anades_rec['DESCAP'] = '';
                $anades_rec['DESCIT'] = '';
                $anades_rec['DESPRO'] = '';
                $anades_rec['DESDAT'] = '';
                $anades_rec['DESDAA'] = '';
                $anades_rec['DESDUF'] = '';
                $anades_rec['DESANN'] = '';
                $anades_rec['DESMAIL'] = '';
                $anades_rec['DESSER'] = '';
                $anades_rec['DESCUF'] = $assegnazione['UFFICIO'];
                $anades_rec['DESGES'] = 1;
                $anades_rec['DESRES'] = '';
                $anades_rec['DESRUOLO'] = $assegnazione['RUOLO'];
                $anades_rec['DESTERMINE'] = $assegnazione['DATA_TERMINE_RICHIESTA'];
                $anades_rec['DESIDMAIL'] = '';
                $anades_rec['DESINV'] = 0;
                $anades_rec['DESTIPO'] = "T";
                $anades_rec['DESORIGINALE'] = '';
                $anades_rec['DESFIS'] = '';
                $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->praPasso->insertRecord($this->proLib->getPROTDB(), 'ANADES', $anades_rec, $insert_Info)) {
                    $toReturn['ESITO'] = 4;
                    $toReturn['MESSAGGIO'] = 'Errore inserimento assegnazioni';
                    return $toReturn;
                }
            }

            /*
             * Sincronizzo ARICTE
             */

            $iter = proIter::getInstance($proLib, $anapro_rec);
            if (!$iter->sincIterProtocollo()) {
                $toReturn['ESITO'] = 5;
                $toReturn['MESSAGGIO'] = 'Errore sincronizzazione iter di assegnazione';
                return $toReturn;
            }
        }
        return $toReturn;
    }
    
    private function chiudiPasso($propak, $dataChiusura=null){        
        $Propas_rec = $this->praLib->GetPropas($propak, "propak");
        
        if ($Propas_rec['PROFIN']) {
            return false;
        }
        $Propas_rec['PROFIN'] = $dataChiusura ?: date('Ymd');
        if (!$this->praPasso->SincronizzaRecordPasso($Propas_rec)) {
            return false;
        }
        
        $this->praPasso->ChiudiTrasmissioniProtocolloPasso($Propas_rec);
        return true;
    }
    
    private function avanzaPasso($gesnum, $dataApertura=null){
        $contesto = $this->getContestoWorkflow($gesnum);
        $contesto->setPassoSuccessivo(); //SPOSTA PUNTATORE DEL CONTESTO AL PASSO SUCCESSIVO
        return $contesto->getPassoCorrente();
    }
    
    /**
     * Modifica dati aggiuntivi passo/pratica
     * @param string $dagpak - Chiave passo/pratica (dagpak/propak)
     * @param array $datiAggiuntivi
     * @return array Esito operazione
     *      - ESITO: 
     *          0 = Esito positivo
     *          1 = Inserimento dataset fallito
     *          2 = Aggiornamento dato aggiuntivo fallito
     *      - MESSAGGIO: eventuale messaggio di errore
     */
    private function modificaDatiAggiuntivi($dagpak, $datiAggiuntivi) {        
        $datiAggiuntiviTab = $this->praLib->GetProdag($dagpak, 'dagpak', true);

        foreach ($datiAggiuntiviTab as &$datiAggiuntiviRec) {
            if (isset($datiAggiuntivi[$datiAggiuntiviRec['DAGKEY']])) {
                $datiAggiuntiviRec['DAGVAL'] = $datiAggiuntivi[$datiAggiuntiviRec['DAGKEY']];
            }
        }
        return $this->aggiornaDati($datiAggiuntiviTab);
    }

    private function inserisciAnaproPasso($keyPasso) {             
        $ritorno = array();
        $ritorno['Status'] = "0";
        $ritorno['Message'] = "Anapro inserito correttamente per il passo $keyPasso";
        $ritorno['RetValue'] = true;

        $praLibAssegnazionePassi = new praLibAssegnazionePassi();
        $DatiProtocollo = $praLibAssegnazionePassi->getDatiProtocollo($keyPasso);
        $rowidAnapro = $praLibAssegnazionePassi->insertAnapro($DatiProtocollo);
        if ($rowidAnapro === false) {
            $ritorno['Status'] = "-1";
            $ritorno['Message'] = "Errore inserimento record ANAPRO per il passo $keyPasso";
            $ritorno['RetValue'] = false;
            return $ritorno;
        }
        $anapro_rec = $this->proLib->GetAnapro($rowidAnapro, "rowid");
        $passo_rec = $this->praLib->GetPropas($keyPasso, "propak");
        $passo_rec['PASPRO'] = $anapro_rec['PRONUM'];
        $passo_rec['PASPAR'] = $anapro_rec['PROPAR'];
        $update_Info = "Oggetto: Aggiorno numero prot " . $passo_rec['PASPRO'] . " tipo " . $passo_rec['PASPAR'] . " su passo $keyPasso";
        if (!$this->praPasso->updateRecord($this->praLib->getPRAMDB(), 'PROPAS', $passo_rec, $update_Info)) {
            $ritorno['Status'] = "-1";
            $ritorno['Message'] = "Aggiornamento passo dopo inserimento su ANAPRO fallito per il passo $keyPasso";
            $ritorno['RetValue'] = false;
            return $ritorno;
        }
        $ritorno['anapro_rec'] = $anapro_rec;
        return $ritorno;
    }

    /**
     * Aggiorna i dati di passo o pratica (in maniera agnostica)
     * @param type $datiAggiuntiviTab
     * @return string|int
     */
    private function aggiornaDati($datiAggiuntiviTab) {        
        /*
         * Da Implememtare

          if (!$this->validaDati(false)) {
          return false;
          }

          $praLibCustomClass = new praLibCustomClass($this->praLib);
          if ($praLibCustomClass->checkEseguiAzione('PRE_SUBMIT_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
          $praLibCustomClass->eseguiAzione('PRE_SUBMIT_RACCOLTA', array(
          'PRAM_DB' => $this->PRAM_DB,
          'DatiAggiuntivi' => $datiAggiuntiviTab,
          'Passo' => $this->praLib->GetPropas($this->propak),
          'Dizionario' => $this->getDizionarioForm(true),
          'CallerForm' => $this
          ), true);
          }
         */
        foreach ($datiAggiuntiviTab as $prodag_rec) {
            /*
             * Record Spia per identificare il numenri raccolta non da elaborare
             */
            if ($prodag_rec['NUMERO_RACCOLTA']) {
                continue;
            }

            /*
             * Dati read-only non viene tenuto in considerazione
             */
            if ($prodag_rec['DAGROL']) {
                continue;
            }

            /*
             * Record spia per la gestione della form 
             * prevediamo di eseguire una pulizia in fase di validazione/normalizzazione
             * 
             */
            unset($prodag_rec['PROSEQ']);
            unset($prodag_rec['PRORDM']);

            /*
             * Aggiornamento PRODST
             */

            /*
             * Controllo se il dato aggiuntivo è di procedimento o di passo.
             */
            if ($prodag_rec['DAGPAK'] != $prodag_rec['DAGNUM']) {
                $prodst_rec = $this->praLib->GetProdst($prodag_rec['DAGSET']);

                if (!$prodst_rec) {
                    $prodst_rec = array();
                    $prodst_rec['DSTSET'] = $prodag_rec['DAGSET'];
                    $prodst_rec['DSTDES'] = 'Data Set ' . substr($prodag_rec['DAGSET'], -2);

                    $insertInfo = "Oggetto : Inserisco data set {$prodag_rec['DAGSET']} del file " . $prodag_rec['DAGKEY'];
                    if (!$this->praPasso->insertRecord($this->praLib->getPRAMDB(), 'PRODST', $prodst_rec, $insertInfo)) {
                        $toReturn = array(
                            'ESITO' => 1,
                            'MESSAGGIO' => "Inserimento dataset {$prodag_rec['DAGSET']} fallito."
                        );
                        return $toReturn;
                    }
                }
            }

            if ($prodag_rec['ROWID']) {
                $updateInfo = sprintf('Aggiornamento dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                if (!$this->praPasso->updateRecord($this->praLib->getPRAMDB(), 'PRODAG', $prodag_rec, $updateInfo)) {
                    $toReturn = array(
                        'ESITO' => 2,
                        'MESSAGGIO' => "Aggiornamento dato aggiuntivo fallito"
                    );
                    return $toReturn;
                }
            } else {
                $insertInfo = sprintf('Inserimento dato aggiuntivo %s fascicolo %s', $prodag_rec['DAGKEY'], $this->gesnum);
                if (!$this->praPasso->insertRecord($this->praLib->getPRAMDB(), 'PRODAG', $prodag_rec, $insertInfo)) {
                    $toReturn = array(
                        'ESITO' => 2,
                        'MESSAGGIO' => "Inserimento dato aggiuntivo fallito"
                    );
                    return $toReturn;
                }
            }
        }
        /*
         * 

          if ($praLibCustomClass->checkEseguiAzione('POST_SUBMIT_RACCOLTA', $this->getCodiceProcedimentoPasso(), $this->getCodicePasso())) {
          $praLibCustomClass->eseguiAzione('POST_SUBMIT_RACCOLTA', array(
          'PRAM_DB' => $this->PRAM_DB,
          'DatiAggiuntivi' => $datiAggiuntiviTab,
          'Passo' => $this->praLib->GetPropas($this->propak),
          'Dizionario' => $this->getDizionarioForm(true),
          'CallerForm' => $this
          ), true);
          }
         */
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => "Aggiornamento concluso con successo"
        );
        return $toReturn;
    }

    private function validaDatiSync($datiInnesco) {
        return true;
    }

    private function validaDatiInnesco($datiInnesco) {
        // Inizializza strutura dati di ritorno
        $toReturn = array(
            'ESITO' => 0,
            'MESSAGGIO' => ''
        );

        $msg = '';
        if (!isset($datiInnesco['GESPRO'])) {
            $msg .= 'Campo GESPRO non valorizzato';
        }
        if (!isset($datiInnesco['GESWFPRO'])) {
            if (strlen($msg) > 0) {
                $msg .= '<br>';
            }
            $msg .= 'Campo GESWFPRO non valorizzato';
        }
        if (!isset($datiInnesco['GESCODPROC'])) {
            if (strlen($msg) > 0) {
                $msg .= '<br>';
            }
            $msg .= 'Campo GESCODPROC non valorizzato';
        }
//        if (!isset($datiInnesco['GESRES'])) {
//            if (strlen($msg) > 0) {
//                $msg .= '<br>';
//            }
//            $msg .= 'Campo GESRES non valorizzato';
//        }
        if (!isset($datiInnesco['GESDRE'])) {
            if (strlen($msg) > 0) {
                $msg .= '<br>';
            }
            $msg .= 'Campo GESDRE non valorizzato';
        }
        if (!isset($datiInnesco['ITEEVT'])) {
            if (strlen($msg) > 0) {
                $msg .= '<br>';
            }
            $msg .= 'Campo ITEEVT non valorizzato';
        }

        if (strlen($msg) > 0) {
            $toReturn['ESITO'] = 1;
            $toReturn['MESSAGGIO'] = $msg;
        }

        return $toReturn;
    }

    private function getDatiAcquisizioneRichiesta($datiInnesco) {
        $Proges = array();
        $Proges['GESPRO'] = $datiInnesco['GESPRO'];
        $Proges['GESWFPRO'] = $datiInnesco['GESWFPRO'];
        $Proges['GESCODPROC'] = $datiInnesco['GESCODPROC'];
        $Proges['GESRES'] = $datiInnesco['GESRES'];
        $Proges['GESDRE'] = $datiInnesco['GESDRE'];
        $iteevt = $datiInnesco['ITEEVT'];

        $prodag_tab = $this->preparaDatiAggiuntiviFascicolo($Proges, $datiInnesco['DATI_AGGIUNTIVI']);
        if ($prodag_tab === false) {
            return false;
        }

        $arrDatiSrc = array(
            "ELENCOALLEGATI" => null,
            "DATA" => date("Ymd"),
            "ORA" => date('His'),
            "FILENAME" => null,
            "IDMAIL" => null,
            "PROGES" => $Proges,
            "ANADES" => null,
            "ITEEVT" => array("ROWID" => $iteevt),
            "PRODAG" => $prodag_tab,
            "EscludiPassiFO" => false,
            "RESULTREST" => null,
            "provenienza" => praLibRichiesta::PROVENIENZA_ANAGRAFICA,
            "idDocumento" => null,
            "segnatura" => null,
            "dataProtocollo" => null,
        );

        $praLibRichiesta = praLibRichiesta::getInstance();
        return $praLibRichiesta->getDatiRichiesta($arrDatiSrc);
    }

    private function preparaDatiAggiuntiviFascicolo($Proges, $datiAggiuntivi) {        
        $anapra_rec = $this->praLib->GetAnapra($Proges['GESPRO']);
        if (!$anapra_rec) {
            return false;
        }

        $itedag_tab = $this->praLib->GetItedag($Proges['GESWFPRO'], 'itekey', true);
        $arrIndexDag = array_column($itedag_tab, 'ITDKEY');
        $prodag_tab = array();
        foreach ($datiAggiuntivi as $key => $value) {
            $prodag_index = array_search($key, $arrIndexDag);
            if ($prodag_index !== false) {

                /*
                 *  Possibile controllo obbligatorieta del campo agiuntivo 
                 *
                  $itedag_rec = $itedag_tab[$prodag_index];
                 * 
                 */
                $prodag_rec = array(
                    'DAGKEY' => $key,
//                    'DAGDAT' => $value
                    'DAGVAL' => $value
                );
                $prodag_tab[] = $prodag_rec;
            }
        }
        return $prodag_tab;
    }

}
