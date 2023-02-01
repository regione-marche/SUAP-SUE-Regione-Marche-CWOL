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
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once (ITA_BASE_PATH . '/apps/Base/basLib.class.php');
include_once (ITA_BASE_PATH . '/apps/Base/basRic.class.php');
include_once ITA_BASE_PATH . '/apps/GeoUtils/geoLib.class.php';

function praSoggettiGest() {
    $praSoggettiGest = new praSoggettiGest();
    $praSoggettiGest->parseEvent();
    return;
}

class praSoggettiGest extends itaModel {

    public $PRAM_DB;
    public $COMUNI_DB;
    public $nameForm = "praSoggettiGest";
    public $divDettaglio = "praSoggettiGest_divDettaglio";
    public $praLib;
    public $proLib;
    public $returnModel;
    public $returnEvent;
    public $soggetto;
    public $rowid;
    public $unitaLocale;
    public $basLib;
    public $geoLib;
    public $idAppoggio;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->basLib = new basLib();
            $this->geoLib = new geoLib();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->returnModel = App::$utente->getKey($this->nameForm . '_returnModel');
            $this->returnEvent = App::$utente->getKey($this->nameForm . '_returnEvent');
            $this->soggetto = App::$utente->getKey($this->nameForm . '_soggetto');
            $this->rowid = App::$utente->getKey($this->nameForm . '_rowid');
            $this->unitaLocale = App::$utente->getKey($this->nameForm . '_unitaLocale');
            $this->idAppoggio = App::$utente->getKey($this->nameForm . '_idAppoggio');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_returnModel', $this->returnModel);
            App::$utente->setKey($this->nameForm . '_returnEvent', $this->returnEvent);
            App::$utente->setKey($this->nameForm . '_soggetto', $this->soggetto);
            App::$utente->setKey($this->nameForm . '_rowid', $this->rowid);
            App::$utente->setKey($this->nameForm . '_unitaLocale', $this->unitaLocale);
            App::$utente->setKey($this->nameForm . '_idAppoggio', $this->idAppoggio);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->unitaLocale = "";
                Out::show($this->nameForm);
                $this->returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnEvent = $_POST[$this->nameForm . '_returnEvent'];
                $this->soggetto = $_POST['soggetto'];
                $perms = $_POST['perms'];
                $this->unitaLocale = $_POST['unitaLocale'];
                Out::valore($this->nameForm . '_ANADES[DESNUM]', $_POST['pratica']);
                $this->Nascondi();
                Out::setFocus('', $this->nameForm . '_ANADES[DESNOM]');
                if ($_POST['rowid'] != "") {
                    $this->rowid = $_POST['rowid'];
                    $this->Modifica($perms);
                } else {
                    $this->Nuovo();
                }

                if ($this->unitaLocale) {
                    Out::setFocus('', $this->nameForm . '_ANADES[DESRAGSOC]');
                } else {
                    Out::setFocus('', $this->nameForm . '_ANADES[DESNOM]');
                }
                //MAPGENTILE
                $Attivazioni = $this->geoLib->getAttivazioni('PRATICHE');
                if ($Attivazioni && $_POST['soggetto']['ROWID']) {
                    Out::show($this->nameForm . '_MapGentile');
                } else {
                    Out::hide($this->nameForm . '_MapGentile');
                }
                $this->idAppoggio = $_POST['soggetto']['ROWID'];

                if ($this->returnEvent == "returnUnitaLocale") {
                    Out::setDialogTitle($this->nameForm, "Localizzazione Intervento");
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_MapGentile':
                        $model = "geoOggetti";
                        $_POST['tipo'] = 'PRATICHE';
                        $_POST['rowid'] = $this->idAppoggio;
                        $_POST['codice'] = $_POST[$this->nameForm . '_ANADES']['DESCODIND'];
                        $_POST['civico'] = $_POST[$this->nameForm . '_ANADES']['DESCIV'];
                        itaLib::openDialog($model);
                        $objBdaLavori = itaModel::getInstance($model);
                        $objBdaLavori->setReturnModel($this->nameForm);
                        $objBdaLavori->setReturnEvent('ReturnVar');
                        if (!$objBdaLavori) {
                            break;
                        }
                        $objBdaLavori->setEvent('openform');
                        $objBdaLavori->parseEvent();
                        break;

                    case $this->nameForm . '_Aggiungi':
                    case $this->nameForm . '_Aggiorna':
                        if (!$this->checkPrerequisiti()) {
                            break;
                        }
                        $this->soggetto = $_POST[$this->nameForm . '_ANADES'];
                        $this->updateRecord($this->PRAM_DB, 'ANADES', $_POST[$this->nameForm . '_ANADES'], $update_Info);
                        $this->returnToParent();
                        break;
                    case $this->nameForm . "_ANADES[DESRUO]_butt":
                        praRic::praRicRuoli($this->nameForm);
                        break;
                    case $this->nameForm . "_ANADES[DESIND]_butt":
                        basRic::basRicVia($this->nameForm);
                        break;
                    case $this->nameForm . "_ANADES[DESCODIND]_butt":
                        basRic::basRicVia($this->nameForm);
                        break;
                    case $this->nameForm . "_ANADES[DESNOM]_butt":
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            proRic::proRicAnamed($this->nameForm, "", 'proAnamed', '2');
                        } else {
                            $whereULoc = "";
                            if ($this->unitaLocale) {
                                $whereULoc = " WHERE DESRUO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'] . "'";
                            }
                            praRic::praRicAnades($this->nameForm, "", $whereULoc);
                        }
                        break;
                    case $this->nameForm . '_CercaAnagrafe':
                        //anaRic::anaRicAnagra($this->nameForm, '', 'DESNOM');
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_DA_ANAGRAFE';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_VediFamiglia':
                        $cf = strtoupper($_POST[$this->nameForm . "_ANADES"]['DESFIS']);
                        if ($cf == "") {
                            Out::msgInfo("Attenzione", "Per visualizzare la famiglia scegliere un codice fiscale");
                            break;
                        }
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['cf'] = $cf;
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_CercaAnagrafeProtocollo':
                        Out::msgInput(
                                'Soggetto', array(
                            'label' => 'Inserisci il codice soggetto    ',
                            'id' => $this->nameForm . '_searchIdSoggetto',
                            'name' => $this->nameForm . '_searchIdSoggetto',
                            'type' => 'text',
                            'size' => '10',
                            'value' => '',
                            'maxchars' => '10'), array(
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaAnagrafeProtocollo', 'model' => $this->nameForm, 'shortCut' => "f5")
                                ), $this->nameForm
                        );
                        break;
                    case $this->nameForm . '_ConfermaAnagrafeProtocollo':
                        include_once ITA_BASE_PATH . '/apps/Protocollo/proIride.class.php';
                        $proIride = new proIride();
                        $paramA = array();
                        $paramA['IdSoggetto'] = $_POST[$this->nameForm . '_searchIdSoggetto'];
                        $ritorno = $proIride->LeggiAnagrafica($paramA);
                        if ($ritorno['Status'] == "0") {
                            $dati = $ritorno['RetValue']['Dati'];
                            Out::valore($this->nameForm . '_ANADES[DESNOM]', $dati['CognomeNome']);
                            if ($dati['PersonaGiuridica'] == true) {
                                Out::valore($this->nameForm . '_ANADES[DESPIVA]', $dati['CodiceFiscale']);
                            } else {
                                Out::valore($this->nameForm . '_ANADES[DESFIS]', $dati['CodiceFiscale']);
                            }
                            Out::valore($this->nameForm . '_ANADES[DESEMA]', $dati['Email']);
                            Out::valore($this->nameForm . '_ANADES[DESIND]', $dati['IndirizzoVia']);
                            Out::valore($this->nameForm . '_ANADES[DESCIT]', $dati['DescrizioneComuneDiResidenza']);
                            Out::valore($this->nameForm . '_ANADES[DESCAP]', $dati['CapComuneDiResidenza']);
                            Out::valore($this->nameForm . '_ANADES[DESTEL]', $dati['TelefonoFax']);
                        }
                        break;
                    case $this->nameForm . '_CercaIPA':
                        $model = 'proRicIPA';
                        itaLib::openForm($model);
                        /* @var $modelObj itaModel */
                        $modelObj = itaModel::getInstance($model);
                        $modelObj->setReturnModel($this->nameForm);
                        $modelObj->setReturnEvent('returnRicIPA');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'PRENDI_DA_ANAGRAFE':
                        $soggetto = $_POST['msgData'];
                        $this->AggiungiDaAnagrafe($soggetto);
                        break;
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANADES[DESCOD]':
                        if ($_POST[$this->nameForm . '_ANADES']["DESCOD"]) {
                            $codice = str_pad($_POST[$this->nameForm . '_ANADES']["DESCOD"], 6, "0", STR_PAD_LEFT);
                            $this->DecodAnades($codice, "codiceSogg");
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESCODIND]':
                        if ($_POST[$this->nameForm . '_ANADES']["DESCODIND"]) {
                            $codice = str_pad($_POST[$this->nameForm . '_ANADES']["DESCODIND"], 6, "0", STR_PAD_LEFT);
                            $this->DecodVia('VIE', "codice", $codice);
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESNOM]':
                        $anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNOM LIKE '%"
                                        . addslashes($_POST[$this->nameForm . '_ANADES']["DESNOM"]) . "%'", false);
                        if (!$anades_rec) {
                            break;
                        }
                        $this->DecodAnades($anades_rec['ROWID'], "rowid");
                        break;
                    case $this->nameForm . '_ANADES[DESRUO]':
                        $ruolo = $_POST[$this->nameForm . '_ANADES']['DESRUO'];
                        if ($ruolo) {
                            $ruolo = str_pad($ruolo, 4, "0", STR_PAD_LEFT);
                            Out::valore($this->nameForm . '_ANADES[DESRUO]', $ruolo);
                            $this->DecodAnaruo($ruolo);
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        $comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE ='"
                                        . addslashes($_POST[$this->nameForm . '_ANADES']['DESCIT']) . "'", false);
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_ANADES[DESCAP]', $comuni_rec['COAVPO']);
                            Out::valore($this->nameForm . '_ANADES[DESPRO]', $comuni_rec['PROVIN']);
                        }
                        break;
                    case $this->nameForm . '_ANADES[DESNASCIT]':
                        $comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE ='"
                                        . addslashes($_POST[$this->nameForm . '_ANADES']['DESNASCIT']) . "'", false);
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_ANADES[DESNASPROV]', $comuni_rec['PROVIN']);
                        }
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANADES[DESNOM]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $whereULoc = "";
                        if ($this->unitaLocale) {
                            $whereULoc = " AND DESRUO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD'] . "'";
                        }

                        $q = itaSuggest::getQuery();
                        $anades_tab = $this->praLib->getGenericTab("SELECT * FROM ANADES WHERE " . $this->praLib->getPRAMDB()->strUpper('DESNOM') . " LIKE '%"
                                . addslashes(strtoupper($q)) . "%' $whereULoc");
                        foreach ($anades_tab as $anades_rec) {

                            itaSuggest::addSuggest($anades_rec['DESNOM'], array(
                                $this->nameForm . '_ANADES[DESNOM]' => $anades_rec['DESNOM'],
                                $this->nameForm . '_ANADES[DESCOD]' => $anades_rec['DESCOD'],
                                $this->nameForm . '_ANADES[DESFIS]' => $anades_rec['DESFIS'],
                                $this->nameForm . '_ANADES[DESIND]' => $anades_rec['DESIND'],
                                $this->nameForm . '_ANADES[DESCIT]' => $anades_rec['DESCIT'],
                                $this->nameForm . '_ANADES[DESPRO]' => $anades_rec['DESPRO'],
                                $this->nameForm . '_ANADES[DESCAP]' => $anades_rec['DESCAP'],
                                $this->nameForm . '_ANADES[DESEMA]' => $anades_rec['DESEMA'],
                            ));
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_ANADES[DESCIT]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $comuni_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($comuni_tab as $comuni_rec) {
                            itaSuggest::addSuggest($comuni_rec['COMUNE']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_ANADES[DESNASCIT]':
                        ob_clean();
                        $comuni_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($comuni_tab as $comuni_rec) {
                            itaSuggest::addSuggest($comuni_rec['COMUNE']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;

            case 'returnAnaruo':
                $this->DecodAnaruo($_POST['retKey'], 'rowid');
                break;
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '2':
                        $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                        break;
                }
                break;
            case 'returnAnades':
                $this->DecodAnades($_POST['retKey'], 'rowid');
                break;
//            case 'returnaAnagraDESNOM':
//                $anaLib = new anaLib();
//                $anagra_rec = $anaLib->GetAnagra($_POST['retKey'], 'rowid');
//                $lavoro_rec = $anaLib->GetLavoro($anagra_rec['CODTRI']);
//                $anindi_rec = $anaLib->GetAnindi($anagra_rec['CODIND']);
//                $anacit_rec = $anaLib->GetAnacit($anagra_rec['CODCIT']);
//                $nome = trim($anagra_rec['NOME']) . trim($anagra_rec['NOME2']) . trim($anagra_rec['NOME3']);
//                $cognome = trim($anagra_rec['COGNOM']) . trim($anagra_rec['COGNO2']) . trim($anagra_rec['COGNO3']);
//                Out::valore($this->nameForm . '_ANADES[DESNOM]', $nome . " " . $cognome);
//                Out::valore($this->nameForm . '_ANADES[DESFIS]', $lavoro_rec['FISCAL']);
//                Out::valore($this->nameForm . '_ANADES[DESCAP]', $anacit_rec['CAP']);
//                Out::valore($this->nameForm . '_ANADES[DESPRO]', $anacit_rec['ITAEST']);
//                Out::valore($this->nameForm . '_ANADES[DESCIT]', $anacit_rec['RESID']);
//                Out::valore($this->nameForm . '_ANADES[DESIND]', trim($anindi_rec['SPECIE']) . ' ' . $anindi_rec['INDIR']);
//                Out::valore($this->nameForm . '_ANADES[DESCIV]', $anagra_rec['CIVICO']);
//                break;
            case 'returnRicIPA':
                //
                //Estraggo il numero civico dall'indirizzo
                //
                $lastSpacePos = strrpos($_POST['PROIND'], " ");
                $civico = substr($_POST['PROIND'], $lastSpacePos + 1);
                $indirizzo = substr($_POST['PROIND'], 0, $lastSpacePos);
                //
                Out::valore($this->nameForm . '_ANADES[DESNOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_ANADES[DESIND]', $indirizzo);
                Out::valore($this->nameForm . '_ANADES[DESCIV]', $civico);
                Out::valore($this->nameForm . '_ANADES[DESCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_ANADES[DESPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_ANADES[DESCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_ANADES[DESPEC]', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_ANADES[DESNASCIT]', "");
                Out::valore($this->nameForm . '_ANADES[DESNASPROV]', "");
                Out::valore($this->nameForm . '_ANADES[DESNASNAZ]', "");
                Out::valore($this->nameForm . '_ANADES[DESNASDAT]', "");
                break;
            case "returnAnavia":
                $this->DecodVia($_POST['retKey'], "rowid");
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_soggetto');
        App::$utente->removeKey($this->nameForm . '_rowid');
        App::$utente->removeKey($this->nameForm . '_unitaLocale');
        App::$utente->removeKey($this->nameForm . '_idAppoggio');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        $_POST = array();
        $_POST['soggetto'] = $this->soggetto;
        $_POST['rowid'] = $this->rowid;
        $objModel = itaModel::getInstance($this->returnModel);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();

//        $_POST = array();
//        $_POST['event'] = $this->returnEvent;
//        $_POST['model'] = $this->returnModel;
//        $_POST['soggetto'] = $this->soggetto;
//        $_POST['rowid'] = $this->rowid;
//        $phpURL = App::getConf('modelBackEnd.php');
//        $appRouteProg = App::getPath('appRoute.' . substr($this->returnModel, 0, 3));
//        include_once $phpURL . '/' . $appRouteProg . '/' . $this->returnModel . '.php';
//        $returnModel = $this->returnModel;
//        $returnModel();
        if ($close)
            $this->close();
    }

    public function Nuovo() {
        Out::show($this->nameForm . '_Aggiungi');
        Out::valore($this->nameForm . '_ANADES[DESNUM]', $_POST['pratica']);
        if ($this->unitaLocale) {
            $this->hideCampiULocale();
            Out::valore($this->nameForm . "_ANADES[DESRUO]", praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']);
            $this->DecodAnaruo(praRuolo::$SISTEM_SUBJECT_ROLES['UNITALOCALE']['RUOCOD']);
            Out::attributo($this->nameForm . "_ANADES[DESRUO]", "readonly", '0');
            Out::hide($this->nameForm . '_ANADES[DESRUO]_butt');
            $proges_rec = $this->praLib->GetProges($_POST['pratica']);
            if ($proges_rec['GESSPA'] != 0) {
                $anaspa_rec = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
                $citta = $anaspa_rec['SPACOM'];
                $provincia = $anaspa_rec['SPAPRO'];
                $cap = $anaspa_rec['SPACAP'];
            } else {
                $anatsp_rec = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
                $citta = $anatsp_rec['TSPCOM'];
                $provincia = $anatsp_rec['TSPPRO'];
                $cap = $anatsp_rec['TSPCAP'];
            }
            Out::valore($this->nameForm . "_ANADES[DESCIT]", $citta);
            Out::valore($this->nameForm . "_ANADES[DESPRO]", $provincia);
            Out::valore($this->nameForm . "_ANADES[DESCAP]", $cap);
        }
    }

    public function Modifica($perms) {
        Out::show($this->nameForm . '_Aggiorna');
        Out::valori($this->soggetto, $this->nameForm . "_ANADES");
        if ($this->soggetto['DESRUO'] == "0001") {
            if ($this->soggetto['DESCMSUSER']) {
                Out::show($this->nameForm . '_ANADES[DESCMSUSER]');
                Out::html($this->nameForm . "_ANADES[DESCMSUSER]", $this->soggetto['DESCMSUSER']);
            }
        }

        /*
         * Se DESNOM è vuoto, lo valorizzo con nome e cognome 
         */
        if (trim($this->soggetto['DESNOM']) == "") {
            Out::valore($this->nameForm . "_ANADES[DESNOM]", $this->soggetto['DESCOGNOME'] . " " . $this->soggetto['DESNOME']);
        }
        $this->DecodAnaruo($this->soggetto['DESRUO']);
        if ($this->unitaLocale) {
            $this->hideCampiULocale();
            Out::attributo($this->nameForm . "_ANADES[DESRUO]", "readonly", '0');
            Out::hide($this->nameForm . '_ANADES[DESRUO]_butt');
        }
        Out::checkDataButton($this->nameForm, $perms);
    }

    public function hideCampiULocale() {
        Out::hide($this->nameForm . '_divSoggetto1_1');
        Out::hide($this->nameForm . '_divSoggetto1_3');
        Out::hide($this->nameForm . '_divSoggetto3');
        Out::show($this->nameForm . '_divSoggetto2_1');
        Out::disableField($this->nameForm . '_ANADES[DESIND]');

//        Out::hide($this->nameForm . '_CercaAnagrafe');
//        Out::hide($this->nameForm . '_VediFamiglia');
//        Out::hide($this->nameForm . '_CercaIPA');
//        Out::hide($this->nameForm . '_headerNascita');
//        Out::hide($this->nameForm . '_ANADES[DESCOD]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNOM]_field');
//        Out::hide($this->nameForm . '_ANADES[DESFIS]_field');
//        Out::hide($this->nameForm . '_ANADES[DESPIVA]_field');
//        Out::hide($this->nameForm . '_ANADES[DESEMA]_field');
//        Out::hide($this->nameForm . '_ANADES[DESPEC]_field');
//        Out::hide($this->nameForm . '_ANADES[DESTEL]_field');
//        Out::hide($this->nameForm . '_ANADES[DESTEL]_field');
//        Out::hide($this->nameForm . '_ANADES[DESFAX]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNASDAT]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNASDAT]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNASCIT]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNASPROV]_field');
//        Out::hide($this->nameForm . '_ANADES[DESNASNAZ]_field');
//        Out::hide($this->nameForm . '_ANADES[DESCMSUSER]_field');
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_ANADES[DESCMSUSER]');
//        include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';
//        $utiEnte = new utiEnte;
//        $PARMENTE_rec = $utiEnte->GetParametriEnte();
//        if ($PARMENTE_rec['TIPOPROTOCOLLO'] == 'Iride') {
//            Out::hide($this->nameForm . '_CercaAnagrafe');
//            Out::hide($this->nameForm . '_VediFamiglia');
//        } else {
        Out::hide($this->nameForm . '_CercaAnagrafeProtocollo');
//        }
    }

    function DecodAnaruo($Codice, $tipoRic = 'codice') {
        $anaruo_rec = $this->praLib->GetAnaruo($Codice, $tipoRic);
        Out::valore($this->nameForm . "_ANADES[DESRUO]", $anaruo_rec['RUOCOD']);
        Out::valore($this->nameForm . "_DESCRUOLO", $anaruo_rec['RUODES']);
        return $anaruo_rec;
    }

    function DecodAnades($Codice, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        App::log($anades_rec);
        $this->DecodAnaruo($anades_rec['DESRUO']);
        Out::valore($this->nameForm . "_ANADES[DESCOD]", $anades_rec['DESCOD']);
        Out::valore($this->nameForm . "_ANADES[DESNOM]", $anades_rec['DESNOM']);
        Out::valore($this->nameForm . "_ANADES[DESRUO]", $anades_rec['DESRUO']);
        Out::valore($this->nameForm . "_ANADES[DESFIS]", $anades_rec['DESFIS']);
        Out::valore($this->nameForm . "_ANADES[DESPIVA]", $anades_rec['DESPIVA']);
        Out::valore($this->nameForm . "_ANADES[DESEMA]", $anades_rec['DESEMA']);
        Out::valore($this->nameForm . "_ANADES[DESPEC]", $anades_rec['DESPEC']);
        Out::valore($this->nameForm . "_ANADES[DESIND]", $anades_rec['DESIND']);
        Out::valore($this->nameForm . "_ANADES[DESCIV]", $anades_rec['DESCIV']);
        Out::valore($this->nameForm . "_ANADES[DESCIT]", $anades_rec['DESCIT']);
        Out::valore($this->nameForm . "_ANADES[DESCAP]", $anades_rec['DESCAP']);
        Out::valore($this->nameForm . "_ANADES[DESPRO]", $anades_rec['DESPRO']);
        Out::valore($this->nameForm . "_ANADES[DESTEL]", $anades_rec['DESTEL']);
        Out::valore($this->nameForm . "_ANADES[DESFAX]", $anades_rec['DESFAX']);
        Out::valore($this->nameForm . '_ANADES[DESNASCIT]', $anades_rec['DESNASCIT']);
        Out::valore($this->nameForm . '_ANADES[DESNASPROV]', $anades_rec['DESNASPROV']);
        Out::valore($this->nameForm . '_ANADES[DESNASNAZ]', $anades_rec['DESNASNAZ']);
        Out::valore($this->nameForm . '_ANADES[DESNASDAT]', $anades_rec['DESNASDAT']);
    }

    function DecodAnamedComP($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            //
            //Estraggo il numero civico dall'indirizzo
            //
            $lastSpacePos = strrpos($anamed_rec['MEDIND'], " ");
            $civico = substr($anamed_rec['MEDIND'], $lastSpacePos + 1);
            $indirizzo = $anamed_rec['MEDIND'];
            if (is_numeric($civico)) {
                $indirizzo = substr($anamed_rec['MEDIND'], 0, $lastSpacePos);
            } else {
                $civico = "";
            }
            Out::valore($this->nameForm . "_ANADES[DESCOD]", $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . "_ANADES[DESNOM]", $anamed_rec['MEDNOM']);
            Out::valore($this->nameForm . "_ANADES[DESFIS]", $anamed_rec['MEDFIS']);
            Out::valore($this->nameForm . "_ANADES[DESEMA]", $anamed_rec['MEDEMA']);
            Out::valore($this->nameForm . "_ANADES[DESIND]", $indirizzo);
            Out::valore($this->nameForm . "_ANADES[DESCIV]", $civico);
            Out::valore($this->nameForm . "_ANADES[DESCIT]", $anamed_rec['MEDCIT']);
            Out::valore($this->nameForm . "_ANADES[DESCAP]", $anamed_rec['MEDCAP']);
            Out::valore($this->nameForm . "_ANADES[DESPRO]", $anamed_rec['MEDPRO']);
            Out::valore($this->nameForm . "_ANADES[DESTEL]", $anamed_rec['MEDTEL']);
            Out::valore($this->nameForm . "_ANADES[DESFAX]", $anamed_rec['MEDFAX']);
            Out::valore($this->nameForm . '_ANADES[DESNASCIT]', "");
            Out::valore($this->nameForm . '_ANADES[DESNASPROV]', "");
            Out::valore($this->nameForm . '_ANADES[DESNASNAZ]', "");
            Out::valore($this->nameForm . '_ANADES[DESNASDAT]', "");
        }
        return $anamed_rec;
    }

    function DecodVia($codice, $tipo = "codice", $anacod = '') {

        if ($tipo == 'rowid') {
            $Via_rec = $this->basLib->GetComana($codice, $tipo);
        } else {
            $Via_rec = $this->basLib->GetComana('VIE', $tipo, $anacod);
        }
        if (!$Via_rec) {
            Out::valore($this->nameForm . '_ANADES[DESCODIND]', '');
            Out::valore($this->nameForm . '_ANADES[DESIND]', '');
            return false;
        }
        Out::valore($this->nameForm . '_ANADES[DESCODIND]', $Via_rec['ANACOD']);
        Out::valore($this->nameForm . '_ANADES[DESIND]', $Via_rec['ANADES']);
        return true;
    }

    function AggiungiDaAnagrafe($soggetto) {
        Out::valore($this->nameForm . '_ANADES[DESCOD]', $soggetto['CODICEUNIVOCO']);
        Out::valore($this->nameForm . '_ANADES[DESNOM]', $soggetto['NOME'] . " " . $soggetto['COGNOME']);
        Out::valore($this->nameForm . '_ANADES[DESFIS]', $soggetto['CODICEFISCALE']);
        Out::valore($this->nameForm . '_ANADES[DESCIT]', $soggetto['RESIDENZA']);
        Out::valore($this->nameForm . '_ANADES[DESPRO]', $soggetto['PROVINCIA']);
        Out::valore($this->nameForm . '_ANADES[DESIND]', $soggetto['INDIRIZZO']);
        Out::valore($this->nameForm . '_ANADES[DESCIV]', $soggetto['CIVICO']);
        Out::valore($this->nameForm . '_ANADES[DESCAP]', $soggetto['CAP']);
        Out::valore($this->nameForm . '_ANADES[DESNASCIT]', $soggetto['LUOGONASCITA']);
        Out::valore($this->nameForm . '_ANADES[DESNASPROV]', $soggetto['PROVINCIANASCITA']);
        Out::valore($this->nameForm . '_ANADES[DESNASNAZ]', $soggetto['CITTADINANZA']);
        Out::valore($this->nameForm . '_ANADES[DESNASDAT]', $soggetto['DATANASCITA']);
    }

    private function checkPrerequisiti() {
        if ($this->unitaLocale) {
            if (!$this->formData[$this->nameForm . '_ANADES']['DESCODIND']) {
                Out::msgStop("Errore", "Codice stradario Obbligatorio,");
                return false;
            }

            if (!$this->DecodVia('VIE', 'codice', $this->formData[$this->nameForm . '_ANADES']['DESCODIND'])) {
                Out::msgStop("Errore", "Codice stradarion non valido");
                return false;
            }
        }

        return true;
    }

}
