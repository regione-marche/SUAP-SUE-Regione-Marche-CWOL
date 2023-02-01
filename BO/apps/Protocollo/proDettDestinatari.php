<?php

/**
 *
 * GESTIONE Destinatari Aggiuntivi
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    28.01.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basRic.class.php';
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';

function proDettDestinatari() {
    $proDettDestinatari = new proDettDestinatari();
    $proDettDestinatari->parseEvent();
    return;
}

class proDettDestinatari extends itaModel {

    public $PROT_DB;
    public $COMUNI_DB;
    public $nameForm;
    public $divDettaglio;
    public $proLib;
    public $basLib;
    public $destGrammi;
    public $destQta;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->nameForm = "proDettDestinatari";
            $this->divDettaglio = $this->nameForm . "_divDettaglio";
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->basLib = new basLib();
            try {
                $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            } catch (Exception $e) {
                App::log($e->getMessage());
            }
            $this->destGrammi = App::$utente->getKey($this->nameForm . '_destGrammi');
            $this->destQta = App::$utente->getKey($this->nameForm . '_destQta');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_destGrammi', $this->destGrammi);
            App::$utente->setKey($this->nameForm . '_destQta', $this->destQta);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::show($this->nameForm);
                $returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnModel = $returnModel;
                Out::valore($this->nameForm . '_returnModel', $returnModel);
                $returnField = $_POST[$this->nameForm . '_returnField'];
                Out::valore($this->nameForm . '_returnField', $returnField);
                $this->divDettaglio = $_POST[$this->nameForm . '_proDettCampi'];

                Out::valore($this->nameForm . '_proDettCampi', $this->divDettaglio);
                Out::valore($this->nameForm . '_proDettTar_tar', '');
                Out::valore($this->nameForm . '_proDettTspdes', '');
                Out::valore($this->nameForm . '_destRowid', $_POST['destRowid']);
                $this->Nascondi();
                //ita-edit-uppercase ***
                $anaent_37 = $this->proLib->GetAnaent('37');
                if ($anaent_37['ENTDE3'] == 1) {
                    Out::addClass($this->nameForm . '_Oggetto', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_destNome', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_destInd', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_destCitta', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_destProv', "ita-edit-uppercase");
                }
                Out::setFocus('', $this->nameForm . '_destCodice');
                if ($_POST[$this->nameForm . '_proDettCampi']) {
                    $this->Modifica();
                } else {
                    $this->Nuovo();
                }
                if ($_POST[$this->nameForm . '_tipoForm'] == 'Richiesta') {
                    Out::hide($this->nameForm . '_divSped');
                }
                $anaent_57 = $this->proLib->GetAnaent('57');
                if ($anaent_57['ENTDE2'] == 1) {
                    Out::addClass($this->nameForm . '_destProtsp', "required");
                }

                switch ($_POST[$this->nameForm . '_permessi']) {
                    case 'tutti':
                        $this->toggleDestinatario('abilita');
                        break;
                    case 'consultazione':
                        $this->toggleDestinatario('disabilita');
                        Out::hide($this->nameForm . '_Aggiorna');
                        break;
                    case 'nessuno':
                        $this->toggleDestinatario('disabilita');
                        if ($_POST[$this->nameForm . '_proDettCampi']['DESIDMAIL'] == '') {
                            $this->toggleDestinatario('disabilita');
                            $this->toggleMailDestinatario('abilita');
                            Out::show($this->nameForm . '_Aggiorna');
                            Out::setFocus('', $this->nameForm . "_destEmail");
                        } else {
                            Out::hide($this->nameForm . '_Aggiorna');
                        }
                        break;
                    case 'indirizzo':
                        $this->toggleDestinatario('disabilita');
                        $this->toggleMailDestinatario('abilita');
                        Out::show($this->nameForm . '_Aggiorna');
                        Out::setFocus('', $this->nameForm . "_destEmail");
                        break;
                }
                if (!$_POST['utilizzaRuoloDestinatari']) {
                    Out::hide($this->nameForm . '_desruo_ext_field');
                    Out::hide($this->nameForm . '_descruolo_ext_field');
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                    case $this->nameForm . '_Aggiungi':
                        if (!$this->ControlloCampi()) {
                            break;
                        }

                        $returnModel = $this->returnModel;
                        $returnField = $_POST[$this->nameForm . '_returnField'];
                        $proDettCampi = array();
                        $proDettCampi['destCodice'] = $_POST[$this->nameForm . '_destCodice'];
                        $proDettCampi['destNome'] = $_POST[$this->nameForm . '_destNome'];
                        $proDettCampi['destInd'] = $_POST[$this->nameForm . '_destInd'];
                        $proDettCampi['destCitta'] = $_POST[$this->nameForm . '_destCitta'];
                        $proDettCampi['destProv'] = $_POST[$this->nameForm . '_destProv'];
                        $proDettCampi['destCap'] = $_POST[$this->nameForm . '_destCap'];
                        $proDettCampi['destAnn'] = $_POST[$this->nameForm . '_destAnn'];
                        $proDettCampi['destDataReg'] = $_POST[$this->nameForm . '_destDataReg'];
                        $proDettCampi['destProtsp'] = $_POST[$this->nameForm . '_destProtsp'];
                        $proDettCampi['destNumRacc'] = $_POST[$this->nameForm . '_destNumRacc'];
                        $proDettCampi['destGrammi'] = $_POST[$this->nameForm . '_destGrammi'];
                        $proDettCampi['destQta'] = $_POST[$this->nameForm . '_destQta'];
                        $proDettCampi['destDataSped'] = $_POST[$this->nameForm . '_destDataSped'];
                        $proDettCampi['destCalcolo'] = $_POST[$this->nameForm . '_destCalcolo'];
                        $proDettCampi['proDettTsp_tar'] = $_POST[$this->nameForm . '_proDettTsp_tar'];
                        $proDettCampi['proDettTsp_pas'] = $_POST[$this->nameForm . '_proDettTsp_pas'];
                        $proDettCampi['destDescSped'] = $_POST[$this->nameForm . '_proDescSped'];
                        $proDettCampi['email'] = $_POST[$this->nameForm . '_destEmail'];
                        $proDettCampi['destRowid'] = $_POST[$this->nameForm . '_destRowid'];
                        $proDettCampi['destFis'] = $_POST[$this->nameForm . '_destFis'];
                        $proDettCampi['desruo_ext'] = $_POST[$this->nameForm . '_desruo_ext'];
                        if ($proDettCampi['desruo_ext']) {
                            $Ana_Ruoli_rec = $this->basLib->getRuolo($proDettCampi['desruo_ext'], 'codice');
                            $proDettCampi['descruolo_ext'] = $Ana_Ruoli_rec['RUODES'];
                        }
                        $_POST = array();
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $returnField;
                        $_POST['proDettCampi'] = $proDettCampi;
//                        App::log($_POST);
//                        $phpURL = App::getConf('modelBackEnd.php');
//                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
//                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
//                        $returnModel();
//                        Out::msgInfo('', print_r($returnModel, true));
//                        return;
                        $returnModelOrig = $returnModel;
                        if (is_array($returnModel)) {
                            $returnModelOrig = $returnModel['nameFormOrig'];
                            $returnModel = $returnModel['nameForm'];
                        }
                        $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
                        $returnObj->setEvent('returntoform');
                        $returnObj->parseEvent();
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_destNome_butt':
                        $descrizione = $_POST[$this->nameForm . '_destNome'];
                        $where = "WHERE MEDNOM LIKE '%$descrizione%'";
                        proRic::proRicAnamed($this->nameForm, $where);
                        break;
                    case $this->nameForm . '_destProtsp_butt':
                        $this->destGrammi = $_POST[$this->nameForm . '_destGrammi'];
                        $this->destQta = $_POST[$this->nameForm . '_destQta'];
                        proRic::proRicAnatsp($this->nameForm);
                        break;
                    case $this->nameForm . '_AggiungiMittente':
                        $mednom = $_POST[$this->nameForm . '_destNome'];
                        $medcit = $_POST[$this->nameForm . '_destCitta'];
                        $medind = $_POST[$this->nameForm . '_destInd'];
                        $medcap = $_POST[$this->nameForm . '_destCap'];
                        $medpro = $_POST[$this->nameForm . '_destProv'];
                        $email = $_POST[$this->nameForm . '_destEmail'];
                        $fisc = $_POST[$this->nameForm . '_destFis'];
                        $risultato = $this->proLib->registraAnamed($mednom, $medcit, $medind, $medcap, $medpro, $email, $fisc);
                        if ($risultato['MEDCOD']) {
                            $this->DecodAnamed($risultato['MEDCOD']);
                        }
                        Out::msgInfo($risultato['titolo'], $risultato['messaggio']);
                        Out::setFocus('', $this->nameForm . "_destNome");
                        break;
                    case $this->nameForm . '_CercaAnagrafe':
//                        $pronom = $_POST[$this->nameForm . '_destNome'];
//                        anaRic::anaRicAnagra($this->nameForm);
//                        Out::valore('gs_COGNOM', $pronom);
//                        Out::setFocus('', "gs_COGNOM");
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_ANAGPRODETT_PRONOM';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
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

                    case $this->nameForm . '_CercaAnaSoggetti':
                        include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
                        proRic::apriRicercaAnagrafeSoggettiUnici('', '', 'retAnaSoggettiUnici', $this->nameForm, $this->nameFormOrig);
                        break;

                    case $this->nameForm . '_CercaAnagPerson':
                        $anaent_58 = $this->proLib->GetAnaent('58');
                        if ($anaent_58['ENTDE6']) {
                            $model = $anaent_58['ENTDE6'];
                            itaLib::openForm($model);
                            /* @var $modelObj itaModel */
                            $modelObj = itaModel::getInstance($model);
                            $ReturnModel = array(
                                'nameForm' => $this->nameForm,
                                'nameFormOrig' => $this->nameFormOrig
                            );
                            $modelObj->setReturnModel($ReturnModel);
                            $modelObj->setReturnEvent('returnAnagPerson');
                            $modelObj->setEvent('openform');
                            $modelObj->parseEvent();
                        }
                        break;

                    case $this->nameForm . "_desruo_ext_butt":
                        basRic::basRicRuoli($this->nameForm);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_destCodice':
                        $codice = $_POST[$this->nameForm . '_destCodice'];
                        if (trim($codice) != "") {
                            $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            $this->DecodAnamed($codice);
//                            Out::valore($this->nameForm.'_destCodice','');
                        }
                        break;
                    case $this->nameForm . '_destCap':
                        if (!is_numeric($_POST[$this->nameForm . '_destCap']))
                            Out::valore($this->nameForm . '_destCap', '');
                        break;
                    case $this->nameForm . '_destCitta':
                        $sql = "SELECT * FROM COMUNI WHERE COMUNE ='" . addslashes($_POST[$this->nameForm . '_destCitta']) . "'";
                        $Comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, false);
                        if ($Comuni_rec) {
                            Out::valore($this->nameForm . '_destProv', $Comuni_rec['PROVIN']);
                            Out::valore($this->nameForm . '_destCap', $Comuni_rec['COAVPO']);
                        }
                        break;
                    case $this->nameForm . '_destProtsp':
                        $codice = $_POST[$this->nameForm . '_destProtsp'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnatsp($codice);
                            $this->CalcolaSpesa($codice, $_POST[$this->nameForm . '_destGrammi'], $_POST[$this->nameForm . '_destQta']);
                        } else {
                            Out::valore($this->nameForm . '_destDescSped', "");
                        }
                        break;
                    case $this->nameForm . '_destGrammi':
                    case $this->nameForm . '_destQta':
                        $codice = $_POST[$this->nameForm . '_destProtsp'];
                        if (trim($codice) != "") {
                            $this->CalcolaSpesa($_POST[$this->nameForm . '_destProtsp'], $_POST[$this->nameForm . '_destGrammi'], $_POST[$this->nameForm . '_destQta']
                            );
                        }
                        break;


                    case $this->nameForm . '_desruo_ext':
                        $codice = $_POST[$this->nameForm . '_desruo_ext'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                                $Ana_Ruoli_rec = $this->basLib->getRuolo($codice, 'codice');
                                Out::valore($this->nameForm . '_desruo_ext', $Ana_Ruoli_rec['RUOCOD']);
                                Out::valore($this->nameForm . '_descruolo_ext', $Ana_Ruoli_rec['RUODES']);
                            } else {
                                Out::valore($this->nameForm . '_desruo_ext', '');
                                Out::valore($this->nameForm . '_descruolo_ext', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_desruo_ext', '');
                            Out::valore($this->nameForm . '_descruolo_ext', '');
                        }
                        break;
                }
                break;

            case 'returnRicIPA':
                Out::valore($this->nameForm . '_destNome', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_destInd', $_POST['PROIND']);
                Out::valore($this->nameForm . '_destCitta', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_destProv', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_destCap', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_destEmail', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_destFis', $_POST['PROFIS']);
                break;

            case 'returnanamed':
                $this->DecodAnamed($_POST['retKey'], 'rowid');
                break;
            case 'returnanatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                $this->CalcolaSpesa($_POST['retKey'], $this->destGrammi, $this->destQta, 'rowid');
                break;
            case 'returnaAnagra':
//                $anaLib = new anaLib();
//                $anagra_rec = $anaLib->GetAnagra($_POST['retKey'], 'rowid');
//                $anindi_rec = $anaLib->GetAnindi($anagra_rec['CODIND']);
//                $anacit_rec = $anaLib->GetAnacit($anagra_rec['CODCIT']);
//                $nome = trim($anagra_rec['NOME']) . trim($anagra_rec['NOME2']) . trim($anagra_rec['NOME3']);
//                $cognome = trim($anagra_rec['COGNOM']) . trim($anagra_rec['COGNO2']) . trim($anagra_rec['COGNO3']);
//                Out::valore($this->nameForm . '_destNome', $cognome . " " . $nome);
//                Out::valore($this->nameForm . '_destInd', trim($anindi_rec['SPECIE']) . ' ' . $anindi_rec['INDIR'] . " " . $anagra_rec['CIVICO']);
//                Out::valore($this->nameForm . '_destCitta', $anacit_rec['RESID']);
//                Out::valore($this->nameForm . '_destProv', $anacit_rec['ITAEST']);
//                Out::valore($this->nameForm . '_destCap', $anacit_rec['CAP']);
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_destNome':
                        $this->suggestAnamed();
                        break;
                    case $this->nameForm . '_destCitta':
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM COMUNI WHERE " . $where;
                        $Comuni_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, $sql, true);
                        if (count($Comuni_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($Comuni_tab as $Comuni_rec) {
                                itaSuggest::addSuggest($Comuni_rec['COMUNE']);
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'PRENDI_ANAGPRODETT_PRONOM':
                        $soggetto = $_POST['msgData'];
                        $this->DecodAnagrafe($soggetto);
                        break;
                }
                break;

            case 'retAnaSoggettiUnici':
                $DatiSogg = $this->formData['returnData'];
                $DatiResSogg = $this->proLib->GetDatiResidenzaSoggettoUnico($DatiSogg['PROGSOGG']);
                if ($DatiSogg['RAGSOC']) {
                    Out::valore($this->nameForm . '_destNome', $DatiSogg['RAGSOC']);
                } else {
                    Out::valore($this->nameForm . '_destNome', $DatiSogg['COGNOME'] . ' ' . $DatiSogg['NOME']);
                }
                Out::valore($this->nameForm . '_destFis', $DatiSogg['CODFISCALE']);
                Out::valore($this->nameForm . '_destInd', $DatiResSogg['DESVIA'] . ' ' . $DatiResSogg['NUMCIV']);
                Out::valore($this->nameForm . '_destCitta', $DatiResSogg['DESLOCAL']);
                Out::valore($this->nameForm . '_destProv', $DatiResSogg['PROVINCIA']);
                Out::valore($this->nameForm . '_destCap', $DatiResSogg['CAP']);
                Out::valore($this->nameForm . '_destEmail', $DatiResSogg['E_MAIL']);
                break;

            case 'returnAnagPerson':
                Out::valore($this->nameForm . '_destNome', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_destInd', $_POST['PROIND']);
                Out::valore($this->nameForm . '_destCitta', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_destProv', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_destCap', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_destEmail', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_destFis', $_POST['PROFIS']);
                break;

            case 'returnAnaruo':
                $Ana_Ruoli_rec = $this->basLib->getRuolo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_desruo_ext', $Ana_Ruoli_rec['RUOCOD']);
                Out::valore($this->nameForm . '_descruolo_ext', $Ana_Ruoli_rec['RUODES']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_destGrammi');
        App::$utente->removeKey($this->nameForm . '_destQta');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function Nuovo() {
        Out::show($this->nameForm . '_Aggiungi');
    }

    public function Modifica() {

        Out::show($this->nameForm . '_Aggiorna');
        Out::valore($this->nameForm . '_destCodice', $this->divDettaglio['DESCOD']);
        Out::valore($this->nameForm . '_destNome', $this->divDettaglio['DESNOM']);
        Out::valore($this->nameForm . '_destInd', $this->divDettaglio['DESIND']);
        Out::valore($this->nameForm . '_destCitta', $this->divDettaglio['DESCIT']);
        Out::valore($this->nameForm . '_destProv', $this->divDettaglio['DESPRO']);
        Out::valore($this->nameForm . '_destCap', $this->divDettaglio['DESCAP']);
        Out::valore($this->nameForm . '_destAnn', $this->divDettaglio['DESANN']);
        Out::valore($this->nameForm . '_destEmail', $this->divDettaglio['DESMAIL']);
        Out::valore($this->nameForm . '_destDataReg', $this->divDettaglio['DESDAT']);
        Out::valore($this->nameForm . '_destFis', $this->divDettaglio['DESFIS']);
        // Decodifico DESTSP se presente:
        Out::valore($this->nameForm . '_destProtsp', $this->divDettaglio['DESTSP']);
        if ($this->divDettaglio['DESTSP']) {
            $this->DecodAnatsp($this->divDettaglio['DESTSP'], 'codice');
        }
//        Out::valore($this->nameForm . '_destProtsp', $this->divDettaglio['PROTSP']);
//        Out::valore($this->nameForm . '_destDescSped', $this->divDettaglio['PRODER']);
        Out::valore($this->nameForm . '_destNumRacc', $this->divDettaglio['DESNRAC']);
        Out::valore($this->nameForm . '_destGrammi', $this->divDettaglio['PROGRA']);
        Out::valore($this->nameForm . '_destQta', $this->divDettaglio['PROQTA']);
        Out::valore($this->nameForm . '_destDataSped', $this->divDettaglio['PRODRA']);
        Out::valore($this->nameForm . '_proDettTsp_tar', $this->divDettaglio['PROIRA']);
        Out::valore($this->nameForm . '_proDettTsp_pas', $this->divDettaglio['PROPES']);
        $this->CalcolaSpesa($this->divDettaglio['PROTSP'], $this->divDettaglio['PROGRA'], $this->divDettaglio['PROQTA']);
        // Decod Ruolo:
        $Ana_Ruoli_rec = $this->basLib->getRuolo($this->divDettaglio['DESRUO_EXT'], 'codice');
        if ($Ana_Ruoli_rec) {
            Out::valore($this->nameForm . '_desruo_ext', $Ana_Ruoli_rec['RUOCOD']);
            Out::valore($this->nameForm . '_descruolo_ext', $Ana_Ruoli_rec['RUODES']);
        }
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_divSped');
        Out::hide($this->nameForm . '_destDataReg');
        Out::hide($this->nameForm . '_destDataReg_lbl');
        Out::hide($this->nameForm . '_destDataReg_datepickertrigger');
        Out::hide($this->nameForm . '_destNumRacc');
        Out::hide($this->nameForm . '_destNumRacc_lbl');
        $anaent_31 = $this->proLib->GetAnaent('31');
        if ($anaent_31['ENTDE4'] == '1') {
            Out::show($this->nameForm . '_CercaAnagrafe');
        } else {
            Out::hide($this->nameForm . '_CercaAnagrafe');
        }
        if ($this->proLib->CheckAbilitaAnaSoggettiUnici()) {
            Out::show($this->nameForm . '_CercaAnaSoggetti');
        } else {
            Out::hide($this->nameForm . '_CercaAnaSoggetti');
        }
        $menLib = new menLib();
        $gruppi = $menLib->getGruppi(App::$utente->getKey('idUtente'));
        $fl1 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGVIS", $menLib->defaultVis);
        $fl2 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGACC", $menLib->defaultAcc);
        $fl3 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGEDT", $menLib->defaultMod);
        $fl4 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGINS", $menLib->defaultIns);
        if ($fl1 && $fl2 && $fl3 && $fl4) {
            Out::show($this->nameForm . '_AggiungiMittente');
        } else {
            Out::hide($this->nameForm . '_AggiungiMittente');
        }
        $anaent_58 = $this->proLib->GetAnaent('58');
        if ($anaent_58['ENTDE6']) {
            Out::show($this->nameForm . '_CercaAnagPerson');
        } else {
            Out::hide($this->nameForm . '_CercaAnagPerson');
        }
    }

    function DecodAnamed($Codice, $_tipoRic = 'codice', $_tutti = 'si') {
        $Anamed_rec = $this->proLib->GetAnamed($Codice, $_tipoRic, $_tutti);
        Out::valore($this->nameForm . '_destCodice', $Anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_destNome', $Anamed_rec['MEDNOM']);
        Out::valore($this->nameForm . '_destInd', $Anamed_rec['MEDIND']);
        Out::valore($this->nameForm . '_destCitta', $Anamed_rec['MEDCIT']);
        Out::valore($this->nameForm . '_destProv', $Anamed_rec['MEDPRO']);
        Out::valore($this->nameForm . '_destCap', $Anamed_rec['MEDCAP']);
        Out::valore($this->nameForm . '_destEmail', $Anamed_rec['MEDEMA']);
        Out::valore($this->nameForm . '_destFis', $Anamed_rec['MEDFIS']);
        return $Anamed_rec;
    }

    function DecodAnatsp($codice, $Tipo = 'codice') {
        $Anatsp_rec = $this->proLib->GetAnatsp($codice, $Tipo);

        if ($Anatsp_rec['TSPGRACC'] == 1) {

            Out::show($this->nameForm . '_destDataReg');
            Out::show($this->nameForm . '_destDataReg_lbl');
            Out::show($this->nameForm . '_destDataReg_datepickertrigger');
            Out::show($this->nameForm . '_destNumRacc');
            Out::show($this->nameForm . '_destNumRacc_lbl');
        } else {
            Out::hide($this->nameForm . '_destDataReg');
            Out::hide($this->nameForm . '_destDataReg_lbl');
            Out::hide($this->nameForm . '_destDataReg_datepickertrigger');
            Out::hide($this->nameForm . '_destNumRacc');
            Out::hide($this->nameForm . '_destNumRacc_lbl');
        }
        Out::valore($this->nameForm . '_destProtsp', $Anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_destDescSped', $Anatsp_rec['TSPDES']);
        Out::valore($this->nameForm . '_proDescSped', $Anatsp_rec['TSPDES']);

        // Stampa analogica e attivi parametro spedizione obbligatoria:
        if ($Anatsp_rec['TSPTIPO'] == "") {
            $anaent_57 = $this->proLib->GetAnaent('57');
            if ($anaent_57['ENTDE2'] == 1 && $this->tipoProt != 'C') {
                Out::addClass($this->nameForm . '_destCap', "required");
            }
        } else {
            Out::delClass($this->nameForm . '_destCap', "required");
        }


        return $Anatsp_rec;
    }

    function CalcolaSpesa($codTsp, $Progra, $destQta, $tipo = 'codice') {
        $Anatsp_rec = $this->proLib->GetAnatsp($codTsp, $tipo);
        if ($Progra > 0 && $Anatsp_rec) {
            for ($i = 0; $i < 30; $i++) {
                if ($Progra <= $Anatsp_rec['TSPPES__' . $i]) {
                    $Tsp_gra = $Anatsp_rec['TSPPES__' . $i];
                    $Tsp_tar = $Anatsp_rec['TSPTAR__' . $i];
                    break;
                }
            }
            $destCalcolo = $Anatsp_rec['TSPDES'] . " gr $Tsp_gra Tar $Tsp_tar = " . $destQta * $Tsp_tar;
            Out::valore($this->nameForm . '_destCalcolo', $destCalcolo);
            Out::valore($this->nameForm . '_proDettTsp_tar', $Tsp_tar);
            Out::valore($this->nameForm . '_proDettTsp_pas', $Tsp_gra);
        }
    }

    public function toggleMailDestinatario($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
        Out::$classMethod($this->nameForm . '_destEmail', "ita-readonly");
        Out::attributo($this->nameForm . '_destEmail', "readonly", $attrCmd);
    }

    public function toggleDestinatario($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }

        //
        //
        Out::$classMethod($this->nameForm . '_destCodice', "ita-readonly");
        Out::attributo($this->nameForm . '_destCodice', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destNome', "ita-readonly");
        Out::attributo($this->nameForm . '_destNome', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_destNome_butt');
        Out::$hideShow($this->nameForm . '_CercaAnagrafe');
        Out::$hideShow($this->nameForm . '_CercaAnagPerson');
        Out::$hideShow($this->nameForm . '_CercaAnaSoggetti');
        Out::$hideShow($this->nameForm . '_CercaIPA');
        Out::$hideShow($this->nameForm . '_AggiungiMittente');

        Out::$classMethod($this->nameForm . '_destInd', "ita-readonly");
        Out::attributo($this->nameForm . '_destInd', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destCitta', "ita-readonly");
        Out::attributo($this->nameForm . '_destCitta', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destProv', "ita-readonly");
        Out::attributo($this->nameForm . '_destProv', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destCap', "ita-readonly");
        Out::attributo($this->nameForm . '_destCap', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destEmail', "ita-readonly");
        Out::attributo($this->nameForm . '_destEmail', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_destFis', "ita-readonly");
        Out::attributo($this->nameForm . '_destFis', "readonly", $attrCmd);
        //
    //
    }

    public function DecodAnagrafe($soggetto) {
        $nome = $soggetto['NOME'];
        $cognome = $soggetto['COGNOME'];
        Out::valore($this->nameForm . '_destNome', $cognome . " " . $nome);
        Out::valore($this->nameForm . '_destInd', $soggetto['INDIRIZZO'] . ' ' . $soggetto['CIVICO']);
        Out::valore($this->nameForm . '_destCitta', $soggetto['RESIDENZA']);
        Out::valore($this->nameForm . '_destProv', $soggetto['PROVINCIA']);
        Out::valore($this->nameForm . '_destCap', $soggetto['CAP']);
    }

    private function suggestAnamed() {
        // VECCHIA
//        $q = itaSuggest::getQuery();
//        itaSuggest::setNotFoundMessage('Nessun risultato.');
//        $parole = explode(' ', $q);
//        foreach ($parole as $k => $parola) {
//            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
//        }
//        $where = implode(" AND ", $parole);
//        $sql = "SELECT * FROM ANAMED WHERE MEDANN=0 AND " . $where;
//        $anamed_tab = $this->proLib->getGenericTab($sql);
//        if (count($anamed_tab) > 100) {
//            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
//        } else {
//            foreach ($anamed_tab as $anamed_rec) {
//                itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_destCodice" => $anamed_rec['MEDCOD']));
//            }
//        }
//        itaSuggest::sendSuggest();

        /*
         * Nuova funzione di ricerca con indirizzo suggerito.
         */
        $q = itaSuggest::getQuery();
        itaSuggest::setNotFoundMessage('Nessun risultato.');

        $parole = explode(' ', $q);
        foreach ($parole as $k => $parola) {
            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE " . $this->PROT_DB->strUpper("'%" . addslashes($parola) . "%'") . "";
        }
        $where = implode(" AND ", $parole);
        $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE " . $where . "  AND MEDANN=0 ORDER BY MEDNOM");

        if (count($anamed_tab) > 100) {
            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
        } else {
            foreach ($anamed_tab as $anamed_rec) {
                $indirizzo = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCIT'] . " " . $anamed_rec['MEDPRO'];
                if (trim($indirizzo) != '') {
                    $indirizzo = " - " . $indirizzo;
                } else {
                    $indirizzo = '';
                }
                $indirizzo = $indirizzo . " - " . $anamed_rec['MEDEMA'];


                itaSuggest::addSuggest($anamed_rec['MEDNOM'] . $indirizzo, array($this->nameForm . "_destCodice" => $anamed_rec['MEDCOD']));
            }
        }
        itaSuggest::sendSuggest();
    }

    public function ControlloCampi() {
        //destNome
        if (!$_POST[$this->nameForm . '_destNome']) {
            Out::msgStop("Attenzione", "Nominativo obbligatorio, compilarlo prima di procedere.");
            Out::setFocus('', $this->nameForm . '_destNome');
            return false;
        }
        return true;
    }

}

?>
