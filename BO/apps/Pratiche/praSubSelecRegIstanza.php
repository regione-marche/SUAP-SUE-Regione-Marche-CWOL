<?php

/* * 
 *
 * GESTIONE REGISTRAZIONE ISTANZA SELEC
 *
 * PHP Version 5
 *
 * @category
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    03.04.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */


include_once ITA_BASE_PATH . '/apps/Pratiche/praCompPassoGest.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibSelec.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praTipiAllegato.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibSelec.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRicSelec.class.php';

function praSubSelecRegIstanza() {
    $praSubPassoUnico = new praSubSelecRegIstanza();
    $praSubPassoUnico->parseEvent();
    return;
}

class praSubSelecRegIstanza extends praCompPassoGest {
    // public $nameForm = 'praSubSelecRegIstanza';
    public $utiEnte;
    public $riepistru_rec;
    public $rowidAppoggio;
    private $arrayData;


    function __construct() {
        parent::__construct();
        $this->arrayData = App::$utente->getKey($this->nameForm . '_arrayData');
        
    }
    
    function postInstance() {
        parent::postInstance();
        
        try {
            $this->utiEnte = new utiEnte();
            $this->praLib = new praLib();
            $this->praLibSelec = new praLibSelec();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            
            $this->riepistru_rec = App::$utente->getKey($this->nameForm . "_riepistru_rec");
            $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
            
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        
        
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_riepistru_rec', $this->riepistru_rec);
            App::$utente->setKey($this->nameForm . '_arrayData', $this->arrayData);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_riepistru_rec');
        App::$utente->removeKey($this->nameForm . '_arrayData');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        
    }

    
    public function parseEvent() {
        parent::parseEvent();
        
        //Out::msgInfo("POST", print_r($_POST, true));
        
        switch ($_POST['event']) {
            case 'openform':

                //$this->currGesnum = '2018000016';
                //$this->keyPasso = '2018000016154281294861';
                //Out::msgInfo("POST", print_r($_POST, true));

                $this->inizializzaForm();

/*
                $this->inizializzaForm();


                if (is_array($_POST['listaAllegati'])) {
                    $this->caricaAllegatiEsterni($_POST['listaAllegati']);
                }
                if ($this->daMail["protocolla"]['FILENAME']) {
                    $this->caricaArrivoDaMail($this->daMail["protocolla"]);
                }

                if ($_POST['datiForm']) { // per integrazione
                    $this->datiForm = $_POST['datiForm'];
                }
                if ($_POST['datiInfo']) {
                    Out::show($this->nameForm . '_divInfo');
                    Out::html($this->nameForm . "_divInfo", $_POST['datiInfo']);
                } else {
                    Out::hide($this->nameForm . '_divInfo');
                }
                if ($_POST['passi']) {
                    $this->praPassi = $_POST['passi'];
                } else {
                    $proges_rec = $this->praLib->GetProges($this->currGesnum);
                    $this->praPassi = $this->praLib->caricaPassiBO($this->currGesnum);
                    if (!$this->praPerms->checkSuperUser($proges_rec)) {
                        $this->praPassi = $this->praPerms->filtraPassiView($this->praPassi);
                    }
                }
                $this->flagAssegnazioniPasso = $this->CheckAssegnazionePasso();
*/

                break;
            
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROGES[GESRES]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Responsabile", '', $this->nameForm . '_PROGES[GESRES]');
                        break;
                    case $this->nameForm . '_RIEPISTRU[CODIMPIEGATO]_butt':
                        praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Impiegato", '', $this->nameForm . '_RIEPISTRU[CODIMPIEGATO]');
                        break;
                    case $this->nameForm . '_CercaProvvedimento_butt':
//                        $praTipi = new praTipiAllegato();
//                        $Tipi = $praTipi->getTipi();
//                        $Tipi = $this->caricaArrayTree();
//                        $this->CaricaProcedimenti($Tipi, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig));
                        
                        $this->getArrayProvvedimenti();
                        $this->CaricaProcedimenti($this->arrayData, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Procedimenti");
                        
                        //praRic::praRicAnanom($this->PRAM_DB, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "Ricerca Impiegato", '', $this->nameForm . '_RIEPISTRU[CODIMPIEGATO]');
                        break;

                    case $this->nameForm . '_ConfermaCancellaLicIstrut':
                        // Out::msgInfo("ConfermaCancella", $this->rowidAppoggio);

                        //$prafodecode_rec = $_POST[$this->nameForm . '_PRAFODECODE'];

                        $delete_Info = 'Oggetto: Cancellazione record LICISTRUT con ID = ' . $this->rowidAppoggio;
                        if ($this->deleteRecord($this->praLibSelec->getSELECDB(), 'LICISTRUT', $this->rowidAppoggio, $delete_Info, "ID")) {
                            // Questo metodo esegui 'onClickTablePager' che fà il caricamento della tabella
                            // Non è corretto farlo direttamente da QUI.
                            TableView::reload($this->nameForm . '_gridAltriProv');
                            // Out::msgInfo("Cancellazione", "Cancellazione eseguita con successo");
                        }
                        break;
                    
                    case $this->nameForm . '_cancellaAttivitaDestinazione_butt':
                        if ($this->riepistru_rec['IDATTIVITA'] > 0 || $this->riepistru_rec['IDOPERATORI'] > 0){
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaAttDest', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        
                        $this->riepistru_rec['IDATTIVITA'] = 0;
                        $this->impostaDatiAttivita();
                        break;

                    case $this->nameForm . '_ConfermaCancellaAttDest':
                        // TODO SIMONE
                        // C'è da cancellare anche i record di SFOPERATORI e SFATTIVITA
                        // Vedi gestione.pannelli.PIstrutOperAttivita.java Metodo: svuotaDatiDestinazione  Riga: 1017
                        
                        $this->cancellaOperatoreDestinazione();
                        
                        
                        $this->riepistru_rec['IDATTIVITA'] = 0;
                        $this->riepistru_rec['IDOPERATORI'] = 0;
                        $this->impostaDatiAttivita();
                        break;

                    case $this->nameForm . '_cancellaAttivitaOrigine_butt':
                        
                        if ($this->riepistru_rec['IDATTIVORIG'] > 0){
                            Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaAttOrig', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                        }
                        break;

                    case $this->nameForm . '_ConfermaCancellaAttOrig':
                        $this->riepistru_rec['IDATTIVORIG'] = 0;
                        $this->riepistru_rec['IDOPERORIG'] = 0;
                        $this->impostaDatiAttivita('Origine');
                        break;

                    
                    case $this->nameForm . '_cercaAttivitaOrigine_butt':
                        //Out::msgInfo("Click", "cercaAttivitaOrigine_butt");
                        
                        $campi[0] =  array(
                            'label' => 'Denominazione<br>',
                            'id' => $this->nameForm . '_ricDitta',
                            'name' => $this->nameForm . '_ricDitta',
                            'type' => 'text',
                            'size' => '30',
                            'class' => "{autocomplete:{active:true,width:150}} ita-edit-uppercase",
                            'maxchars' => '50');

                        $campi[1] =  array(
                            'label' => 'Indirizzo<br>',
                            'id' => $this->nameForm . '_ricIndir',
                            'name' => $this->nameForm . '_ricIndir',
                            'type' => 'text',
                            'size' => '30',
                            'class' => "{autocomplete:{active:true,width:150}} ita-edit-uppercase",
                            'maxchars' => '50');
                        
                        
                        Out::msgInput(
                                'Ricerca Attività Origine', 
                                $campi, 
                                array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaRicercaOrigine', 'model' => $this->nameForm)
                                ), $this->nameForm . "_origine_div" // "praGest2_workSpace" 
                        );
                        
                        break;

                    case $this->nameForm . '_ConfermaRicercaOrigine':
                        $ragsocTrova = "";
                        $indirTrova = "";
                        if ($_POST[$this->nameForm . "_ricDitta"]) {
                            $ragsocTrova = $_POST[$this->nameForm . "_ricDitta"];
                            $ragsocTrova = str_replace("'", "\'", $ragsocTrova);
                        }
                        if ($_POST[$this->nameForm . "_ricIndir"]) {
                            $indirTrova = $_POST[$this->nameForm . "_ricIndir"];
                            $indirTrova = str_replace("'", "\'", $indirTrova);
                        }

                        //Out::msgInfo("ConfermaRicercaOrigine", print_r($_POST,true));
                        //Out::msgInfo("Parametri", $ragsocTrova . " - " . $indirTrova);

                        $where = " WHERE SFATTIVITA.SUB=0 AND SFATTIVITA.IDSUCC=0 AND SFATTIVITA.TIPOESER=1 " . 
                                " AND SFATTIVITA.IDCOMULTICOM = " . $this->praLibSelec->getIdComistat();
                        if ($ragsocTrova) {
                            $where = $where . " AND UPPER(SFOPERATORI.RAGSOC) LIKE UPPER('%" . $ragsocTrova . "%')";
                        }
                        if ($indirTrova){
                            $where = $where . " AND UPPER(VIE.DESCR) LIKE UPPER('%" . $indirTrova . "%')";
                        }
                        
                        praRicSelec::praRicSelecAttivita(array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), $where, "returnRicOrigine");
                        
                        //gfmRic::gfmRicAnaditta($this->nameForm, $ragsocTrova, "returnRicOrigine");
                        break;
 
                }
                
                break;

            case 'onChange':
                
                switch ($_POST['id']) {
                    case $this->nameForm . '_permanente_radio':
                        $this->sistemaRadio("Permanente");
                        break;
                    case $this->nameForm . '_stagionale_radio':
                        $this->sistemaRadio("Stagionale");
                        break;
                    case $this->nameForm . '_temporanea_radio':
                        $this->sistemaRadio("Temporanea");
                        break;
                    case $this->nameForm . '_tipoRichiesta':
                        //Out::msgInfo("onChange", print_r($_POST,true));
                        //Out::msgInfo("onChange", $_POST[$_POST['id']]);
                        $this->riepistru_rec['IDTIPORIC'] = $_POST[$_POST['id']];
                        break;

                }
                
                break;

            //Evento che si verifica quando rinfresca una griglia
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridAltriProv':
                        
                        $this->caricaGrigliaAltriProv();
                
                        break;
                    
                }
                
                break;

            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridAltriProv':
                        $this->getArrayProvvedimenti();
                        $this->CaricaProcedimenti($this->arrayData, array("nameForm" => $this->nameForm, "nameFormOrig" => $this->nameFormOrig), "LicIstrut");
                        
                        break;
                }
                break;

            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridAltriProv':
                        //Out::msgInfo("POST", print_r($_POST,true));
                        $this->rowidAppoggio = $_POST['rowid'];
                        
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancella', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaLicIstrut', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        
                        
                        break;
                }
                break;
            
            case 'onBlur':
                Out::msgInfo("onBlur", print_r($_POST,true));
                break;



            
            case 'returnUnires': 
                //Out::msgInfo("returnUnires", print_r($_POST, true));
                $this->DecodAnanom($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            
            case 'returnProcedimenti':
//                Out::msgInfo("returnProcedimenti", print_r($_POST, true));
//                Out::msgInfo("rowID", $_POST[rowData][ROWID]);
//                Out::msgInfo("rowID", $_POST[rowData][isLeaf]);
                
                if ($_POST[rowData][isLeaf]){
                    $idClassLic = $_POST[rowData][ROWID];
                    $sql = "SELECT * FROM TIPOLICCLAS WHERE IDCLASSLIC=$idClassLic ";
                    $tipoliclass_rec = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);

                    if ($tipoliclass_rec){
                        $this->riepistru_rec['IDTIPOLIC'] = $tipoliclass_rec['IDTIPOLIC'];
                        $this->sistemaProcedimentoRichiesta();
                    }
                    
                }
                else {
                    Out::msgInfo("ATTENZIONE !!!", "E' possiible riportare solo le foglie dell'albero");
                }
                
                break;

            case 'returnLicIstrut':
                //Out::msgInfo("returnLicIstrut", print_r($_POST, true));
                if ($_POST[rowData][isLeaf]){
                    $idClassLic = $_POST[rowData][ROWID];
                    $sql = "SELECT * FROM TIPOLICCLAS WHERE IDCLASSLIC=$idClassLic ";
                    $tipoliclass_rec = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);

                    if ($tipoliclass_rec){
                        // Inserimento record LICISTRUT
                        $licIstrut_rec = array();                        
                        $IdTab = $this->praLibSelec->getLastId('LICISTRUT');
                        
                        $licIstrut_rec['ID'] = $IdTab;
                        $licIstrut_rec['IDRIEPISTRU'] = $this->riepistru_rec['ID'];
                        $licIstrut_rec['IDTIPOLIC'] = $tipoliclass_rec['IDTIPOLIC'];
                        $licIstrut_rec['PRINCIPALE'] = 'N';
                        $licIstrut_rec['DURATA'] = $this->getDurata($tipoliclass_rec['IDTIPOLIC'], $this->riepistru_rec['IDTIPORIC']);
                        
                        $insert_info = 'Oggetto: ' . 'LICISTRUT' . ' IDRIEPISTRU' . $licIstrut_rec['IDRIEPISTRU'] . " IDTIPOLIC = " . $tipoliclass_rec['IDTIPOLIC'];

                        if (!$this->insertRecord($this->praLibSelec->getSELECDB(), 'LICISTRUT', $licIstrut_rec, $insert_info)) {
                            //$this->unlock($lock);
                            //break;
                        }
                       
                        
                        //$this->riepistru_rec['IDTIPOLIC'] = $tipoliclass_rec['IDTIPOLIC'];
                        //$this->sistemaProcedimentoRichiesta();
                    }
                    
                    // Questo metodo esegui 'onClickTablePager' che fà il caricamento della tabella
                    // Non è corretto farlo direttamente da QUI.
                    TableView::reload($this->nameForm . '_gridAltriProv');
                }
                else {
                    Out::msgInfo("ATTENZIONE !!!", "E' possiible riportare solo le foglie dell'albero");
                }

                
                
                break;
                
            case "returnRicOrigine":
               // Out::msgInfo("Ci sono", print_r($_POST, true));

                $idAttivita = $_POST['retKey'];
                if ($idAttivita > 0){
                    $this->riepistru_rec['IDATTIVORIG'] = $idAttivita;
                    $this->impostaDatiAttivita('Origine');
                    
                }
                
                break;
            
                
        }
    }

    public function aggiornaDati(){
        
        Out::msgInfo("aggiornaDati", "Entrato nel metodo");

        $update_Info = "Oggetto: Aggiornamento Riepistru con ID = " . $this->riepistru_rec['ID'] . " e numero " . $this->riepistru_rec['NUMISTRUT'];
        if (!$this->updateRecord($this->praLibSelec->getSELECDB(), 'RIEPISTRU', $this->riepistru_rec, $update_Info, "ID")) {
            Out::msgStop("ERRORE", "Aggiornamento record");
            return false;
        }


        
    }

    public function openGestione($rowid, $tipo = 'propak') {
        $this->AzzeraVariabili();
        $this->Nascondi();
        
        //$rowid --> PRAPAS.PROPAK
        // Record di PROPAS corrente
        $propas_rec = $this->praLib->GetPropas($rowid, $tipo);
        
        // Record di PROGES corrente
        $proges_rec = $this->praLib->GetProges($propas_rec['PRONUM']);
//        if (!$proges_rec) {
//            $this->OpenRicerca();
//            Out::msgStop("Attenzione", "Record PROGES con rowid: $Indice non più disponibile.");
//            return false;
//        }


        $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
        if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
            Out::valore($this->nameForm . "_DataProtocollo", $metaDati['Data']);
            Out::show($this->nameForm . '_DataProtocollo_field');
        } else {
            Out::hide($this->nameForm . '_DataProtocollo_field');
        }

        $Metadati = $this->praLib->GetMetadatiProges($proges_rec['GESNUM']);
        $this->switchIconeProtocollo($proges_rec['GESNPR'], $Metadati);

        Out::disableField($this->nameForm . '_procedimento_field');

        Out::valori($proges_rec, $this->nameForm . '_PROGES');

        $ananom_rec = $this->praLib->GetAnanom($proges_rec['GESRES']);
        if ($ananom_rec){
            Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
        }


        
        /*
         * Cercare RIEPISTRU attraverso il PROGES.GESNUM.
         * Se non trovato, creare il record e valorizzarlo dove possibile
         * Per fare il tutto, gestire il DataBase ita_selec01
         */
        
        // Record di PROGES corrente
        $this->riepistru_rec = $this->trovaRiepistru($proges_rec);
        
        Out::valori($this->riepistru_rec, $this->nameForm . '_RIEPISTRU');
        $ananom_rec = $this->praLib->GetAnanom($this->riepistru_rec['CODIMPIEGATO']);
        if ($ananom_rec){
            Out::valore($this->nameForm . '_desc_impiegato', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
        }

        
        $this->sistemaProcedimentoRichiesta();
        
        
        $datiAlDom_rec = $this->trovaDatiAlDom();

        Out::valori($datiAlDom_rec, $this->nameForm . '_DATIALDOM');

        
        switch ($datiAlDom_rec['STAG']){
            case "Permanente":
                Out::attributo($this->nameForm . "_permanente_radio", "checked", "0", "checked");        
                break;
            case "Stagionale":
                Out::attributo($this->nameForm . "_stagionale_radio", "checked", "0", "checked");        
                break;
            case "Temporanea":
                Out::attributo($this->nameForm . "_temporanea_radio", "checked", "0", "checked");        
                break;
            default:
                Out::attributo($this->nameForm . "_permanente_radio", "checked", "0", "checked");        
                
                
        }
        
        $this->sistemaRadio($datiAlDom_rec['STAG']);
        
        // Questo metodo esegui 'onClickTablePager' che fà il caricamento della tabella
        // Non è corretto farlo direttamente da QUI.
        TableView::reload($this->nameForm . '_gridAltriProv');
        // Messo in inizializaForm, così lo carica la prima volta
        //$this->caricaGrigliaAltriProv();
        
        $this->impostaDatiAttivita('Origine');
        $this->impostaDatiAttivita();

        
    }
    
    private function sistemaProcedimentoRichiesta(){
        Out::valore($this->nameForm . "_procedimento", $this->getProcedimento($this->riepistru_rec['IDTIPOLIC']));
        Out::show($this->nameForm . '_procedimento_field');

        $this->sistemaComboRichieste($this->riepistru_rec['IDTIPOLIC'], $this->riepistru_rec['IDTIPORIC']);
    }


    private function trovaRiepistru($proges_rec){
        
        // Record di RIEPISTRU corrente
        $riepistru_rec = $this->praLibSelec->getRiepistru($proges_rec['GESNUM']);
        if (!$riepistru_rec){
            
            $IdTab = $this->praLibSelec->getLastId('RIEPISTRU');

            $riepistru_rec['ID'] = $IdTab;
            $riepistru_rec['PROGESNUM'] = $proges_rec['GESNUM'];
            $riepistru_rec['NUMISTRUT'] = $this->getLastNumIstrut();
            $riepistru_rec['ORAPEC'] = $proges_rec['GESORA'];
            if ($proges_rec['GESNPR']) $riepistru_rec['NUMPROT_GEN'] = substr($proges_rec['GESNPR'], 4, 6) . "/" . substr($proges_rec['GESNPR'], 0, 4);

            if ($proges_rec['GESDRE']){
                $data = substr($proges_rec['GESDRE'],0,4) . '-' . substr($proges_rec['GESDRE'],4,2) . '-' . substr($proges_rec['GESDRE'],6,2);
                $riepistru_rec['DATAPER'] = $data;
            }
            
            if ($proges_rec['GESDRI']){
                $riepistru_rec['DATAPEC'] = substr($proges_rec['GESDRI'],0,4) . '-' . substr($proges_rec['GESDRI'],4,2) . '-' . substr($proges_rec['GESDRI'],6,2);
            }

            $riepistru_rec['IDCOMULTICOM'] = $this->praLibSelec->getIdComistat();
            
            $riepistru_rec['IDTIPOLIC'] = $this->getIdTipolic($proges_rec);
            $riepistru_rec['IDTIPORIC'] = $this->getIdTiporic($proges_rec);
            $riepistru_rec['DESCPROCSUAP'] = $proges_rec['GESOGG'];

            // TODO SIMONE: Vedere come valorizzare il campo RIEPISTRU.TIPOESER
            $riepistru_rec['TIPOESER'] = 1;
            //DATAPROT_GEN; 

            
            //Out::msgInfo("ID", print_r($riepistru_rec, true));
            
            
            $insert_info = 'Oggetto: ' . 'RIEPISTRU' . ' Codice' . $proges_rec['GESNUM'];


            if (!$this->insertRecord($this->praLibSelec->getSELECDB(), 'RIEPISTRU', $riepistru_rec, $insert_info)) {
                //$this->unlock($lock);
                //break;
            }
            
            
        }
        
        return $riepistru_rec;
    }

    private function trovaDatiAlDom(){
        
        // Record di DATIALDOM corrente
        $condizione = " WHERE DATIALDOM.IDRIEPISTRU = " . $this->riepistru_rec['ID'] ;
        //$datiAlDom_rec = $this->praLibSelec->getRiepistru($this->riepistru_rec['ID']);
        $datiAlDom_rec = $this->praLibSelec->getRecordSelec("DATIALDOM", $condizione, false);
        if (!$datiAlDom_rec){
            
            $IdTab = $this->praLibSelec->getLastId('DATIALDOM');

            $datiAlDom_rec['ID'] = $IdTab;
            $datiAlDom_rec['IDRIEPISTRU'] = $this->riepistru_rec['ID'];
            $datiAlDom_rec['STAG'] = "Permanente";
            //$datiAlDom_rec['IDCOMULTICOM'] = $this->praLibSelec->getIdComistat();
            
            $insert_info = 'Oggetto: ' . 'DATIALDOM' . ' Id Riepistru' . $this->riepistru_rec['ID'];


            if (!$this->insertRecord($this->praLibSelec->getSELECDB(), 'DATIALDOM', $datiAlDom_rec, $insert_info)) {
                //$this->unlock($lock);
                //break;
            }
            
            
        }
        
        return $datiAlDom_rec;
    }
    
    
    
    private function getLastNumIstrut() {
        $ultimo = 1;
        $sql = "SELECT * FROM RIEPISTRU ORDER BY NUMISTRUT DESC";
        $record = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);
        if ($record){
            $ultimo = $record['NUMISTRUT'] + 1;
        }
        
        return $ultimo;
    }

    private function getIdTipolic($proges_rec){
        $idTipoLic = 0;

        $condizione = " WHERE WEBTIPOLICEST.CODICE = " . $proges_rec['GESATT'];
        $webtipolicest_rec = $this->praLibSelec->getRecordSelec('WEBTIPOLICEST', $condizione, false);
        if ($webtipolicest_rec){

            $condizione = " WHERE WEBTIPOLICEST_TIPOLIC.IDWEBTIPOLICEST = " . $webtipolicest_rec['ID'];
            $webtipolicest_tipolic_rec = $this->praLibSelec->getRecordSelec('WEBTIPOLICEST_TIPOLIC', $condizione, false);
            
            $idTipoLic = $webtipolicest_tipolic_rec['IDTIPOLIC'];
            
        }
        
        
        return $idTipoLic;
    }
    
    private function getIdTiporic($proges_rec){
        $idTipoRic = 0;

        $condizione = " WHERE WEBTIPORICEST.CODICECAR = '" . $proges_rec['GESEVE'] . "'";
        $webtiporicest_rec = $this->praLibSelec->getRecordSelec('WEBTIPORICEST', $condizione, false);
        if ($webtiporicest_rec){

            $idTipoRic = $webtiporicest_rec['IDTIPORIC'];
            
        }
        
        
        return $idTipoRic;
    }
    
    private function getProcedimento($idTipoLic){
        $procedimento = "";
        $condizione = " WHERE TIPOLIC.ID = " . $idTipoLic ;
        $tipoLic_rec = $this->praLibSelec->getRecordSelec('TIPOLIC', $condizione, false);

        if ($tipoLic_rec){
            $procedimento = $tipoLic_rec['DESCLIC'];
        }
        
        return $procedimento;
    }

    private function sistemaComboRichieste($idTipoLic, $idTipoRic){
        Out::html($this->nameForm . "_tipoRichiesta", "");
        
        if ($idTipoLic > 0){
            
            $sql = " SELECT TIPORIC.* FROM TIPORIC "
                    . "LEFT JOIN TIPOLICVAR ON TIPOLICVAR.IDTIPORIC = TIPORIC.ID "
                    . "WHERE TIPOLICVAR.IDTIPOLIC = " . $idTipoLic;
            
            $tipoRic_tab = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, true);
            
            foreach ($tipoRic_tab as $richiesta) {
                
                if ($richiesta['ID'] == $idTipoRic){
                    Out::select($this->nameForm . '_tipoRichiesta', '1', $richiesta['ID'], '1', $richiesta['DESCVARIAZIONE']);
                }
                else {
                    Out::select($this->nameForm . '_tipoRichiesta', '1', $richiesta['ID'], '0', $richiesta['DESCVARIAZIONE']);
                }
                
                
                //Out::select($this->nameForm . '_NomeSelect', 1, $Tabella_rec['CODICEUNIVOCO'], "0", $Tabella_rec['DESCRIZIONE']);
                
            }
            
        }
        
        
    }
    

    function AzzeraVariabili() {
        $this->rowidAppoggio = null;
        $this->riepistru_rec = array();
        $this->ricDitta = "";

    }
    
    public function Nascondi() {
        Out::hide($this->nameForm . '_Protocolla');
        Out::hide($this->nameForm . '_RimuoviProtocolla');
        Out::hide($this->nameForm . '_InviaProtocollo');
        Out::hide($this->nameForm . '_VediProtocollo');
        Out::hide($this->nameForm . '_GestioneProtocollo');

        Out::hide($this->nameForm . '_CodAmmAoo');
        Out::hide($this->nameForm . '_CodAmmAoo_lbl');
        
        Out::disableField($this->nameForm . '_ragioneSocialeOrigine');
        Out::disableField($this->nameForm . '_indirizzoAttivitaOrigine');
        Out::disableField($this->nameForm . '_catastoOrigine');

        Out::disableField($this->nameForm . '_ragioneSocialeDestinazione');
        Out::disableField($this->nameForm . '_indirizzoAttivitaDestinazione');
        Out::disableField($this->nameForm . '_catastoDestinazione');

        Out::disableField($this->nameForm . '_attivitaPrevRT');
        
        $this->nascondiCampiStagTemp();
        
        
//        Out::hide($this->nameForm . '_divAlertRicevute');
        
    }
    
    private function nascondiCampiStagTemp(){
        /**
         * Nascondere le caselle della Stagionalità 
         */
        Out::hide($this->nameForm . '_DATIALDOM[GGINIZIOSTAG]');
        Out::hide($this->nameForm . '_DATIALDOM[GGINIZIOSTAG]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[MMINIZIOSTAG]');
        Out::hide($this->nameForm . '_DATIALDOM[MMINIZIOSTAG]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[GGFINESTAG]');
        Out::hide($this->nameForm . '_DATIALDOM[GGFINESTAG]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[MMFINESTAG]');
        Out::hide($this->nameForm . '_DATIALDOM[MMFINESTAG]_lbl');
        
        Out::hide($this->nameForm . '_DATIALDOM[GGINIZIOSTAG1]');
        Out::hide($this->nameForm . '_DATIALDOM[GGINIZIOSTAG1]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[MMINIZIOSTAG1]');
        Out::hide($this->nameForm . '_DATIALDOM[MMINIZIOSTAG1]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[GGFINESTAG1]');
        Out::hide($this->nameForm . '_DATIALDOM[GGFINESTAG1]_lbl');

        Out::hide($this->nameForm . '_DATIALDOM[MMFINESTAG1]');
        Out::hide($this->nameForm . '_DATIALDOM[MMFINESTAG1]_lbl');
        
        
        
        // Nasconde le caselle della Temporanea 
        Out::hide($this->nameForm . '_DATIALDOM[DATAINIZIO]');
        Out::hide($this->nameForm . '_DATIALDOM[DATAINIZIO]_lbl');
        Out::hide($this->nameForm . '_DATIALDOM[DATAINIZIO]_datepickertrigger');
        
        Out::hide($this->nameForm . '_DATIALDOM[DATAFINE]');
        Out::hide($this->nameForm . '_DATIALDOM[DATAFINE]_lbl');
        Out::hide($this->nameForm . '_DATIALDOM[DATAFINE]_datepickertrigger');
        
    }
    
    function inizializzaForm() {
        Out::html($this->nameForm . "_PROGES[GESPAR]", "");
        $this->CreaCombo();
        $this->caricaGrigliaAltriProv();

        
    }
    
    public function CreaCombo() {
        Out::html($this->nameForm . "_PROGES[GESPAR]", "");

        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "", "1", "");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "A", "0", "Arrivo     ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "P", "0", "Partenza   ");
        Out::select($this->nameForm . '_PROGES[GESPAR]', 1, "C", "0", "Interno   ");

    }


    private function switchIconeProtocollo($numeroProt, $Metadati) {


        $PARMENTE_rec = $this->utiEnte->GetParametriEnte();
        $tipoProt = $PARMENTE_rec['TIPOPROTOCOLLO'];
        $numeroProt = (String) $numeroProt;
        if ($tipoProt != 'Manuale' && $tipoProt) {
            if ($numeroProt != '' && $numeroProt != '0' && $Metadati) {
                Out::show($this->nameForm . "_GestioneProtocollo");
            }

//
//protocollo vuoto -> tutti i campi editabili            
//            
            if ($numeroProt == '' || $numeroProt == '0') {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '1');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '1');
                Out::show($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
            } elseif (($numeroProt != '' && $numeroProt != '0' && $Metadati) || $tipoProt == "Italsoft") { //c'è il protocollo e ci sono sono i metadati -> sparisce l'icona, i campi non sono editabili
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::hide($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::show($this->nameForm . '_ProtocollaPartenza');
            } else { //c'è il protocollo, ma non ci sono i metadati -> sparisce icona +, campi non editabili, compare un cestino per cancellarli
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_Protocolla');
            }
        } else {
            if ($numeroProt == '' || $numeroProt == '0') { //protocollo vuoto -> tutti i campi editabili
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '1');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '1');
                Out::hide($this->nameForm . '_Protocolla'); //per l'inserimento manuale l'icona + è sempre disabilitata
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::show($this->nameForm . '_InviaProtocollo');
            } else {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::show($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_Protocolla');
                if (isset($Metadati['DatiProtocollazione']['IdMailRichiesta'])) {
                    Out::show($this->nameForm . '_InviaProtocollo');
                }
            }
        }

        /*
         * Controllo Finale se utente abilitato al protocollo
         */

        /*
         * Verifico se è stato attivato l'utilizzo dei profili valore parametro=1
         */
        $filent_rec = $this->praLib->GetFilent(26);
        if ($filent_rec["FILDE1"] == 1) {
            $profilo = proSoggetto::getProfileFromIdUtente();

            /*
             * Utente disabilitato da profilo (solo partenza o nega)
             */
            if ($profilo['PROT_ABILITATI'] == '2' || $profilo['PROT_ABILITATI'] == '3') {
                Out::attributo($this->nameForm . '_Numero_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_Anno_prot', "readonly", '0');
                Out::attributo($this->nameForm . '_PROGES[GESPAR]', "readonly", '0');
                Out::hide($this->nameForm . '_Protocolla');
                Out::hide($this->nameForm . '_RimuoviProtocolla');
                Out::hide($this->nameForm . '_ProtocollaPartenza');
                Out::hide($this->nameForm . '_InviaProtocollo');
                //Out::hide($this->nameForm . "_BloccaAllegatiPratica");
            }
        }
    }

    function DecodAnanom($Codice, $retid, $tipoRic = 'codice') {
        $ananom_rec = $this->praLib->GetAnanom($Codice, $tipoRic);
        switch ($retid) {
            case $this->nameForm . "_RIEPISTRU[CODIMPIEGATO]":
                Out::valore($this->nameForm . '_RIEPISTRU[CODIMPIEGATO]', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_desc_impiegato', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                break;
            case $this->nameForm . "_PROGES[GESRES]":
                Out::valore($this->nameForm . '_PROGES[GESRES]', $ananom_rec['NOMRES']);
                Out::valore($this->nameForm . '_Desc_resp2', $ananom_rec['NOMCOG'] . " " . $ananom_rec['NOMNOM']);
                $this->DecodResponsabile($ananom_rec);
                break;
            case "" :
                break;
        }
        return $ananom_rec;
    }

    function DecodResponsabile($Ananom_rec) {
        $sql = "SELECT ANAUNI.ROWID AS ROWID, ANAUNI.UNIADD AS UNIADD, ANAUNI.UNIOPE AS UNIOPE, ANAUNI.UNISET AS UNISET,
            ANAUNI.UNISER AS UNISER,SETTORI.UNIDES AS DESSET, SERVIZI.UNIDES AS DESSER,UNITA.UNIDES AS DESOPE,
            NOMCOG & ' ' & NOMNOM AS NOMCOG
            FROM ANAUNI ANAUNI LEFT OUTER JOIN ANANOM ANANOM ON ANAUNI.UNIADD=ANANOM.NOMRES
            LEFT OUTER JOIN ANAUNI SETTORI ON ANAUNI.UNISET=SETTORI.UNISET AND SETTORI.UNISER=''
            LEFT OUTER JOIN ANAUNI SERVIZI ON ANAUNI.UNISET=SERVIZI.UNISET AND ANAUNI.UNISER=SERVIZI.UNISER AND SERVIZI.UNIOPE=''
            LEFT OUTER JOIN ANAUNI UNITA   ON ANAUNI.UNISET=UNITA.UNISET AND ANAUNI.UNISER=UNITA.UNISER AND ANAUNI.UNIOPE=UNITA.UNIOPE
            AND UNITA.UNIADD=''
            WHERE ANAUNI.UNISET<>'' AND ANAUNI.UNISER<>'' AND ANAUNI.UNIOPE<>'' AND ANAUNI.UNIADD<>'' AND ANAUNI.UNIAPE=''";
        $sql .= " AND ANAUNI.UNIADD = '" . $Ananom_rec["NOMRES"] . "'";
        $AnauniRes_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        //$Anauni_rec = $this->praLib->getAnauni($AnauniRes_rec['UNISET']);
        //Out::valore($this->nameForm . '_PROGES[GESSET]', $Anauni_rec['UNISET']);
        //Out::valore($this->nameForm . '_Settore', $Anauni_rec['UNIDES']);
        if ($AnauniRes_rec['UNISER'] == "")
            $AnauniRes_rec['UNISET'] = "";
        //$AnauniServ_rec = $this->praLib->GetAnauniServ($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER']);
        //Out::valore($this->nameForm . '_PROGES[GESSER]', $AnauniServ_rec['UNISER']);
        //Out::valore($this->nameForm . '_Servizio', $AnauniServ_rec['UNIDES']);
        if ($AnauniRes_rec['UNISET'] == "")
            $AnauniRes_rec['UNIOPE'] = "";
        $AnauniOpe_rec = $this->praLib->GetAnauniOpe($AnauniRes_rec['UNISET'], $AnauniRes_rec['UNISER'], $AnauniRes_rec['UNIOPE']);
        Out::valore($this->nameForm . '_PROGES[GESOPE]', $AnauniOpe_rec['UNIOPE']);
        Out::valore($this->nameForm . '_Unita', $AnauniOpe_rec['UNIDES']);
    }

    private function sistemaRadio($tipoStag){
        
        $this->nascondiCampiStagTemp();

        switch ($tipoStag) {
            case 'Permanente':

                break;
            case 'Stagionale':
                /**
                 * Visualizza le caselle della Stagionalità 
                 */
                Out::show($this->nameForm . '_DATIALDOM[GGINIZIOSTAG]');
                Out::show($this->nameForm . '_DATIALDOM[GGINIZIOSTAG]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[MMINIZIOSTAG]');
                Out::show($this->nameForm . '_DATIALDOM[MMINIZIOSTAG]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[GGFINESTAG]');
                Out::show($this->nameForm . '_DATIALDOM[GGFINESTAG]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[MMFINESTAG]');
                Out::show($this->nameForm . '_DATIALDOM[MMFINESTAG]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[GGINIZIOSTAG1]');
                Out::show($this->nameForm . '_DATIALDOM[GGINIZIOSTAG1]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[MMINIZIOSTAG1]');
                Out::show($this->nameForm . '_DATIALDOM[MMINIZIOSTAG1]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[GGFINESTAG1]');
                Out::show($this->nameForm . '_DATIALDOM[GGFINESTAG1]_lbl');

                Out::show($this->nameForm . '_DATIALDOM[MMFINESTAG1]');
                Out::show($this->nameForm . '_DATIALDOM[MMFINESTAG1]_lbl');

                break;
            case 'Temporanea':
                // Visualizza le caselle della Temporanea 
                Out::show($this->nameForm . '_DATIALDOM[DATAINIZIO]');
                Out::show($this->nameForm . '_DATIALDOM[DATAINIZIO]_lbl');
                Out::show($this->nameForm . '_DATIALDOM[DATAINIZIO]_datepickertrigger');

                Out::show($this->nameForm . '_DATIALDOM[DATAFINE]');
                Out::show($this->nameForm . '_DATIALDOM[DATAFINE]_lbl');
                Out::show($this->nameForm . '_DATIALDOM[DATAFINE]_datepickertrigger');

                break;

        }
        

        
    }

    function CreaSqlAltriProv() {
        
        $sql = "SELECT 
                LICISTRUT.ID AS ROW_ID,
                TIPOLIC.ID AS IDTIPOLIC,
                TIPOLIC.DESCLIC AS DESCLIC,
                LICISTRUT.DURATA AS DURATA
             FROM LICISTRUT 
                LEFT OUTER JOIN TIPOLIC ON TIPOLIC.ID=LICISTRUT.IDTIPOLIC

             WHERE 1 ";


        if ($this->riepistru_rec['ID']){
            $sql .= " AND LICISTRUT.IDRIEPISTRU = " . $this->riepistru_rec['ID'];
        }


        //Out::msgInfo("Query SQL", $sql);

        return $sql;
    }

    private function caricaGrigliaAltriProv(){
        //TableView::clearGrid($this->gridAltriProv);

        if ($this->riepistru_rec['ID']){
            $sql = $this->CreaSqlAltriProv();
            $ita_grid01 = new TableView($this->nameForm . '_gridAltriProv', array('sqlDB' => $this->praLibSelec->getSELECDB(), 'sqlQuery' => $sql));

            //Out::msgInfo("Griglia", print_r($_POST, true));

            //$ita_grid01 = new TableView($_POST['id'], array('sqlDB' => $this->praLibSelec->getSELECDB(), 'sqlQuery' => $sql));
    //        $ita_grid01->setPageNum($_POST['page']);
    //        $ita_grid01->setPageRows($_POST['rows']);
    //        $ita_grid01->setSortIndex($_POST['sidx']);
    //        $ita_grid01->setSortOrder($_POST['sord']);
            $Result_tab = $ita_grid01->getDataArray();
            if (!$ita_grid01->getDataPageFromArray('json', $Result_tab) && $_POST['_search'] !== 'true') {
                //Out::msgStop("Selezione", "Nessun record trovato.");
                //Ritorna alla videata della ricerca
                //$this->openRicerca();

            }


            //TableView::clearGrid($this->nameForm . '_gridAltriProv');
            TableView::enableEvents($this->nameForm . '_gridAltriProv');
            
        }
        
        
    }

/*    
    private function caricaArrayTree(){
        
        $arrayData[0] = array(
            'level' => 0,
            'parent' => NULL,
            'isLeaf' => 'false',
            'loaded' => 'true',
            'expanded' => 'true',
            'INDICE' => 0,
            'UFFCOD' => 10,
            'UFFDES' => '<div>' . 'Primo' . '</div>',
            'ROWID' => 1,
            'CODICE_PADRE' => 0
        );

        $arrayData[1] = array(
            'level' => 1,
            'parent' => 0,
            'isLeaf' => 'false',
            'loaded' => 'true',
            'expanded' => 'true',
            'INDICE' => 1,
            'UFFCOD' => 11,
            'UFFDES' => '<div>' . 'Secondo' . '</div>',
            'ROWID' => 2,
            'CODICE_PADRE' => 0
        );

        $arrayData[2] = array(
            'level' => 2,
            'parent' => 1,
            'isLeaf' => 'true',
            'loaded' => 'true',
            'expanded' => 'true',
            'INDICE' => 2,
            'UFFCOD' => 12,
            'UFFDES' => '<div>' . 'Terzo' . '</div>',
            'ROWID' => 3,
            'CODICE_PADRE' => 0
        );
        
        
        $arrayData[3] = array(
            'level' => 0,
            'parent' => NULL,
            'isLeaf' => 'true',
            'loaded' => 'true',
            'expanded' => 'true',
            'INDICE' => 3,
            'UFFCOD' => 20,
            'UFFDES' => '<div>' . 'Venti' . '</div>',
            'ROWID' => 4,
            'CODICE_PADRE' => 0
        );
        
        
        
        return $arrayData;
        
    }
*/

    
    private function CaricaProcedimenti($arrayTipi, $returnModel, $returnTipo = 'Procedimenti') {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Albero Procedimenti',
            "width" => '450',
            "height" => '430',
            "sortname" => "valore",
            "sortorder" => "desc",
            "rowNum" => '20000',
            "rowList" => '[]',
            "arrayTable" => $arrayTipi,
            "treeGrid" => 'true', 
            "treeGridModel" => 'adjacency', 
            "ExpandColumn" => 'valore', 
            "colNames" => array(
                "DESCRIZIONE"
            ),
            "colModel" => array(
                array("name" => 'UFFDES', "width" => 400)
                //array("name" => 'valore', "width" => 400)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = "return" . $returnTipo;
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $contenuto;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }


    private function getArrayProvvedimenti() {
        $this->arrayData = array();
        $sql = "SELECT * FROM ALBERI WHERE CORRENTE='S' ";
        $albero_rec = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);
        if ($albero_rec){
            /*
             * 
             * Costruzione Albero Provvedimenti
             */
            $i = 0;
            $this->caricaFigli(0, $i, 0, $albero_rec['ID']);
        }
    }

    private function caricaFigli($idPrec, &$i, $level_padre, $idAlbero) {
//        $sqlUffici = "SELECT * FROM ANAUFF WHERE IDPREC='$idAlbero' AND UFFANN=0 AND TIPOUFFICIO<>'R' ORDER BY UFFCOD";
//        $Uffici_figli_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sqlUffici, true);

        $sqlClassLic = "SELECT * FROM CLASSLIC WHERE IDALBERI=$idAlbero AND IDPREC=$idPrec ORDER BY ORDINE";
        $classlic_tab = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sqlClassLic, true);

        $id_parent = $i;
        $expanded = 'true';
        if ($level_padre > 0) {
            $expanded = 'false';
        }
        foreach ($classlic_tab as $classlic_rec) {
            $i++;
            $curr_idx = $i;

            $descrRuolo = $classlic_rec['DESCRIZIONE'];
            //$descrRuolo = '<span style="width:18px; display:inline-block;" class="ita-tooltip ui-icon ui-icon-group" title="Ruolo"></span>';
            //$descrRuolo .= $classlic_rec['UFFDES'] . ' <span style="color:darkred;font-weight:bold;"> [' . $classlic_rec['LIVELLOVIS'] . ']</span>';

            $this->arrayData[$i] = array(
                'level' => $level_padre + 1,
                'parent' => $id_parent,
                'isLeaf' => 'true',
                'loaded' => 'true',
                'expanded' => $expanded,
                'INDICE' => $i,
                'UFFCOD' => $classlic_rec['ID'],
                'UFFDES' => '<div>' . $descrRuolo . '</div>',
                'ROWID' => $classlic_rec['ID'],
                'CODICE_PADRE' => $classlic_rec['IDPADRE']
            );
            
            $this->caricaFigli($classlic_rec['ID'], $i, $level_padre + 1, $idAlbero);
            if ($i > $curr_idx) {
                $this->arrayData[$curr_idx]['isLeaf'] = false;
            }
        }

    }
    
    private function getDurata($idTipoLic, $idTipoRic){
        $durata = 0;
        
        $sql = "SELECT * FROM TIPOLICVAR WHERE IDTIPOLIC=" . $idTipoLic . " AND IDTIPORIC = " . $idTipoRic;
        $tipolicvar_rec = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);
        if ($tipolicvar_rec){
            $durata = $tipolicvar_rec['GIORNIITER'];
        }
        
        return $durata;
    }

    private function impostaDatiAttivita($tipo = 'Destinazione'){
        
        if ($tipo == 'Destinazione'){
            $idAttivita = $this->riepistru_rec['IDATTIVITA'];
            Out::html($this->nameForm . "_ragioneSocialeDestinazione", "");
            Out::html($this->nameForm . "_indirizzoAttivitaDestinazione", "");
            Out::html($this->nameForm . "_catastoDestinazione", "");
        }
        else {
            $idAttivita = $this->riepistru_rec['IDATTIVORIG'];
            Out::html($this->nameForm . "_ragioneSocialeOrigine", "");
            Out::html($this->nameForm . "_indirizzoAttivitaOrigine", "");
            Out::html($this->nameForm . "_catastoOrigine", "");
        }
        
        if ($idAttivita){

            $sql = "SELECT SFATTIVITA.ID, SFATTIVITA.NUMCIV1, SFATTIVITA.NUMCIV2, SFATTIVITA.KM, " .
                " SFATTIVITA.IDOPERATORI, SFOPERATORI.RAGSOC, VIE.INDIRIZZO " .
                " FROM (SFATTIVITA LEFT JOIN SFOPERATORI ON SFATTIVITA.IDOPERATORI = SFOPERATORI.ID) " .
                " LEFT JOIN VIE ON SFATTIVITA.IDVIE = VIE.ID " .
                " WHERE SFATTIVITA.ID=" . $idAttivita . 
                " AND SFATTIVITA.IDCOMULTICOM = " . $this->praLibSelec->getIdComistat();
            $attivita_rec = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, false);
            if ($attivita_rec){

                $indirizzo = $attivita_rec['INDIRIZZO'];
                if ($attivita_rec['NUMCIV1'] > 0) {
                    if ($attivita_rec['KM'] == 'S') $indirizzo = $indirizzo . " km. " . $attivita_rec['NUMCIV1'];
                    else {
                        $indirizzo = $indirizzo . " n. " . $attivita_rec['NUMCIV1'];
                        if ($attivita_rec['NUMCIV2']) $indirizzo = $indirizzo . " / " . $attivita_rec['NUMCIV2'];
                    }
                }


                if ($tipo=='Destinazione'){
                    Out::html($this->nameForm . "_ragioneSocialeDestinazione", $attivita_rec['RAGSOC']);
                    Out::html($this->nameForm . "_indirizzoAttivitaDestinazione", $indirizzo);

                }
                else {
                    Out::html($this->nameForm . "_ragioneSocialeOrigine", $attivita_rec['RAGSOC']);
                    Out::html($this->nameForm . "_indirizzoAttivitaOrigine", $indirizzo);

                }

                $sqlCat = "SELECT UIMM.* " .
                  "FROM (UIMM LEFT JOIN SFATTUIMM ON SFATTUIMM.IDUIMM = UIMM.ID) " .
                  "WHERE SFATTUIMM.IDATTIVITA = " . $idAttivita;
                $catasto_tab = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sqlCat, true);
                if ($catasto_tab){
                    $primo = true;
                    $catasto = "";
                    foreach ($catasto_tab as $catasto_rec) {
                        if (!primo) $catasto = $catasto . " - ";

                        $catasto = $catasto . "Sez.: " . $catasto_rec['SEZIONE'] .
                           "  Foglio: " . $catasto_rec['FOGLIO'] .
                           "  Part.: " . $catasto_rec['NUMPART'] . 
                           "  Sub.: " . $catasto_rec['SUBALTERNO'] ;

                        if ($catasto_rec['CORTILE'] > 0) $catasto = $catasto . "  Cortile: " . $catasto_rec['CORTILE'];
                        if ($catasto_rec['SCALA'] > 0) $catasto = $catasto . "  Scala: " . $catasto_rec['SCALA'];
                        if ($catasto_rec['PIANO'] > 0) $catasto = $catasto . "  Piano: " . $catasto_rec['PIANO'];
                        if ($catasto_rec['INTERNO'] > 0) $catasto = $catasto . "  Interno: " . $catasto_rec['INTERNO'];

                        $primo = false;

                    }

                    if ($tipo=='Destinazione'){
                        Out::html($this->nameForm . "_catastoDestinazione", $catasto);
                    }
                    else {
                        Out::html($this->nameForm . "_catastoOrigine", $catasto);
                    }

                }

            }

            
        }
        
        
    }

    private function cancellaOperatoreDestinazione(){

        if ($this->riepistru_rec['IDOPERATORI'] > 0){
            if ($this->riepistru_rec['IDOPERATORI'] |= $this->riepistru_rec['IDOPERORIG']){
                $sql = "SELECT * FROM SFATTIVITA WHERE IDOPERATORI=$this->riepistru_rec['IDOPERATORI']";
                $attivita_tab = ItaDB::DBSQLSelect($this->praLibSelec->getSELECDB(), $sql, true);
                if ($attivita_tab){
                    if (count($attivita_tab) < 2) {
                        
                        Out::msgInfo("RaSoc", $this->nameForm . "_ragioneSocialeOrigine");
                        
                        return $this->praLibSelec->cancellaOperatore($this->riepistru_rec['IDOPERATORI']);                        
                        
                    }
                    
                    
                }
                else return $this->praLibSelec->cancellaOperatore($this->riepistru_rec['IDOPERATORI']);

                
            }
        }

        return true;

/*
    if (idOperDest > 0){
      if (idOperDest != idOperOrig) {
        qdsAttivita = selec.gui.SCalcolo.getQuery("SELECT * FROM SFATTIVITA WHERE SFATTIVITA.IDOPERATORI = " + idOperDest,dbSource);
        if (qdsAttivita.getRowCount() < 2) {

          if (jTextRagSoc.getText().length() < 1){
            selec.sinfecon.gui.utility.SCancellaDati.cancOperatori(idOperDest,dbSource);
          }
          else {
            String msg=" L'Operatore " + jTextRagSoc.getText() + " \n " +
                " non ha associato altre attività. \n \n" +
                " Si desidera cancellare anche l'Operatore ? \n \n";

            if(JOptionPane.showConfirmDialog(null,msg,
                            "Conferma Cancellazione Operatore",
                            JOptionPane.YES_NO_OPTION,
                            JOptionPane.WARNING_MESSAGE)==JOptionPane.YES_OPTION){

               selec.sinfecon.gui.utility.SCancellaDati.cancOperatori(idOperDest,dbSource);
            }

          }

        }
      }
    }
 
 
 
 */
        
        
    }
    
}




