<?php

/**
 *
 * TEST Gestione Pratica con WorkFlow
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    30.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPSelec/itaStarServiceClient.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibDatiWorkFlow.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibElaborazioneDati.class.php';

include_once ITA_BASE_PATH . './apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . './apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');

function praWorkflowTest() {
    $praWorkflowTest = new praWorkflowTest();
    $praWorkflowTest->parseEvent();
    return;
}

class praWorkflowTest extends itaModel {

    public $nameForm = "praWorkflowTest";
    public $invioComunicazione_filePath;
    static private $tipoFo = "STARWS";
    public $praCompPassoGestFormname;
    /* @var $datiWf praWorkflowTest */
    public $datiWf;

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_invioComunicazione_filePath', $this->invioComunicazione_filePath);
            App::$utente->setKey($this->nameForm . '_praCompPassoGestFormname', $this->praCompPassoGestFormname);
            App::$utente->setKey($this->nameForm . '_datiWf', serialize($this->datiWf));
        }
    }

    function postInstance() {
        parent::postInstance();
        $this->invioComunicazione_filePath = App::$utente->getKey($this->nameForm . '_invioComunicazione_filePath');
        $this->praCompPassoGestFormname = App::$utente->getKey($this->nameForm . '_praCompPassoGestFormname');
        $this->datiWf = unserialize(App::$utente->getKey($this->nameForm . '_datiWf'));
    }

    private function disegnaDomandaSemplice($passoCorrente) {
        Out::html($this->nameForm . '_divContenitorePasso', '');

        /* @var $praCompPassoGest praCompPassoGest */
        $praCompPassoGest = itaFormHelper::innerForm('praCompDomandaSemplice', $this->nameForm . '_divContenitorePasso');
        $praCompPassoGest->setEvent('openform');
        $praCompPassoGest->setReturnModel($this->nameForm);
        $praCompPassoGest->setReturnEvent('returnFromGestPasso');
        $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
        //$praCompPassoGest->setPropak($this->datiWf->passi[0]['PROPAK']);
        $praCompPassoGest->setReturnId('');
        $praCompPassoGest->parseEvent();

        $this->praCompPassoGestFormname['praCompDomandaSemplice'] = $praCompPassoGest->getNameForm();
    }

    private function disegnaDomandaMultipla($passoCorrente) {
        Out::html($this->nameForm . '_divContenitorePasso', '');

        /* @var $praCompPassoGest praCompPassoGest */
        $praCompPassoGest = itaFormHelper::innerForm('praCompDatiAggiuntiviForm', $this->nameForm . '_divContenitorePasso');
        $praCompPassoGest->setEvent('openform');
        $praCompPassoGest->setReturnModel($this->nameForm);
        $praCompPassoGest->setReturnEvent('returnFromGestPasso');
//        $praCompPassoGest->setPropak($passoCorrente['PROPAK']);
//        $praCompPassoGest->setReturnId('');
        $praCompPassoGest->parseEvent();
        $praCompPassoGest->openGestione($passoCorrente['PRONUM'], $passoCorrente['PROPAK']);

        
        $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] = $praCompPassoGest->getNameForm();
    }
    
    
    private function salvaPassiFatti($passoSuccessivo) {
        $praLib = new praLib();

        $propasFatti_rec = Array();
        $passoAttuale = $this->datiWf->getPassoCorrente();


        // Salvo record PRAPASFATTI
        //Cerco record di PROPAS da aggiornare
        $sql = "SELECT * FROM PROPASFATTI "
                . " WHERE PROPASFATTI.PROPAK = '" . $passoAttuale['PROPAK'] . "' ";

        $propasFatti_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, false);

        
        
        if (!$propasFatti_rec) {
            $propasFatti_rec = Array();
            $propasFatti_rec['PRONUM'] = $passoAttuale['PRONUM'];
            $propasFatti_rec['PROPRO'] = $passoAttuale['PROPRO'];
            $propasFatti_rec['PROPAK'] = $passoAttuale['PROPAK'];
            $propasFatti_rec['PROSPA'] = $passoSuccessivo;
            
            $insert_info = 'Inserito record PROPASFATTI con PROPAK: ' . $propasFatti_rec['PROPAK'];

            if (!$this->insertRecord($praLib->getPRAMDB(), 'PROPASFATTI', $propasFatti_rec, $insert_info)) {
                
                return false;
            }

//            
//            try {
//                $nrow = ItaDB::DBInsert($praLib->getPRAMDB(), "PROPASFATTI", 'ROW_ID', $insert_info);
//                if ($nrow != 1) {
//                    return false;
//                }
//                $lastId = $praLib->getPRAMDB()->getLastId();
//                
//            } catch (Exception $exc) {
//                Out::msgStop("Errore", $exc->getMessage());
//                return false;
//            }            
            
        } else {
            $propasFatti_rec['PROSPA'] = $passoSuccessivo;

            $update_info = 'Salva passo fatto: ' . $propasFatti_rec['PROPAK'] . ' con passo successivo' . $propasFatti_rec['PROSPA'];

            if (!$this->updateRecord($praLib->getPRAMDB(), 'PROPASFATTI', $propasFatti_rec, $update_info, 'ROW_ID')) {

                return false;
            }
        }

        return true;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                $this->setClientConfig();
                Out::setFocus('', $this->nameForm . "_CONFIG[wsEndpoint]");
                break;
            case 'returnFromNavigatore':

                $arrayNavigatore = $this->formData;

                $praCompPassoGest=null;
                
                if ($this->praCompPassoGestFormname['praCompDomandaSemplice'] != null){
                    $praCompPassoGest = itaModel::getInstance('praCompDomandaSemplice', $this->praCompPassoGestFormname['praCompDomandaSemplice']);
                }
                else if ($this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] != null){
                    $praCompPassoGest = itaModel::getInstance('praCompDatiAggiuntiviForm', $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm']);                    
                }

                // Se non trovo la videata associata, metto sempre Domanda Semplice
                if ($praCompPassoGest==null){
                    $praCompPassoGest = itaModel::getInstance('praCompDomandaSemplice', $this->praCompPassoGestFormname['praCompDomandaSemplice']);
                }
                

                //$praCompPassoGest->returnToParent(false);
                // Il controllo dati inseriti e il salvataggio si fà solo se si preme 'Avanti'
                if ($arrayNavigatore['eventoNavigatore'] == 'avanti') {
                    
                    if ($praCompPassoGest instanceof praCompDomandaSemplice){
                        if (!$praCompPassoGest->preCondizioni()) {
                            Out::msgStop("Errore", $praCompPassoGest->getErrMessage());
                            break;
                        }
                    }
                    

                    if (!$praCompPassoGest->aggiornaDati()) {
                    //if (!$praCompPassoGest->salvataggio($this->datiWf->getPassoCorrente())) {
                        Out::msgStop("Errore", $praCompPassoGest->getErrMessage());
                        break;
                    }
                }

                //Prima di gestire la navigazione, bisogna rileggere il dizionario con le eventuali
                // modifica apportate.
                $this->datiWf->creaDizionari();


                $propakNuovo = $praCompPassoGest->navigazione($this->datiWf, $arrayNavigatore['eventoNavigatore']);

                //Out::msgInfo("Passo Destinazione", $propakNuovo);
                
                if ($arrayNavigatore['eventoNavigatore'] == 'avanti') {
                    if (!$this->salvaPassiFatti($propakNuovo)) {
                        break;
                    }
                }

                //Imposto il nuovo passo come quello corrente
                $this->datiWf->setPassoCorrente($propakNuovo);

                $this->inizializzaOggetti();

                break;
            case 'returnFromGestPasso':
                //Out::msgInfo("returnFromGestPasso", print_r($_POST, true));

                break;

            case 'returnFromDiagramma':
            case 'returnFromElencoPassi':
                $propak = $this->formData;
                
                //Imposto il nuovo passo come quello corrente
                $this->datiWf->setPassoCorrente($propak);
                
                $this->inizializzaOggetti();
                
                break;
                
            case 'onClick':
                switch ($_POST['id']) {

                    case $this->nameForm . '_callTestNavigatore':
                        $this->praCompPassoGestFormname = array();

                        /* @var $this->$datiWf praLibDatiWorkFlow */
                        $this->datiWf = null;
                        $this->datiWf = new praLibDatiWorkFlow($this->formData[$this->nameForm . '_fascicolo']);
                        //Out::msgInfo("Passi", print_r($this->datiWf->getPassi(), true));

                        // Diagramma (WorkFlow)
                        Out::html($this->nameForm . '_divContenitoreDiagramma', '');

                        /* @var $praCompDiagramma praCompDiagramma */
                        $praCompDiagramma = itaFormHelper::innerForm('praCompDiagramma', $this->nameForm . '_divContenitoreDiagramma');
                        $praCompDiagramma->setEvent('openform');
                        $praCompDiagramma->setReturnModel($this->nameForm);
                        $praCompDiagramma->setReturnEvent('returnFromDiagramma');
                        $praCompDiagramma->setDatiWorkflow($this->datiWf);
                        $praCompDiagramma->parseEvent();
                        
                        $this->praCompPassoGestFormname['praCompDiagramma'] = $praCompDiagramma->getNameForm();
                        
                        // Elenco Passi
                        Out::html($this->nameForm . '_divContenitoreElencoPassi', '');

                        /* @var $praCompElencoPassi praCompElencoPassi */
                        $praCompElencoPassi = itaFormHelper::innerForm('praCompElencoPassi', $this->nameForm . '_divContenitoreElencoPassi');
                        $praCompElencoPassi->setEvent('openform');
                        $praCompElencoPassi->setReturnModel($this->nameForm);
                        $praCompElencoPassi->setReturnEvent('returnFromElencoPassi');
                        $praCompElencoPassi->setReturnId('');
                        $praCompElencoPassi->setDatiWorkflow($this->datiWf);
                        $praCompElencoPassi->parseEvent();

                        $this->praCompPassoGestFormname['praCompElencoPassi'] = $praCompElencoPassi->getNameForm();

                        //Navigatore
                        Out::html($this->nameForm . '_divContenitoreNavigatore', '');

                        /* @var $praCompNavigatore praCompNavigatore */
                        $praCompNavigatore = itaFormHelper::innerForm('praCompNavigatore', $this->nameForm . '_divContenitoreNavigatore');
                        $praCompNavigatore->setEvent('openform');
                        $praCompNavigatore->setReturnModel($this->nameForm);
                        $praCompNavigatore->setReturnEvent('returnFromNavigatore');
                        $praCompNavigatore->setReturnId('');
                        $praCompNavigatore->inizializza($this->datiWf);
                        $praCompNavigatore->parseEvent();

                        $this->praCompPassoGestFormname['praCompNavigatore'] = $praCompNavigatore->getNameForm();

                        //Disegna il Form associato al passo corrente e sistemata tutti i 
                        //subform
                        $this->inizializzaOggetti();
                        
                        //Out::msgInfo("Mappa Componenti", print_r($this->praCompPassoGestFormname, true));
                        //Out::msgInfo("Dizionario", print_r($this->datiWf->getDizionari(), true));
                        
                        break;
                    case $this->nameForm . '_callDomandaSemplice':
                        itaLib::openForm('praCompDomandaSemplice');
                        /* @var $objDomanda praCompDomandaSemplice */
                        $objDomanda = itaModel::getInstance('praCompDomandaSemplice');
                        $objDomanda->setPropak($this->formData[$this->nameForm . '_propak']);
                        $objDomanda->setEvent('openform');
                        $objDomanda->parseEvent();

                        break;
                    case $this->nameForm . '_callLanciaPragest2':
                        itaLib::openForm('praGest2');
                        /* @var $objDomanda praCompDomandaSemplice */
                        $objDomanda = itaModel::getInstance('praGest2');

                        $objDomanda->setEvent('openform');
                        $objDomanda->parseEvent();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_invioComunicazione_filePath');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function setClientConfig() {
        $config_tab = array();
        $devLib = new devLib();
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEENDPOINT', false);
        $config_rec['wsEndpoint'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEUSER', false);
        $config_rec['wsUser'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEPASSWD', false);
        $config_rec['wsPassword'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICENAMESPACE', false);
        $config_rec['wsNameSpace'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICETIMEOUT', false);
        $config_rec['wsTimeout'] = $config_val['CONFIG'];

        Out::valori($config_rec, $this->nameForm . '_CONFIG');
    }


    private function inizializzaOggetti(){

        $this->datiWf->creaDizionari();

        $this->praCompPassoGestFormname['praCompDomandaSemplice'] = null;
        $this->praCompPassoGestFormname['praCompDatiAggiuntiviForm'] = null;
        
        $passoCorrente = $this->datiWf->getPassoCorrente();
        if ($passoCorrente['PROQST'] == 0){
            // Raccolta Dati
            $this->disegnaDomandaMultipla($this->datiWf->getPassoCorrente());
        }
        else if ($passoCorrente['PROQST'] == 1){
            // Domanda Semplice
            $this->disegnaDomandaSemplice($this->datiWf->getPassoCorrente());
        }
        if ($passoCorrente['PROQST'] == 2){
            // Domanda Muiltipla
            $this->disegnaDomandaMultipla($this->datiWf->getPassoCorrente());
        }

        //Posiziona la griglia dei passi sul record corrente
        $praCompElencoPassi = itaModel::getInstance('praCompElencoPassi', $this->praCompPassoGestFormname['praCompElencoPassi']);
        $praCompElencoPassi->setDatiWorkflow($this->datiWf);
        $praCompElencoPassi->refreshSelection();

        $praCompNavigatore = itaModel::getInstance('praCompNavigatore', $this->praCompPassoGestFormname['praCompNavigatore']);
        $praCompNavigatore->inizializza($this->datiWf);

        $praCompDiagramma = itaModel::getInstance('praCompDiagramma', $this->praCompPassoGestFormname['praCompDiagramma']);
        $praCompDiagramma->setDatiWorkflow($this->datiWf);
        $praCompDiagramma->refreshSelection();

        
    }
}
