<?php

/**
 *
 * GESTIONE Soggetti
 *
 * PHP Version 5
 *
 * @category
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    06.03.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once (ITA_BASE_PATH . '/apps/Base/basLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Base/basRic.class.php');
include_once(ITA_LIB_PATH . '/itaPHPWSProcedimarche/itaRESTProcedimarcheClient.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibGenMetadata.class.php';

function praProcedimarcheRest() {
    $praProcedimarcheRest = new praProcedimarcheRest();
    $praProcedimarcheRest->parseEvent();
    return;
}

class praProcedimarcheRest extends itaModel {

    public $PRAM_DB;
    public $ITALWEB_DB;
    public $nameForm = "praProcedimarcheRest";
    public $divGestione = "praProcedimarcheRest_divGestione";
    public $divRisultato = "praProcedimarcheRest_divRisultato";
    public $gridAggregati = "praProcedimarcheRest_gridAggregati";
    public $praLib;
    public $proLib;
    public $returnModel;
    public $returnEvent;
    public $restClient;
    public $rowid;
    public $procediKey;
    public $procediIdRecord;
    public $procediIdGenerico;
    public $procediCf;
    public $procediDatiGenerali;
    public $procediDatiSpecifici;
    public $praLibGenMetadata;
    public $genMetadata;
    public $aggregati;
    public $aggregato;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->devLib = new devLib();
            $this->praLibGenMetadata = new praLibGenMetadata();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITALWEB_DB = $this->praLib->getITALWEBDB();
            $this->restClient = new itaRESTProcedimarcheClient();
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->rowid = App::$utente->getKey($this->nameForm . '_rowid');
            $this->procediKey = App::$utente->getKey($this->nameForm . '_procediKey');
            $this->procediIdRecord = App::$utente->getKey($this->nameForm . '_procediIdRecord');
            $this->procediIdGenerico = App::$utente->getKey($this->nameForm . '_procediIdGenerico');
            $this->procediCf = App::$utente->getKey($this->nameForm . '_procediCf');
            $this->procediDatiGenerali = App::$utente->getKey($this->nameForm . '_procediDatiGenerali');
            $this->procediDatiGenerali = App::$utente->getKey($this->nameForm . '_procediDatiSpecifici');
            $this->genMetadata = App::$utente->getKey($this->nameForm . '_genMetadata');
            $this->aggregati = App::$utente->getKey($this->nameForm . '_aggregati');
            $this->aggregato = App::$utente->getKey($this->nameForm . '_aggregato');
            //
            $this->inizializzaConnessione();
            //
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_rowid', $this->rowid);
            App::$utente->setKey($this->nameForm . '_procediKey', $this->procediKey);
            App::$utente->setKey($this->nameForm . '_procediIdRecord', $this->procediIdRecord);
            App::$utente->setKey($this->nameForm . '_procediIdGenerico', $this->procediIdGenerico);
            App::$utente->setKey($this->nameForm . '_procediCf', $this->procediCf);
            App::$utente->setKey($this->nameForm . '_procediDatiGenerali', $this->procediDatiGenerali);
            App::$utente->setKey($this->nameForm . '_procediDatiSpecifici', $this->procediDatiGenerali);
            App::$utente->setKey($this->nameForm . '_genMetadata', $this->genMetadata);
            App::$utente->setKey($this->nameForm . '_aggregati', $this->aggregati);
            App::$utente->setKey($this->nameForm . '_aggregato', $this->aggregato);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->rowid = $_POST['ITEROW'];
                $Anaspa_tab = $this->leggiAggregati();
                $this->aggregati = $this->preparaAggregati($Anaspa_tab);
                $this->aggregato = array();
                $this->predisponiModel();
                //$this->dettaglio();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_IdTipoProcedimentoGenerico_butt':
                        $this->RicTipoProcedimentoGenerale();
                        break;
                    case $this->nameForm . '_IdSerieArchivistica_butt':
                        $this->RicSerieArchivistica();
                        break;
                    case $this->nameForm . '_IdTipoFascicolo_butt':
                        $this->RicTipoFascicolo();
                        break;
                    case $this->nameForm . '_Invia':
                        if ($this->Invia()) {
                            
                        }
                        break;
                    case $this->nameForm . '_Torna':
                        Out::show($this->nameForm . '_divRisultato');
                        Out::hide($this->nameForm . '_divGestione');
                        Out::hide($this->nameForm . '_Torna');
                        Out::hide($this->nameForm . '_Invia');
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAggregati:
                        $rowid = $_POST['rowid'];
                        if ($rowid) {
                            if ($this->aggregati[$rowid]['STATO'] == 1) {
                                $this->aggregato = $this->aggregati[$rowid];
                                $this->inizializzaConnessione();
                                $this->dettaglio();
                            } else {
                                Out::msgInfo('ATTENZIONE', 'Invio non possibile per mancanza di parametri.');
                            }
                        }
                }
                break;
            case 'returnRicTipoProcedimentoGenerale':
                $ret = $_POST;
                Out::valore($this->nameForm . '_IdTipoProcedimentoGenerico', $ret['rowData']['Id']);
                break;
            case 'returnRicSerieArchivistica':
                $ret = $_POST;
                Out::valore($this->nameForm . '_IdSerieArchivistica', $ret['rowData']['Id']);
                break;
            case 'returnRicTipoFascicolo':
                $ret = $_POST;
                Out::valore($this->nameForm . '_IdTipoFascicolo', $ret['rowData']['Id']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_rowid');
        App::$utente->removeKey($this->nameForm . '_procediKey');
        App::$utente->removeKey($this->nameForm . '_procediIdRecord');
        App::$utente->removeKey($this->nameForm . '_procediCf');
        App::$utente->removeKey($this->nameForm . '_procediDatiGenerali');
        App::$utente->removeKey($this->nameForm . '_procediDatiSpecifici');
        App::$utente->removeKey($this->nameForm . '_genMetadata');
        App::$utente->removeKey($this->nameForm . '_aggregati');
        App::$utente->removeKey($this->nameForm . '_aggregato');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['model'] = $this->returnModel;
        $_POST['soggetto'] = $this->soggetto;
        $_POST['rowid'] = $this->rowid;
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
        $returnModel = $this->returnModel;
        $returnModel();
        if ($close)
            $this->close();
    }

    public function Invia() {
        $Iteevt_rec = $this->praLib->GetIteevt($this->rowid, 'rowid');
        if (!$Iteevt_rec) {
            Out::msgStop("Errore", "Evento da elaborare non accessibile");
            return false;
        }
        if ($_POST[$this->nameForm . '_IdTipoProcedimentoGenerico'] == '') {
            Out::msgInfo('AVVISO', 'Selezionare un tipo di procedimento generico.');
            return false;
        }
        $idEsterno = $_POST[$this->nameForm . '_IdEsterno'];
        if ($idEsterno) {
            if ($this->restClient->rest_TipoProcedimentoSpecificoUpdate($idEsterno, $this->getJsondati())) {
                $audit_Info = "Aggiornato Procedimarche: id $idEsterno Procedimento {$Iteevt_rec['ITEPRA']}-{$Iteevt_rec['IEVCOD']} ";
                $this->insertAudit($this->PRAM_DB, "TEEVT", $audit_Info);
                Out::msgInfo('AVVISO', 'Procedimento Aggiornato');
            } else {
                Out::msgStop("Error Update $idSpecifico", $this->restClient->getErrMessage());
                Out::msgStop("Status Update $idSpecifico", $this->restClient->getHttpStatus());
                return false;
            }
        } else {
            if ($this->restClient->rest_TipoProcedimentoSpecificoInsert($this->getJsondati())) {
                if ($this->restClient->getHttpStatus() == 201) {
                    $codiceAggregato = '0';
                    if ($this->aggregato) {
                        $codiceAggregato = $this->aggregato['CODICE'];
                    }
                    $idEsterno = $this->restClient->getResult();
                    $insMetadata = array(
                        'CLASSE' => 'ITEEVT',
                        'CHIAVE' => $Iteevt_rec['ITEPRA'] . '-' . $Iteevt_rec['IEVCOD'] . '-' . $codiceAggregato,
                        'CAMPO' => 'RELPROCEDIMARCHE',
                        'VALORE' => $_POST[$this->nameForm . '_IdTipoProcedimentoGenerico'] . "|" . $_POST[$this->nameForm . '_CfEnte'] . "|" . $idEsterno
                    );
                    $result = $this->praLibGenMetadata->insertGenMetadata($insMetadata);
                    if ($result !== true) {
                        Out::msgStop('ATTENZIONE', 'Procedimento versato in Regione con questi valori : ' . print_r($insMetadata, true) . ' Segnalazione di errore in GENMETADATA.  ' . $this->praLibGenMetadata->getErrMessage());
                        return false;
                    }
                    Out::valore($this->nameForm . '_IdEsterno', $idEsterno);
                    Out::msgInfo('AVVISO', 'Procedimento Inviato');
                    return true;
                }
                Out::msgInfo("Result Insert", $this->restClient->getResult());
                Out::msgInfo("Status Insert", $this->restClient->getHttpStatus());
                return false;
            } else {
                Out::msgStop("Error Insert", $this->restClient->getErrMessage());
                Out::msgStop("Status Insert", $this->restClient->getHttpStatus());
                return false;
            }
        }
        return true;
    }

    private function getProcedimarcheConfig($classe) {
        $config = array();
        $endPoint = $this->devLib->getEnv_config($classe, 'codice', 'WSRESTENDPOINT', false);
        $utente = $this->devLib->getEnv_config($classe, 'codice', 'WSUTENTE', false);
        $password = $this->devLib->getEnv_config($classe, 'codice', 'WSPASSWORD', false);
        $cfEnte = $this->devLib->getEnv_config($classe, 'codice', 'CFENTE', false);
        $timeout = $this->devLib->getEnv_config($classe, 'codice', 'TIMEOUT', false);
        $urlModulistica = $this->devLib->getEnv_config($classe, 'codice', 'URLMODULISTICA', false);
        $urlServizio = $this->devLib->getEnv_config($classe, 'codice', 'URLSERVIZIO', false);
//
        $this->config['endPoint'] = $endPoint['CONFIG'];
        $this->config['utente'] = $utente['CONFIG'];
        $this->config['password'] = $password['CONFIG'];
        $this->config['cfEnte'] = $cfEnte['CONFIG'];
        $this->config['timeout'] = $timeout['CONFIG'];
        $this->config['urlModulistica'] = $urlModulistica['CONFIG'];
        $this->config['urlServizio'] = $urlServizio['CONFIG'];
    }

    private function setClientConfig($restClient, $classe) {
        $endPoint = $this->devLib->getEnv_config($classe, 'codice', 'WSRESTENDPOINT', false);
        $utente = $this->devLib->getEnv_config($classe, 'codice', 'WSUTENTE', false);
        $password = $this->devLib->getEnv_config($classe, 'codice', 'WSPASSWORD', false);
        $cfEnte = $this->devLib->getEnv_config($classe, 'codice', 'CFENTE', false);
        $timeout = $this->devLib->getEnv_config($classe, 'codice', 'TIMEOUT', false);
//
        $restClient->setWebservices_uri($endPoint['CONFIG']);
        $restClient->setUsername($utente['CONFIG']);
        $restClient->setPassword($password['CONFIG']);
        $restClient->setCF($cfEnte['CONFIG']);
        $restClient->setTimeout($timeout['CONFIG']);
    }

    private function getJsondati() {
        $jsondati = '	{
                        "IdTipoProcedimentoGenerico" :"' . $_POST[$this->nameForm . '_IdTipoProcedimentoGenerico'] . '", 
                        "IdSerieArchivistica" : "' . $_POST[$this->nameForm . '_IdSerieArchivistica'] . '", 
                        "IdTipoFascicolo" : "' . $_POST[$this->nameForm . '_IdTipoFascicolo'] . '", 
                        "CfEnte" : "' . $_POST[$this->nameForm . '_CfEnte'] . '", 
                        "IdProcedimentoEnte" :"' . $_POST[$this->nameForm . '_IdProcedimentoEnte'] . '", 
                        "NomeProcedimentoEnte" : "' . $_POST[$this->nameForm . '_NomeProcedimentoEnte'] . '", 
                        "DettaglioTitolario" : "' . $_POST[$this->nameForm . '_DettaglioTitolario'] . '", 
                        "CodiceClassifica" : "' . $_POST[$this->nameForm . '_CodiceClassifica'] . '", 
                        "AnniConservazione" : "' . $_POST[$this->nameForm . '_AnniConservazione'] . '", 
                        "UoCompetenzaIstruttoria" : "' . $_POST[$this->nameForm . '_UoCompetenzaIstruttoria'] . '", 
                        "UoCompetenzaProvvedimentoFinale" : "' . $_POST[$this->nameForm . '_UoCompetenzaProvvedimentoFinale'] . '", 
                        "UoRecapitiIstruttoria" : "' . $_POST[$this->nameForm . '_UoRecapitiIstruttoria'] . '", 
                        "ResponsabileNome" : "' . $_POST[$this->nameForm . '_ResponsabileNome'] . '", 
                        "ResponsabileCognome" : "' . $_POST[$this->nameForm . '_ResponsabileCognome'] . '", 
                        "NomeCognomeSostituto" :"' . $_POST[$this->nameForm . '_NomeCognomeSostituto'] . '", 
                        "TermineConclusione" : "' . $_POST[$this->nameForm . '_TermineConclusione'] . '", 
                        "MaxGiorniTermine" :"' . $_POST[$this->nameForm . '_MaxGiorniTermine'] . '", 
                        "AttoDefinizioneTermine" : "' . $_POST[$this->nameForm . '_AttoDefinizioneTermine'] . '", 
                        "ModalitaPagamenti" : "' . $_POST[$this->nameForm . '_ModalitaPagamenti'] . '", 
                        "ModalitaRichiestaInfo" : "' . $_POST[$this->nameForm . '_ModalitaRichiestaInfo'] . '", 
                        "LinkModulistica" : "' . $_POST[$this->nameForm . '_LinkModulistica'] . '", 
                        "LinkServizio" : "' . $_POST[$this->nameForm . '_LinkServizio'] . '", 
                        "CustomerSatisfation" :"' . $_POST[$this->nameForm . '_CustomerSatisfation'] . '", 
                        }';
        return $jsondati;
    }

    private function dettaglio() {

        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_divGestione');
        Out::show($this->nameForm . '_Invia');
        Out::hide($this->nameForm . '_Torna');

        Out::hide($this->nameForm . '_divAggregato');
        if ($this->aggregato) {
            Out::valore($this->nameForm . '_NomeAggregato', $this->aggregato['AGGREGATO']);
            Out::show($this->nameForm . '_divAggregato');
            Out::show($this->nameForm . '_Torna');
        }

        $Iteevt_rec = $this->praLib->GetIteevt($this->rowid, 'rowid');
        $Anapra_rec = $this->praLib->GetAnapra($Iteevt_rec['ITEPRA'], 'codice');
        $Anaset_rec = $this->praLib->GetAnaset($Iteevt_rec['IEVSTT'], 'codice');
        $Anaatt_rec = $this->praLib->GetAnaatt($Iteevt_rec['IEVATT'], 'codice');
        $Ananom_rec = $this->praLib->GetAnanom($Anapra_rec['PRARES'], 'codice');
        $Anatsp_rec = $this->praLib->GetAnatsp($Iteevt_rec['IEVTSP'], 'codice');

        $pecSportello = $Anatsp_rec['TSPPEC'];
        $linkModulistica = $this->config['urlModulistica'];
        $linkModulistica = str_replace("%proc%", $Anapra_rec['PRANUM'], $linkModulistica);
        $linkModulistica = str_replace("%subproc%", $Iteevt_rec['IEVCOD'], $linkModulistica);
        $linkServizio = $this->config['urlServizio'];
        $linkServizio = str_replace("%proc%", $Anapra_rec['PRANUM'], $linkServizio);
        $linkServizio = str_replace("%subproc%", $Iteevt_rec['IEVCOD'], $linkServizio);
        $codiceAggregato = '0';
        if ($this->aggregato) {
            $codiceAggregato = $this->aggregato['CODICE'];
        }
        $ricMetadata = array(
            'CLASSE' => 'ITEEVT',
            'CHIAVE' => $Iteevt_rec['ITEPRA'] . '-' . $Iteevt_rec['IEVCOD'] . '-' . $codiceAggregato,
            'CAMPO' => 'RELPROCEDIMARCHE'
        );
        $this->genMetadata = $this->praLibGenMetadata->getGenMetadata($ricMetadata);
        $this->procediKey = $this->genMetadata['VALORE'];
        $this->procediIdRecord = null;
        $this->procediIdGenerico = null;
        $this->procediCf = null;
        $trovato = false;
        if ($this->procediKey) {
            if ($this->trovaProcediMarche($this->procediKey)) {
                Out::valore($this->nameForm . '_IdEsterno', $this->procediIdRecord);
                foreach ($this->procediDatiSpecifici as $key => $value) {
                    Out::valore($this->nameForm . "_$key", $value);
                }
                Out::disableField($this->nameForm . '_IdTipoProcedimentoGenerico');
                $trovato = true;
            }
        }
        if ($trovato === false) {
            Out::valore($this->nameForm . '_IdEsterno', '');
            Out::valore($this->nameForm . '_IdSerieArchivistica', '0');
            Out::valore($this->nameForm . '_IdTipoFascicolo', '1');
            Out::valore($this->nameForm . '_NomeProcedimentoEnte', $Anaset_rec["SETDES"] . " " . $Anaatt_rec['ATTDES'] . " " . $Anapra_rec['PRADES__1']);
            Out::valore($this->nameForm . '_DettaglioTitolario', 'Commercio');
            //Out::valore($this->nameForm . '_DettaglioTitolario', $Anaset_rec["SETDES"]);
            //Out::valore($this->nameForm . '_CodiceClassifica', $Anaatt_rec['ATTDES']);
            Out::valore($this->nameForm . '_AnniConservazione', '10');
            Out::valore($this->nameForm . '_CfEnte', $this->config['cfEnte']);
            Out::valore($this->nameForm . '_UoRecapitiIstruttoria', $pecSportello);
            Out::valore($this->nameForm . '_UoCompetenzaProvvedimentoFinale', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
            Out::valore($this->nameForm . '_ResponsabileNome', $Ananom_rec['NOMNOM']);
            Out::valore($this->nameForm . '_ResponsabileCognome', $Ananom_rec['NOMCOG']);
            Out::valore($this->nameForm . '_NomeCognomeSostituto', "Segretario Comunale");
            Out::valore($this->nameForm . '_IdProcedimentoEnte', $Anapra_rec['PRANUM'] . "-" . $Iteevt_rec['IEVCOD']);
        }
        Out::valore($this->nameForm . '_LinkModulistica', $linkModulistica);
        Out::valore($this->nameForm . '_LinkServizio', $linkServizio);
        //Out::disableField($this->nameForm . '_LinkModulistica');
    }

    private function trovaProcedimarche($procediKey) {
        list($procediIdGenerico, $procediCf, $procediIdRecord) = explode("|", $procediKey);
        if ($this->restClient->rest_TipoProcedimentoCompleto()) {
            $procediData_tab = json_decode($this->restClient->getResult(), true);
            $datiGenerali = null;
            $datiSpecifici = null;
            foreach ($procediData_tab as $key => $procediData_rec) {
                $datiGenerali = $procediData_rec['DatiGenerali'];
                $datiSpecifici = $procediData_rec['DatiSpecifici'];
                if ($datiSpecifici['IdTipoProcedimentoGenerico'] == $procediIdGenerico && $datiSpecifici['CfEnte'] == $procediCf) {
                    break;
                }
                $datiGenerali = null;
                $datiSpecifici = null;
            }
            if ($datiSpecifici) {
                $this->procediIdGenerico = $procediIdGenerico;
                $this->procediIdRecord = $procediIdRecord;
                $this->procediCf = $procediCf;
                $this->procediDatiGenerali = $datiGenerali;
                $this->procediDatiSpecifici = $datiSpecifici;
                return true;
            } else {
                return false;
            }
        } else {
            Out::msgStop("TipiProcedimentoCompleto   ", $this->restClient->getError());
            return false;
        }
    }

    private function RicSerieArchivistica() {
        if (!$this->restClient->rest_SerieArchivistica()) {
            return false;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Seleziona la Serire Archivistica",
            "width" => '400',
            "height" => '400',
            "rowNum" => '15',
            "filterToolbar" => 'false',
            "rowList" => '[]',
            "arrayTable" => json_decode($this->restClient->getResult(), true),
            "colNames" => array(
                "Codice",
                "Descrizione    "
            ),
            "colModel" => array(
                array("name" => 'Id', "width" => 60),
                array("name" => 'Descrizione', "width" => 330)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnRicSerieArchivistica';
        $_POST['returnKey'] = 'retKey';
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function RicTipoFascicolo() {
        if (!$this->restClient->rest_TipoFascicolo()) {
            return false;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => "Seleziona il Tipo Fascicolo",
            "width" => '400',
            "height" => '400',
            "rowNum" => '15',
            "filterToolbar" => 'false',
            "rowList" => '[]',
            "arrayTable" => json_decode($this->restClient->getResult(), true),
            "colNames" => array(
                "Codice",
                "Descrizione    "
            ),
            "colModel" => array(
                array("name" => 'Id', "width" => 60),
                array("name" => 'Descrizione', "width" => 330)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnRicTipoFascicolo';
        $_POST['returnKey'] = 'retKey';
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    private function RicTipoProcedimentoGenerale() {
        if (!$this->restClient->rest_TipoProcedimentoGenerale()) {
            return false;
        }
        $model = 'utiRicDiag';

        $filterName = array('Descrizione');

        $gridOptions = array(
            "Caption" => "Seleziona il Tipo Procedimento",
            "width" => '600',
            "height" => '400',
            "rowNum" => '15',
            "filterToolbar" => 'true',
            "filterName" => $filterName,
            "rowList" => '[]',
            "arrayTable" => json_decode($this->restClient->getResult(), true),
            "colNames" => array(
                "Codice",
                "Descrizione    "
            ),
            "colModel" => array(
                array("name" => 'Id', "width" => 60),
                array("name" => 'Descrizione', "width" => 500)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = 'returnRicTipoProcedimentoGenerale';
        $_POST['returnKey'] = 'retKey';
        if ($msgDetail != '') {
            $_POST['msgDetail'] = $msgDetail;
        }
        itaLib::openForm($model, true, true, 'desktopBody', $returnModel);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    function leggiAggregati() {
        return ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPAATT = 1 ORDER BY SPADES", true);
    }

    function preparaAggregati($Anaspa_tab) {
        $Iteevt_rec = $this->praLib->GetIteevt($this->rowid, 'rowid');
        $aggregati = array();
        $i = 0;
        if ($Anaspa_tab) {
            foreach ($Anaspa_tab as $key => $aggregato) {
                $i ++;
                $aggregati[$i]['CODICE'] = $aggregato['SPACOD'];
                $aggregati[$i]['AGGREGATO'] = $aggregato['SPADES'];
                $aggregati[$i]['STATO'] = '';
                $aggregati[$i]['PARAMETRO_PROCEDIMARCHE'] = '';
                $aggregati[$i]['PARAMETRI'] = "<span class = \"ita-icon ita-icon-bullet-red-16x16\" style=\"display: inline-block;\"></span>";
                if ($aggregato['SPAMETAPROT']) {
                    $arrayMeta = unserialize($aggregato['SPAMETAPROT']);
                    if ($arrayMeta['CLASSIPARAMETRI']['PROCEDIMARCHE']['KEYPARAMWSPROCEDIMARCHE']) {
                        $aggregati[$i]['STATO'] = '1';
                        $aggregati[$i]['PARAMETRO_PROCEDIMARCHE'] = $arrayMeta['CLASSIPARAMETRI']['PROCEDIMARCHE']['KEYPARAMWSPROCEDIMARCHE'];
                        $aggregati[$i]['PARAMETRI'] = "<span class = \"ita-icon ita-icon-bullet-green-16x16\" style=\"display: inline-block;\"></span>";
                    }
                }
                $ricMetadata = array(
                    'CLASSE' => 'ITEEVT',
                    'CHIAVE' => $Iteevt_rec['ITEPRA'] . '-' . $Iteevt_rec['IEVCOD'] . '-' . $aggregato['SPACOD'],
                    'CAMPO' => 'RELPROCEDIMARCHE'
                );
                if ($this->controllaSeTrasmesso($ricMetadata)) {
                    $aggregati[$i]['TRASMESSO'] = "<span class=\"ita-icon ita-icon-regioneMarche-24x24\" style=\"display: inline-block;\" title=\"Pubblicato su procediMarche\"></span>";
                } else {
                    $aggregati[$i]['TRASMESSO'] = '';
                }
            }
            /*
             * Controlla se esiste anche il parametro PROCEDIMARCHE per l'ente che rappresenta l'aggregato
             */
            $envconfigTab = $this->devLib->getEnv_config('PROCEDIMARCHE', 'codice');
            if ($envconfigTab) {
                $i ++;
                $aggregati[$i]['CODICE'] = '0';
                $aggregati[$i]['AGGREGATO'] = '';
                foreach ($envconfigTab as $envconfigRec) {
                    if ($envconfigRec['CHIAVE'] == 'PARAM_CLASS_DESC') {
                        $aggregati[$i]['AGGREGATO'] = $envconfigRec['CONFIG'];
                        break;
                    }
                }
                $aggregati[$i]['STATO'] = '1';
                $aggregati[$i]['PARAMETRO_PROCEDIMARCHE'] = 'PROCEDIMARCHE';
                $aggregati[$i]['PARAMETRI'] = "<span class = \"ita-icon ita-icon-bullet-green-16x16\" style=\"display: inline-block;\"></span>";
            }
        }

        return $aggregati;
    }

    function predisponiModel() {
        if (!$this->aggregati) {
            Out::hide($this->nameForm . '_divRisultato');
            Out::show($this->nameForm . '_divGestione');
            Out::show($this->nameForm . '_Invia');
            $this->inizializzaConnessione();
            $this->dettaglio();
        } else {
            $this->caricaTabellaAggregati();
            Out::show($this->nameForm . '_divRisultato');
            Out::hide($this->nameForm . '_divGestione');
            Out::hide($this->nameForm . '_Invia');
            Out::hide($this->nameForm . '_Torna');
        }
    }

    function caricaTabellaAggregati() {
        $ita_grid01 = new TableView(
                $this->gridAggregati, array('arrayTable' => $this->aggregati,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('10000');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridAggregati);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridAggregati);
        }
    }

    function inizializzaConnessione() {
        $classe = 'PROCEDIMARCHE';
        if ($this->aggregato['PARAMETRO_PROCEDIMARCHE']) {
            $classe = $this->aggregato['PARAMETRO_PROCEDIMARCHE'];
        }
        $this->setClientConfig($this->restClient, $classe);
        $this->getProcedimarcheConfig($classe);
    }

    function controllaSeTrasmesso($ricMetadata) {
        if ($this->praLibGenMetadata->getGenMetadata($ricMetadata)) {
            return true;
        } else {
            return false;
        }
    }

}

?>