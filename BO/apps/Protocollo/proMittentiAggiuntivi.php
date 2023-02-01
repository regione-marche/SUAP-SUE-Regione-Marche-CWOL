<?php

/**
 *
 * Relazioni Procedimenti - Titolario
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    29.01.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';

//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
//include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';

function proMittentiAggiuntivi() {
    $proMittentiAggiuntivi = new proMittentiAggiuntivi();
    $proMittentiAggiuntivi->parseEvent();
    return;
}

class proMittentiAggiuntivi extends itaModel {

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proMittentiAggiuntivi";
    public $divDettaglio = "proMittentiAggiuntivi_divDettaglio";
    public $gridMitAgg = "proMittentiAggiuntivi_gridMitAgg";
    public $mittentiAggiuntivi;
    public $returnModel;
    public $returnEvent;
    public $elemento;
    public $tipoProt;
    public $titoloForm = '';

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->mittentiAggiuntivi = App::$utente->getKey($this->nameForm . "_mittentiAggiuntivi");
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->elemento = App::$utente->getKey($this->nameForm . "_elemento");
            $this->tipoProt = App::$utente->getKey($this->nameForm . "_tipoProt");
            $this->titoloForm = App::$utente->getKey($this->nameForm . "_titoloForm");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_mittentiAggiuntivi", $this->mittentiAggiuntivi);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_elemento", $this->elemento);
            App::$utente->setKey($this->nameForm . "_tipoProt", $this->tipoProt);
            App::$utente->setKey($this->nameForm . "_titoloForm", $this->titoloForm);
        }
    }

    public function setTitoloForm($titoloForm) {
        $this->titoloForm = $titoloForm;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                if (!isset($_POST['tipoProt'])) {
                    $this->tipoProt = "A ";
                } else {
                    $this->tipoProt = $_POST['tipoProt'];
                }

                $this->mittentiAggiuntivi = array();
                foreach ($_POST['mittentiAggiuntivi'] as $value) {
                    $this->mittentiAggiuntivi[] = $value;
                }
                $returnModel = $_POST[$this->nameForm . '_returnModel'];
                $this->returnModel = $returnModel;
                $this->returnEvent = $_POST['returnEvent'];

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
                //ita-edit-uppercase ***
                $anaent_37 = $this->proLib->GetAnaent('37');
                if ($anaent_37['ENTDE3'] == 1) {
                    Out::addClass($this->nameForm . '_PROMITAGG[PRONOM]', "ita-edit-uppercase");
                }

                $anaent_58 = $this->proLib->GetAnaent('58');
                if ($anaent_58['ENTDE6']) {
                    Out::show($this->nameForm . '_CercaAnagPerson');
                } else {
                    Out::hide($this->nameForm . '_CercaAnagPerson');
                }

                $menLib = new menLib();
                $gruppi = $menLib->getGruppi(App::$utente->getKey('idUtente'));
//                $fl1 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGVIS", '');
//                $fl2 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGACC", '');
//                $fl3 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGEDT", '');
//                $fl4 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGINS", '');
                $fl1 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGVIS", $menLib->defaultVis);
                $fl2 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGACC", $menLib->defaultAcc);
                $fl3 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGEDT", $menLib->defaultMod);
                $fl4 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGINS", $menLib->defaultIns);
                if ($fl1 && $fl2 && $fl3 && $fl4) {
                    Out::show($this->nameForm . '_AggiungiMittente');
                } else {
                    Out::hide($this->nameForm . '_AggiungiMittente');
                }
                switch ($this->tipoProt) {
                    case "A":
                        Out::hide($this->nameForm . '_UFFNOM_field');
                        break;
                    default:
                        Out::attributo($this->nameForm . '_PROMITAGG[PROIND]', 'readonly', '0', 'readonly');
                        Out::attributo($this->nameForm . '_PROMITAGG[PROCIT]', 'readonly', '0', 'readonly');
                        Out::attributo($this->nameForm . '_PROMITAGG[PROPRO]', 'readonly', '0', 'readonly');
                        Out::attributo($this->nameForm . '_PROMITAGG[PROCAP]', 'readonly', '0', 'readonly');
                        Out::attributo($this->nameForm . '_PROMITAGG[PROMAIL]', 'readonly', '0', 'readonly');
                        Out::attributo($this->nameForm . '_PROMITAGG[PROFIS]', 'readonly', '0', 'readonly');
                        Out::show($this->nameForm . '_UFFNOM_field');
                        Out::hide($this->nameForm . '_CercaAnagrafe');
                        Out::hide($this->nameForm . '_CercaAnaSoggetti');
                        Out::hide($this->nameForm . '_CercaIPA');
                        Out::hide($this->nameForm . '_AggiungiMittente');
                        Out::hide($this->nameForm . '_divInd');
                        Out::hide($this->nameForm . '_CercaAnagPerson');
                        break;
                }
                $this->caricaMittentiAggiuntivi();
                if ($_POST['consultazione']) {
                    Out::hide($this->divDettaglio);
                    Out::hide($this->gridMitAgg . '_addGridRow');
                    Out::hide($this->gridMitAgg . '_editGridRow');
                    Out::hide($this->gridMitAgg . '_delGridRow');
                }
                /*
                 * App Title
                 */
                if ($this->titoloForm) {
                    Out::setAppTitle($this->nameForm, $this->titoloForm);
                }
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridMitAgg:
                        $this->elemento = $_POST['rowid'] - 1;
                        $this->Dettaglio();
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridMitAgg:
                        $this->caricaMittentiAggiuntivi();
                        break;
                }
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridMitAgg:
                        unset($this->mittentiAggiuntivi[$_POST['rowid'] - 1]);
                        $this->caricaMittentiAggiuntivi();
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridMitAgg:
                        $riga = $_POST['rowid'] - 1;
                        switch ($_POST['colName']) {
                            case 'MAILMIT':
                                if (array_key_exists($riga, $this->mittentiAggiuntivi)) {
                                    if (!$this->mittentiAggiuntivi[$riga]['PROIDMAILDEST']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $this->mittentiAggiuntivi[$riga]['PROIDMAILDEST'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'ACCMIT':
                                if (array_key_exists($riga, $this->mittentiAggiuntivi)) {
                                    if ($this->mittentiAggiuntivi[$riga]['PROIDMAILDEST'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRic($this->mittentiAggiuntivi[$riga]['PROIDMAILDEST']);
                                    if (!$retRic['ACCETTAZIONE']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $retRic['ACCETTAZIONE'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'CONSMIT':
                                if (array_key_exists($riga, $this->mittentiAggiuntivi)) {
                                    if ($this->mittentiAggiuntivi[$riga]['PROIDMAILDEST'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRicMulti($this->mittentiAggiuntivi[$riga]['PROIDMAILDEST']);
                                    if (!$retRic['CONSEGNA']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $retRic['CONSEGNA'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Carica':
                        if (($this->tipoProt == "P" || $this->tipoProt == "C") && !$_POST[$this->nameForm . '_CODICE']) {
                            Out::msgStop("Attenzione", "Codice Mittente/Firmatario. Obbligatorio");
                            break;
                        }
                        if ($this->tipoProt == "P" || $this->tipoProt == "C") {
                            $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($_POST[$this->nameForm . '_CODICE']);
                            $ok_ruolo = false;
                            foreach ($ruoli as $ruolo) {
                                if ($ruolo['CODICEUFFICIO'] == $_POST[$this->nameForm . '_PROMITAGG']['PRODESUFF']) {
                                    $ok_ruolo = true;
                                    break;
                                }
                            }
                            if (!$ok_ruolo) {
                                Out::msgStop("Inserimento Mittente.", "Il firmatario non appartiene all'ufficio selezionato. Controllare.");
                                Out::setFocus('', $this->nameForm . '_CODICE');
                                break;
                            }
                        }

                        if ($_POST[$this->nameForm . '_PROMITAGG']['PRONOM'] == '') {
                            break;
                        }
                        if ($this->elemento >= 0) {
                            $this->mittentiAggiuntivi[$this->elemento]['PRODESCOD'] = $_POST[$this->nameForm . '_CODICE'];
                            $this->mittentiAggiuntivi[$this->elemento]['PRODESUFF'] = $_POST[$this->nameForm . '_PROMITAGG']['PRODESUFF'];
                            $this->mittentiAggiuntivi[$this->elemento]['PRONOM'] = $_POST[$this->nameForm . '_PROMITAGG']['PRONOM'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROIND'] = $_POST[$this->nameForm . '_PROMITAGG']['PROIND'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROCIT'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCIT'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROPRO'] = $_POST[$this->nameForm . '_PROMITAGG']['PROPRO'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROCAP'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCAP'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROMAIL'] = $_POST[$this->nameForm . '_PROMITAGG']['PROMAIL'];
                            $this->mittentiAggiuntivi[$this->elemento]['PROFIS'] = $_POST[$this->nameForm . '_PROMITAGG']['PROFIS'];
                        } else {
                            $mittente['PRODESCOD'] = $_POST[$this->nameForm . '_CODICE'];
                            $mittente['PRODESUFF'] = $_POST[$this->nameForm . '_PROMITAGG']['PRODESUFF'];
                            $mittente['PRONOM'] = $_POST[$this->nameForm . '_PROMITAGG']['PRONOM'];
                            $mittente['PROIND'] = $_POST[$this->nameForm . '_PROMITAGG']['PROIND'];
                            $mittente['PROCIT'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCIT'];
                            $mittente['PROPRO'] = $_POST[$this->nameForm . '_PROMITAGG']['PROPRO'];
                            $mittente['PROCAP'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCAP'];
                            $mittente['PROMAIL'] = $_POST[$this->nameForm . '_PROMITAGG']['PROMAIL'];
                            $mittente['PROFIS'] = $_POST[$this->nameForm . '_PROMITAGG']['PROFIS'];
                            $this->mittentiAggiuntivi[count($this->mittentiAggiuntivi)] = $mittente;
                        }
                        $this->caricaMittentiAggiuntivi();
                        break;
                    case $this->nameForm . '_PROMITAGG[PRONOM]_butt':
                        if ($this->tipoProt == "A") {
                            $filtroUff = '';
                        } else {
                            $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        }
                        proRic::proRicAnamed($this->nameForm, $filtroUff, 'proAnamed');
                        break;
                    case $this->nameForm . '_AggiungiMittente':
                        $nome = $_POST[$this->nameForm . '_PROMITAGG']['PRONOM'];
                        $citta = $_POST[$this->nameForm . '_PROMITAGG']['PROCIT'];
                        $indirizzo = $_POST[$this->nameForm . '_PROMITAGG']['PROIND'];
                        $anamed_check = $this->proLib->getGenericTab("SELECT * FROM ANAMED
                            WHERE " . $this->PROT_DB->strUpper('MEDNOM') . " ='" . addslashes(strtoupper(trim($nome))) . "' AND
                                  " . $this->PROT_DB->strUpper('MEDCIT') . " ='" . addslashes(strtoupper(trim($citta))) . "' AND
                                  " . $this->PROT_DB->strUpper('MEDIND') . " ='" . addslashes(strtoupper(trim($indirizzo))) . "'");

                        if ($anamed_check) {
                            Out::msgStop("Attenzione!", "Il nominativo è già presente nell'archivio. Non è possibile reinserirlo!");
                        } else {
                            $this->registraAnamed();
                        }
                        break;
                    case $this->nameForm . '_CercaAnagrafe':
//                        $pronom = $_POST[$this->nameForm . '_PROMITAGG']['PRONOM'];
//                        anaRic::anaRicAnagra($this->nameForm, '', '', 'PRONOM');
//                        Out::valore('gs_COGNOM', $pronom);
//                        Out::setFocus('', "gs_COGNOM");
                        $_POST = array();
                        $model = 'utiVediAnel';
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_ANAGPROMITT_PRONOM';
                        itaLib::openDialog($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_UFFNOM_butt':
                        $codice = $_POST[$this->nameForm . '_CODICE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if ($anamed_rec) {
                                proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                            }
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

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODICE':
                        Out::valore($this->nameForm . '_PROMITAGG[PRODESUFF]', '');
                        Out::valore($this->nameForm . '_UFFNOM', '');
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_CODICE':
                        $codice = $_POST[$this->nameForm . '_CODICE'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            if (substr($this->tipoProt, 0, 1) == "P" || substr($this->tipoProt, 0, 1) == "C") {
                                $this->DecodAnamed($codice, 'codice', 'no');
                                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                                if (count($uffdes_tab) == 1) {
                                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                    Out::valore($this->nameForm . '_PROMITAGG[PRODESUFF]', $anauff_rec['UFFCOD']);
                                    Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                                } else {
                                    if ($_POST[$this->nameForm . '_PROMITAGG']['PRODESUFF'] == '' || $_POST[$this->nameForm . '_UFFNOM'] == '') {
                                        proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', 'Firmatario');
                                        Out::setFocus('', "utiRicDiag_gridRis");
                                        break;
                                    }
                                }
                            } else {
                                $this->DecodAnamed($codice);
                            }
                        }
                        Out::setFocus('', $this->nameForm . "_PROMITAGG[PRONOM]");
                        break;
                    case $this->nameForm . '_PROMITAGG[PROCIT]':
                        $comuni_rec = $this->proLib->getGenericTab("SELECT * FROM COMUNI WHERE " . $this->PROT_DB->strUpper('COMUNE') . " ='"
                                . addslashes(strtoupper($_POST[$this->nameForm . '_PROMITAGG']['PROCIT'])) . "'", false, 'COMUNI');
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $comuni_rec['PROVIN']);
                            Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $comuni_rec['COAVPO']);
                        }
                        break;
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PROMITAGG[PRONOM]':
                        $this->suggestAnamed();
                        break;
                    case $this->nameForm . '_PROMITAGG[PROCIT]':
                        /* new suggest */
                        $COMUNI_DB = $this->proLib->getCOMUNIDB();
                        $q = itaSuggest::getQuery();
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $parole = explode(' ', $q);
                        foreach ($parole as $k => $parola) {
                            $parole[$k] = $COMUNI_DB->strUpper('COMUNE') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                        }
                        $where = implode(" AND ", $parole);
                        $sql = "SELECT * FROM COMUNI WHERE " . $where;
                        $comuni_tab = $this->proLib->getGenericTab($sql, true, 'COMUNE');
                        if (count($comuni_tab) > 100) {
                            itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                        } else {
                            foreach ($comuni_tab as $comuni_rec) {
                                itaSuggest::addSuggest($comuni_rec['COMUNE']);
                            }
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnanamed':
                $this->DecodAnamed($_POST['retKey'], 'rowid');
                break;
//            case 'returnaAnagra':
////                $anaLib = new anaLib();
////                $anagra_rec = $anaLib->GetAnagra($_POST['retKey'], 'rowid');
////                $anindi_rec = $anaLib->GetAnindi($anagra_rec['CODIND']);
////                $anacit_rec = $anaLib->GetAnacit($anagra_rec['CODCIT']);
////                $nome = trim($anagra_rec['NOME']) . trim($anagra_rec['NOME2']) . trim($anagra_rec['NOME3']);
////                $cognome = trim($anagra_rec['COGNOM']) . trim($anagra_rec['COGNO2']) . trim($anagra_rec['COGNO3']);
////                Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $cognome . " " . $nome);
////                Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $anacit_rec['CAP']);
////                Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $anacit_rec['ITAEST']);
////                Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $anacit_rec['RESID']);
////                Out::valore($this->nameForm . '_PROMITAGG[PROIND]', trim($anindi_rec['SPECIE']) . ' ' . $anindi_rec['INDIR'] . " " . $anagra_rec['CIVICO']);
//                break;
            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_PROMITAGG[PRODESUFF]', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_PROMITAGG[PRONOM]");
                break;
            case 'returnRicIPA':
                Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_PROMITAGG[PROIND]', $_POST['PROIND']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_PROMITAGG[PROMAIL]', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_PROMITAGG[PROFIS]', $_POST['PROFIS']);
                break;
            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'PRENDI_ANAGPROMITT_PRONOM':
                        $soggetto = $_POST['msgData'];
                        $this->DecodAnagrafe($soggetto);
                        break;
                }
                break;


            case 'retAnaSoggettiUnici':
                $DatiSogg = $this->formData['returnData'];
                $DatiResSogg = $this->proLib->GetDatiResidenzaSoggettoUnico($DatiSogg['PROGSOGG']);
                if ($DatiSogg['RAGSOC']) {
                    Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $DatiSogg['RAGSOC']);
                } else {
                    Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $DatiSogg['COGNOME'] . ' ' . $DatiSogg['NOME']);
                }
                Out::valore($this->nameForm . 'PROMITAGG[PROFIS]', $DatiSogg['CODFISCALE']);
                Out::valore($this->nameForm . '_PROMITAGG[PROIND]', $DatiResSogg['DESVIA'] . ' ' . $DatiResSogg['NUMCIV']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $DatiResSogg['DESLOCAL']);
                Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $DatiResSogg['PROVINCIA']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $DatiResSogg['CAP']);
                Out::valore($this->nameForm . '_PROMITAGG[PROMAIL]', $DatiResSogg['E_MAIL']);
                break;

            case 'returnAnagPerson':
                Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_PROMITAGG[PROIND]', $_POST['PROIND']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_PROMITAGG[PROMAIL]', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_PROMITAGG[PROFIS]', $_POST['PROFIS']);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_mittentiAggiuntivi');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_elemento');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_titoloForm');
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent() {
        $_POST = array();
        $returnModel = $this->returnModel;
        $_POST['event'] = $this->returnEvent;
        $_POST['model'] = $returnModel;
        $_POST['mittentiAggiuntivi'] = $this->mittentiAggiuntivi;
        $returnModelOrig = $returnModel;
        if (is_array($returnModel)) {
            $returnModelOrig = $returnModel['nameFormOrig'];
            $returnModel = $returnModel['nameForm'];
        }
        $returnObj = itaModel::getInstance($returnModelOrig, $returnModel);
        $returnObj->setEvent($this->returnEvent);
        $returnObj->parseEvent();
        $this->close();
    }

    function caricaMittentiAggiuntivi() {
        Out::show($this->nameForm);
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valore($this->nameForm . '_PROMITAGG[ROWID]', '');
        $this->elemento = -1;
        $this->CaricaGriglia();
        Out::setFocus('', $this->nameForm . '_PROMITAGG[PRONOM]');
    }

    function CaricaGriglia() {
        $appoggio = array();

        foreach ($this->mittentiAggiuntivi as $value) {
            $value['NOMINATIVO'] = $this->mittentiAggiuntivi['PRONOM'];
            if ($this->tipoProt == 'P') {
                $soggettoObj = proSoggetto::getInstance($this->proLib, $value['PRODESCOD'], $value['PRODESUFF']);
                $soggetto = $soggettoObj->getSoggetto();
                $value['NOMINATIVO'] = $soggetto['DESCRIZIONESOGGETTO'] . "<br>" . $soggetto['RUOLO'] . "-" . $soggetto['DESCRIZIONEUFFICIO'];
            } else {
                $value['NOMINATIVO'] = $value['PRONOM'];
            }
            if ($value['PROIDMAILDEST']) {
                $value['MAILMIT'] = '<span class="ui-icon ui-icon-mail-closed"></span>';
                $retRic = $this->proLib->checkMailRic($value['PROIDMAILDEST']);
                if ($retRic['ACCETTAZIONE']) {
                    $value['ACCMIT'] = '<span class="ui-icon ui-icon-check"></span>';
                    $value['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                }
                if ($retRic['CONSEGNA']) {
                    $value['CONSMIT'] = '<span class="ui-icon ui-icon-check"></span>';
                    $value['IDCONSEGNA'] = $retRic['CONSEGNA'];
                }
            }

            $appoggio[] = $value;
        }
        TableView::enableEvents($this->gridMitAgg);
        TableView::clearGrid($this->gridMitAgg);
        if ($appoggio) {
            $ita_grid01 = new TableView(
                    $this->gridMitAgg, array('arrayTable' => $appoggio,
                'rowIndex' => 'idx')
            );
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows('10000');
            $ita_grid01->getDataPage('json');
        }
        return;
    }

    function Dettaglio() {
        $promitagg_rec = $this->mittentiAggiuntivi[$this->elemento];
        Out::clearFields($this->nameForm, $this->divDettaglio);
        Out::valori($promitagg_rec, $this->nameForm . '_PROMITAGG');
        Out::valore($this->nameForm . '_CODICE', $promitagg_rec['PRODESCOD']);
        $anauff_rec = $this->proLib->GetAnauff($promitagg_rec['PRODESUFF']);
        Out::valore($this->nameForm . '_UFFNOM', $anauff_rec['UFFDES']);



        Out::setFocus('', $this->nameForm . '_PROMITAGG[PRONOM]');
    }

    function DecodAnamed($codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipoRic, $tutti);
        Out::valore($this->nameForm . '_CODICE', $anamed_rec['MEDCOD']);
        Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $anamed_rec['MEDNOM']);
        Out::valore($this->nameForm . '_PROMITAGG[PROIND]', $anamed_rec['MEDIND']);
        Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $anamed_rec['MEDCIT']);
        Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $anamed_rec['MEDPRO']);
        Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $anamed_rec['MEDCAP']);
        Out::valore($this->nameForm . '_PROMITAGG[PROMAIL]', $anamed_rec['MEDEMA']);
        Out::valore($this->nameForm . '_PROMITAGG[PROFIS]', $anamed_rec['MEDFIS']);
        return $anamed_rec;
    }

    private function suggestAnamed() {
        if ($this->tipoProt == 'C' || $this->tipoProt == 'P') {
            $filtroUff = " AND MEDUFF" . $this->PROT_DB->isNotBlank();
        }
        $q = itaSuggest::getQuery();
        itaSuggest::setNotFoundMessage('Nessun risultato.');

        $parole = explode(' ', $q);
        foreach ($parole as $k => $parola) {
            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
        }
        $where = implode(" AND ", $parole);
        $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE " . $where . " $filtroUff  AND MEDANN=0 ORDER BY MEDNOM");

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
                itaSuggest::addSuggest($anamed_rec['MEDNOM'] . $indirizzo, array($this->nameForm . "_CODICE" => $anamed_rec['MEDCOD'], $this->nameForm . "_PROMITAGG[PRODESUFF]" => '', $this->nameForm . "_UFFNOM" => ''));
            }
        }
        itaSuggest::sendSuggest();
//            echo $anamed_rec['MEDNOM'] . $indirizzo . "|" . $this->nameForm . "_CODICE|" . $anamed_rec['MEDCOD'] . "|" . $this->nameForm . '_PROMITAGG[PRODESUFF]' . "||" . $this->nameForm . '_UFFNOM' . "|\n";
    }

    function registraAnamed() {
        $medcod = '';
        for ($i = 1; $i <= 999999; $i++) {
            $codice = str_repeat("0", 6 - strlen(trim($i))) . trim($i);
            $anamed_rec = $this->proLib->GetAnamed($codice);
            if (!$anamed_rec) {
                $medcod = $codice;
                break;
            }
        }
        if ($medcod == '') {
            Out::msgStop("Attenzione!", "Contattare l'assistenza, raggiunto il limite di inserimento mittenti/destinatari.");
        } else {
            $anamed_rec['MEDCOD'] = $medcod;
            $anamed_rec['MEDNOM'] = $_POST[$this->nameForm . '_PROMITAGG']['PRONOM'];
            $anamed_rec['MEDCIT'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCIT'];
            $anamed_rec['MEDIND'] = $_POST[$this->nameForm . '_PROMITAGG']['PROIND'];
            $anamed_rec['MEDCAP'] = $_POST[$this->nameForm . '_PROMITAGG']['PROCAP'];
            $anamed_rec['MEDPRO'] = $_POST[$this->nameForm . '_PROMITAGG']['PROPRO'];
            $anamed_rec['MEDEMA'] = $_POST[$this->nameForm . '_PROMITAGG']['PROMAIL'];
            $anamed_rec['MEDFIS'] = $_POST[$this->nameForm . '_PROMITAGG']['PROFIS'];
            $insert_Info = 'Oggetto: ' . $anamed_rec['MEDCOD'] . " " . $anamed_rec['MEDNOM'];
            $this->insertRecord($this->PROT_DB, 'ANAMED', $anamed_rec, $insert_Info);
            Out::msgInfo("Registrazione.", "Elemento registrato correttamente.");
            $this->DecodAnamed($medcod);
            Out::setFocus('', $this->nameForm . "_PROMITAGG[PRONOM]");
        }
    }

    public function DecodAnagrafe($soggetto) {
        $nome = $soggetto['NOME'];
        $cognome = $soggetto['COGNOME'];
        Out::valore($this->nameForm . '_PROMITAGG[PRONOM]', $cognome . " " . $nome);
        Out::valore($this->nameForm . '_PROMITAGG[PROCAP]', $soggetto['CAP']);
        Out::valore($this->nameForm . '_PROMITAGG[PROPRO]', $soggetto['PROVINCIA']);
        Out::valore($this->nameForm . '_PROMITAGG[PROCIT]', $soggetto['RESIDENZA']);
        Out::valore($this->nameForm . '_PROMITAGG[PROIND]', $soggetto['INDIRIZZO'] . ' ' . $soggetto['CIVICO']);
    }

}

?>