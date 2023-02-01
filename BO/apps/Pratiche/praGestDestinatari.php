<?php

/**
 *
 * Gestione destinatari
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Paratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.mosiconi@italsoft.eu>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    07.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaLib.class.php';
include_once ITA_BASE_PATH . '/apps/Anagrafe/anaRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlMessage.class.php';

function praGestDestinatari() {
    $praGestDestinatari = new praGestDestinatari();
    $praGestDestinatari->parseEvent();
    return;
}

class praGestDestinatari extends itaModel {

    public $nameForm = "praGestDestinatari";
    public $COMUNI_DB;
    public $mode;
    public $destinatario;
    public $rowid;
    public $praLib;
    public $proLib;
    public $keyPasso;
    public $currGesnum;
    public $giorniScadenza;
    public $rowidMail;

    function __construct() {
        parent::__construct();
        try {
            $this->praLib = new praLib();
            $this->proLib = new proLib();
            $this->COMUNI_DB = ItaDB::DBOpen('COMUNI', false);
            $this->destinatario = App::$utente->getKey($this->nameForm . '_destinatario');
            $this->mode = App::$utente->getKey($this->nameForm . '_mode');
            $this->rowid = App::$utente->getKey($this->nameForm . '_rowid');
            $this->keyPasso = App::$utente->getKey($this->nameForm . '_keyPasso');
            $this->currGesnum = App::$utente->getKey($this->nameForm . '_currGesnum');
            $this->giorniScadenza = App::$utente->getKey($this->nameForm . '_giorniScadenza');
            $this->rowidMail = App::$utente->getKey($this->nameForm . '_rowidMail');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        App::$utente->setKey($this->nameForm . '_destinatario', $this->destinatario);
        App::$utente->setKey($this->nameForm . '_mode', $this->mode);
        App::$utente->setKey($this->nameForm . '_rowid', $this->rowid);
        App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
        App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
        App::$utente->setKey($this->nameForm . '_giorniScadenza', $this->giorniScadenza);
        App::$utente->setKey($this->nameForm . '_rowidMail', $this->rowidMail);
    }

    public function setMode($mode) {
        $this->mode = $mode;
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function setGesnum($gesnum) {
        $this->currGesnum = $gesnum;
    }

    public function setGiorniScadenza($giorni) {
        $this->giorniScadenza = $giorni;
    }

    public function setRowid($rowid) {
        if ($rowid != "") {
            $this->rowid = $rowid;
        } else {
            $this->rowid = "";
        }
    }

    public function setDestinatario($destinatario) {
        $this->destinatario = $destinatario;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::attributo($this->nameForm . '_PRAMITDEST[SCADENZARISCONTRO]', 'readonly', '0');
                $this->Nascondi();
                $this->DecodAnatsp($this->destinatario['TIPOINVIO']);
                $this->inizializza();
                switch ($this->mode) {
                    case "new":
                        Out::show($this->nameForm . '_Aggiungi');
                        break;
                    case "edit":
                        Out::show($this->nameForm . '_Aggiorna');
                        //$this->DecodDataScadenza();
                        Out::valori($this->destinatario, $this->nameForm . "_PRAMITDEST");
                        Out::valore($this->nameForm . "_PRAMITDEST[NOME]", strip_tags($this->destinatario['NOME']));
                        if ($this->destinatario['IDMAIL'] == "" && $this->destinatario['DATAINVIO'] == "") {
                            Out::html($this->nameForm . '_statoComunicazione', "Comunicazione non ancora inviata");
                        } else {
                            if ($this->destinatario['IDMAIL']) {
                                $icon = $this->praLib->GetIconAccettazioneConsegna($this->destinatario['IDMAIL'], $this->keyPasso, "PASSO");
                                Out::html($this->nameForm . '_statoComunicazione', "Comunicazione N. " . $this->destinatario['ROWID'] . " inviata in data "
                                        . substr($this->destinatario['DATAINVIO'], 6, 2) . "/" . substr($this->destinatario['DATAINVIO'], 4, 2) . "/" . substr($this->destinatario['DATAINVIO'], 0, 4) . "<br>" . $icon['accettazione'] . "<br>" . $icon['consegna']);
                            } else {
                                Out::html($this->nameForm . '_statoComunicazione', "Comunicazione non ancora inviata");
                            }
                        }
                        $Anamed_rec = $this->proLib->GetAnamed($this->destinatario['CODICE']);
                        if ($Anamed_rec) {
                            $Tabdag_tab = $this->proLib->GetTabdag("ANAMED", "chiave", $Anamed_rec['ROWID'], "EMAILPEC", 0, true);
                            if ($Tabdag_tab) {
                                Out::show($this->nameForm . '_PRAMITDEST[MAIL]_butt');
                                Out::show($this->nameForm . '_MailPreferita');
                            } else {
                                Out::hide($this->nameForm . '_PRAMITDEST[MAIL]_butt');
                                Out::hide($this->nameForm . '_MailPreferita');
                            }
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiungi':
                        $this->destinatario = $_POST[$this->nameForm . '_PRAMITDEST'];
                        $sql = "SELECT * FROM ANAMED WHERE MEDCOD= '" . $this->destinatario['CODICE'] . "' AND MEDUFF " . $this->proLib->getPROTDB()->isNotBlank();
                        $anamed_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
                        if (!$anamed_rec) {
                            if ($this->destinatario['MAIL'] == "") {
                                Out::msgInfo("Destinatari", "Campo <b>E-Mail</b> obbligatorio per  Destinatari Esterni.");
                                break;
                            }
                        }
                        $this->destinatario['NOME'] = "<span style = \"color:orange;\">" . $_POST[$this->nameForm . '_PRAMITDEST']['NOME'] . "</span>";
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_Aggiorna':

                        //$this->destinatario = $_POST[$this->nameForm . '_PRAMITDEST'];
                        $this->destinatario['CODICE'] = $_POST[$this->nameForm . '_PRAMITDEST']['CODICE'];
                        $nome = $_POST[$this->nameForm . '_PRAMITDEST']['NOME'];
                        if ($this->destinatario['ROWID'] == 0) {
                            $nome = "<span style = \"color:orange;\">" . $_POST[$this->nameForm . '_PRAMITDEST']['NOME'] . "</span>";
                        }
                        $this->destinatario['NOME'] = $nome;
                        $this->destinatario['FISCALE'] = $_POST[$this->nameForm . '_PRAMITDEST']['FISCALE'];
                        $this->destinatario['INDIRIZZO'] = $_POST[$this->nameForm . '_PRAMITDEST']['INDIRIZZO'];
                        $this->destinatario['COMUNE'] = $_POST[$this->nameForm . '_PRAMITDEST']['COMUNE'];
                        $this->destinatario['PROVINCIA'] = $_POST[$this->nameForm . '_PRAMITDEST']['PROVINCIA'];
                        $this->destinatario['CAP'] = $_POST[$this->nameForm . '_PRAMITDEST']['CAP'];
                        $this->destinatario['MAIL'] = $_POST[$this->nameForm . '_PRAMITDEST']['MAIL'];
                        $this->destinatario['DATAINVIO'] = $_POST[$this->nameForm . '_PRAMITDEST']['DATAINVIO'];
                        $this->destinatario['ORAINVIO'] = $_POST[$this->nameForm . '_PRAMITDEST']['ORAINVIO'];
                        $this->destinatario['DATARISCONTRO'] = $_POST[$this->nameForm . '_PRAMITDEST']['DATARISCONTRO'];
                        $dataScadenzaRiscontro = $this->praLib->CalcolaDataScadenza($this->giorniScadenza, $this->destinatario['DATAINVIO'], $this->destinatario['DATARISCONTRO']);
                        if ($dataScadenzaRiscontro !== false) {
                            Out::valore($this->nameForm . "_PRAMITDEST[SCADENZARISCONTRO]", $dataScadenzaRiscontro);

                            $this->destinatario['SCADENZARISCONTRO'] = $dataScadenzaRiscontro;
                        }
                        $this->destinatario['TIPOINVIO'] = $_POST[$this->nameForm . '_PRAMITDEST']['TIPOINVIO'];
                        $this->returnToParent();
                        break;
                    case $this->nameForm . '_PRAMITDEST[NOME]_butt':
                        proRic::proRicAnamed($this->nameForm, "", 'proAnamed', '2');
//                        $Filent_Rec = $this->praLib->GetFilent(18);
//                        if ($Filent_Rec['FILVAL'] == 1) {
//                            proRic::proRicAnamed($this->nameForm, "", 'proAnamed', '2');
//                        } else {
//                            praRic::praRicAnades($this->nameForm);
//                        }
                        break;
                    case $this->nameForm . '_MailPreferita':
                        if (!$this->praLib->AggiornaMailPreferita($_POST[$this->nameForm . '_PRAMITDEST']['CODICE'], $this->rowidMail, $_POST[$this->nameForm . '_PRAMITDEST']['MAIL'])) {
                            Out::msgStop("Attenzione", "Aggiornamento mail preferita fallito su TABDAG");
                            break;
                        }
                        Out::msgBlock("", 3500, true, "La mail " . $_POST[$this->nameForm . '_PRAMITDEST']['MAIL'] . " è stata impostata come preferita per il destinatario " . $_POST[$this->nameForm . '_PRAMITDEST']['NOME']);
                        break;
                    case $this->nameForm . '_PRAMITDEST[MAIL]_butt':
                        $Anamed_rec = $this->proLib->GetAnamed($_POST[$this->nameForm . '_PRAMITDEST']['CODICE']);
                        $ret = proRic::proRicTabdagMail($this->nameForm, "ANAMED", $Anamed_rec['ROWID'], "", "Il Destinatario ha più Mail. Sceglierne una");
                        if ($ret) {
                            $Tabdag_rec = $this->proLib->GetTabdag($ret, 'rowid');
                            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $Tabdag_rec['TDAGVAL']);
                        }
                        break;
                    case $this->nameForm . '_CercaAnagrafe':
                        //anaRic::anaRicAnagra($this->nameForm, '', 'PARTENZA');
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
                        $cf = strtoupper($_POST[$this->nameForm . "_PRAMITDEST"]['FISCALE']);
                        if ($cf == "") {
                            Out::msgInfo("Attenzione", "Per visualizzare la famiglia scegliere un codice fiscale");
                            return false;
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
                    case $this->nameForm . "_VediMailAcc":
                        $icon = $this->praLib->GetIconAccettazioneConsegna($this->destinatario['IDMAIL'], $this->keyPasso, "PASSO");
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $icon['IDMAILACC'];
                        $_POST['tipo'] = 'id';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . "_VediMailCons":
                        $icon = $this->praLib->GetIconAccettazioneConsegna($this->destinatario['IDMAIL'], $this->keyPasso, "PASSO");
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $icon['IDMAILCON'];
                        $_POST['tipo'] = 'id';
                        itaLib::openForm($model);
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
                            Out::valore($this->nameForm . '_PRAMITDEST[NOME]', $dati['CognomeNome']);
                            Out::valore($this->nameForm . '_PRAMITDEST[FISCALE]', $dati['CodiceFiscale']);
                            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $dati['Email']);
                            Out::valore($this->nameForm . '_PRAMITDEST[INDIRIZZO]', $dati['IndirizzoVia']);
                            Out::valore($this->nameForm . '_PRAMITDEST[COMUNE]', $dati['DescrizioneComuneDiResidenza']);
                            Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $dati['CapComuneDiResidenza']);
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
                    case $this->nameForm . "_Comint":
                        praRic::praRicAnades($this->nameForm, "", "WHERE DESNUM = '$this->currGesnum'");
                        break;
                    case 'close-portlet':
                        $this->close();
                        break;
                    case $this->nameForm . '_PRAMITDEST[TIPOINVIO]_butt':
                        $where = " WHERE ANATSP.TSPTIPO != " . prolib::TIPOSPED_CART;
                        proRic::proRicAnatsp(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameForm
                        ), $where);
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
                    case $this->nameForm . "_PRAMITDEST[CODICE]":
                        if ($_POST[$this->nameForm . '_PRAMITDEST']["CODICE"]) {
                            $codice = str_pad($_POST[$this->nameForm . "_PRAMITDEST"]['CODICE'], 6, "0", STR_PAD_LEFT);
                            $Filent_Rec = $this->praLib->GetFilent(18);
                            if ($Filent_Rec['FILVAL'] == 1) {
                                $this->DecodAnamedComP($codice);
                            } else {
                                $this->decodAnades($codice, "", "codiceSogg");
                            }
                        }
//                        $codice = str_pad($_POST[$this->nameForm . "_PRAMITDEST"]['CODICE'], 6, "0", STR_PAD_LEFT);
//                        Out::valore($this->nameForm . "_PRAMITDEST[CODICE]", $codice);
//                        $this->DecodAnamedComP($codice);
                        break;
                    case $this->nameForm . '_PRAMITDEST[NOME]':
                        $Filent_Rec = $this->praLib->GetFilent(18);
                        if ($Filent_Rec['FILVAL'] == 1) {
                            $anamed_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), "SELECT * FROM ANAMED WHERE MEDNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_PRAMITDEST']["NOME"]) . "%' AND MEDANN=0", false);
                            $this->DecodAnamedComP($anamed_rec['MEDCOD']);
                        } else {
                            $anades_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), "SELECT * FROM ANADES WHERE DESNOM LIKE '%"
                                            . addslashes($_POST[$this->nameForm . '_PRAMITDEST']["NOME"]) . "%'", false);
                            $this->decodAnades($anades_rec['ROWID'], "", "rowid");
                        }
                        break;
                    case $this->nameForm . "_PRAMITDEST[DATAINVIO]":
                        $dataScadenzaRiscontro = $this->praLib->CalcolaDataScadenza($this->giorniScadenza, $_POST[$this->nameForm . '_PRAMITDEST']['DATAINVIO'], $_POST[$this->nameForm . '_PRAMITDEST']['DATARISCONTRO']);
                        Out::valore($this->nameForm . "_PRAMITDEST[SCADENZARISCONTRO]", $dataScadenzaRiscontro);
                        break;
                    case $this->nameForm . "_PRAMITDEST[DATARISCONTRO]":
                        $dataScadenzaRiscontro = $this->praLib->CalcolaDataScadenza($this->giorniScadenza, $_POST[$this->nameForm . '_PRAMITDEST']['DATAINVIO'], $_POST[$this->nameForm . '_PRAMITDEST']['DATARISCONTRO']);
                        Out::valore($this->nameForm . "_PRAMITDEST[SCADENZARISCONTRO]", $dataScadenzaRiscontro);
                        break;
                    case $this->nameForm . '_PRAMITDEST[COMUNE]':
                        $comuni_rec = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE COMUNE ='"
                                        . addslashes($_POST[$this->nameForm . '_PRAMITDEST']['COMUNE']) . "'", false);
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $comuni_rec['COAVPO']);
                            Out::valore($this->nameForm . '_PRAMITDEST[PROVINCIA]', $comuni_rec['PROVIN']);
                        }
                        break;
                    case $this->nameForm . '_PRAMITDEST[TIPOINVIO]':
                        /*
                        $codice = $_POST[$this->nameForm . '_ANAPRO']['PROTSP'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnatsp($codice);
                        } else {
                            Out::valore($this->nameForm . '_Tspdes', "");
                        }
                         
                         */
                        break;
                        
                }
                break;
            case 'suggest':
                switch ($_POST['id']) {
                    case $this->nameForm . '_PRAMITDEST[NOME]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $anamed_tab = $this->proLib->getGenericTab("SELECT * FROM ANAMED WHERE " . $this->proLib->getPROTDB()->strUpper('MEDNOM') . " LIKE '%"
                                . addslashes(strtoupper(itaSuggest::getQuery())) . "%' AND MEDANN=0");
                        foreach ($anamed_tab as $anamed_rec) {
                            $indirizzo = $anamed_rec['MEDIND'] . " " . $anamed_rec['MEDCIT'] . " " . $anamed_rec['MEDPRO'];
                            if (trim($indirizzo) != '') {
                                $indirizzo = " - " . $indirizzo;
                            } else {
                                $indirizzo = '';
                            }
                            itaSuggest::addSuggest($anamed_rec['MEDNOM'], array(
                                $this->nameForm . "_ANADES[DESCOD]" => $anamed_rec['MEDCOD'],
                                $this->nameForm . "_ANADES[DESFIS]" => $anamed_rec['MEDFIS'],
                                $this->nameForm . "_ANADES[DESIND]" => $anamed_rec['MEDIND'],
                                $this->nameForm . "_ANADES[DESCIT]" => $anamed_rec['MEDCIT'],
                                $this->nameForm . "_ANADES[DESPRO]" => $anamed_rec['MEDPRO'],
                                $this->nameForm . "_ANADES[DESCAP]" => $anamed_rec['MEDCAP'],
                                $this->nameForm . "_ANADES[DESEMA]" => $anamed_rec['MEDEMA']));
                        }
                        itaSuggest::sendSuggest();
                        break;
                    case $this->nameForm . '_PRAMITDEST[COMUNE]':
                        itaSuggest::setNotFoundMessage('Nessun risultato.');
                        $comuni_tab = ItaDB::DBSQLSelect($this->COMUNI_DB, "SELECT * FROM COMUNI WHERE " . $this->COMUNI_DB->strUpper('COMUNE') . " LIKE '"
                                        . addslashes(strtoupper(itaSuggest::getQuery())) . "%'", true);
                        foreach ($comuni_tab as $comuni_rec) {
                            itaSuggest::addSuggest($comuni_rec['COMUNE']);
                        }
                        itaSuggest::sendSuggest();
                        break;
                }
                break;
            case 'returnanamed':
                switch ($_POST['retid']) {
                    case '2':
                        $this->DecodAnamedComP($_POST['retKey'], 'rowid');
                        break;
                }
                break;
//            case "returnAnades":
//                $this->DecodIntestatario($_POST['retKey']);
//                break;
            case "returnTabdag":
                $Tabdag_rec = $this->proLib->GetTabdag($_POST['retKey'], 'rowid');
                $this->rowidMail = $Tabdag_rec['ROWID'];
                Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $Tabdag_rec['TDAGVAL']);
                break;
            case 'returnAnades':
                $this->decodAnades($_POST['retKey'], $_POST['retid'], 'rowid');
                break;
            case 'returnRicIPA':
                $this->Nascondi();
                Out::show($this->nameForm . '_Aggiungi');
                Out::valore($this->nameForm . '_PRAMITDEST[NOME]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_PRAMITDEST[INDIRIZZO]', $_POST['PROIND']);
                Out::valore($this->nameForm . '_PRAMITDEST[COMUNE]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_PRAMITDEST[PROVINCIA]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $_POST['PROMAIL']);
                break;
            case 'returnanatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function returnToParent() {
        $returnModelObj = itaModel::getInstance($this->returnModel);
        if ($returnModelObj == false) {
            return;
        }
        $_POST = array();
        $_POST['event'] = $this->returnEvent;
        $_POST['destinatario'] = $this->destinatario;
        $_POST['rowid'] = $this->rowid;
        $returnModelObj->parseEvent();
        $this->close();
    }

    public function Nascondi() {
        Out::hide($this->nameForm . '_Aggiungi');
        Out::hide($this->nameForm . '_Aggiorna');
        Out::hide($this->nameForm . '_PRAMITDEST[MAIL]_butt');
        Out::hide($this->nameForm . '_MailPreferita');
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

    function DecodDataScadenza() {
        if ($this->giorniScadenza) {
            if ($this->destinatario['DATARISCONTRO'] != "" || $this->destinatario['DATAINVIO'] != "") {
                if ($this->destinatario['DATARISCONTRO'] && $this->destinatario['DATAINVIO'])
                    $da_ta = $this->destinatario['DATARISCONTRO'];
                if ($this->destinatario['DATARISCONTRO'] == "" && $this->destinatario['DATAINVIO'])
                    $da_ta = $this->destinatario['DATAINVIO'];
                if ($this->destinatario['DATARISCONTRO'] && $this->destinatario['DATAINVIO'] == "")
                    $da_ta = $this->destinatario['DATARISCONTRO'];
                $this->destinatario['SCADENZARISCONTRO'] = $this->proLib->AddGiorniToData($da_ta, $this->giorniScadenza);
            }else {
                $this->destinatario['SCADENZARISCONTRO'] = "";
            }
        }
    }

    function DecodAnamedComP($Codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($Codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            Out::valore($this->nameForm . '_PRAMITDEST[CODICE]', $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . '_PRAMITDEST[NOME]', $anamed_rec['MEDNOM']);
            Out::valore($this->nameForm . '_PRAMITDEST[INDIRIZZO]', $anamed_rec['MEDIND']);
            Out::valore($this->nameForm . '_PRAMITDEST[COMUNE]', $anamed_rec['MEDCIT']);
            Out::valore($this->nameForm . '_PRAMITDEST[PROVINCIA]', $anamed_rec['MEDPRO']);
            Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $anamed_rec['MEDCAP']);
            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $anamed_rec['MEDEMA']);
            Out::valore($this->nameForm . '_PRAMITDEST[FISCALE]', $anamed_rec['MEDFIS']);
            $Tabdag_tab = $this->proLib->GetTabdag("ANAMED", "chiave", $anamed_rec['ROWID'], "EMAILPEC", 0, true);
            if ($Tabdag_tab) {
                Out::show($this->nameForm . '_PRAMITDEST[MAIL]_butt');
                Out::show($this->nameForm . '_MailPreferita');
                $ret = proRic::proRicTabdagMail($this->nameForm, "ANAMED", $anamed_rec['ROWID'], "", "Il Destinatario ha più Mail. Sceglierne una");
                if ($ret) {
                    $Tabdag_rec = $this->proLib->GetTabdag($ret, 'rowid');
                    Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $Tabdag_rec['TDAGVAL']);
                    Out::hide($this->nameForm . '_PRAMITDEST[MAIL]_butt');
                    Out::hide($this->nameForm . '_MailPreferita');
                }
            } else {
                Out::hide($this->nameForm . '_PRAMITDEST[MAIL]_butt');
                Out::hide($this->nameForm . '_MailPreferita');
            }
        } else {
            Out::valore($this->nameForm . "_PRAMITDEST[CODICE]", '');
        }
        return $anamed_rec;
    }

    private function decodAnades($Codice, $retid, $tipoRic = 'codice') {
        $anades_rec = $this->praLib->GetAnades($Codice, $tipoRic);
        Out::valore($this->nameForm . "_PRAMITDEST[CODICE]", $anades_rec['DESCOD']);
        Out::valore($this->nameForm . "_PRAMITDEST[NOME]", $anades_rec['DESNOM']);
        Out::valore($this->nameForm . "_PRAMITDEST[FISCALE]", $anades_rec['DESFIS']);
        Out::valore($this->nameForm . "_PRAMITDEST[INDIRIZZO]", $anades_rec['DESIND'] . " " . $anades_rec['DESCIV']);
        Out::valore($this->nameForm . "_PRAMITDEST[COMUNE]", $anades_rec['DESCIT']);
        Out::valore($this->nameForm . "_PRAMITDEST[CAP]", $anades_rec['DESCAP']);
        Out::valore($this->nameForm . "_PRAMITDEST[PROVINCIA]", $anades_rec['DESPRO']);
        if ($anades_rec['DESPEC']) {
            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $anades_rec['DESPEC']);
        } else {
            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $anades_rec['DESEMA']);
        }
    }

//    public function DecodIntestatario($codice, $tipo = "rowid") {
//        $anades_rec = $this->praLib->GetAnades($codice, $tipo);
//        Out::valore($this->nameForm . '_PRAMITDEST[CODICE]', $anades_rec['DESCOD']);
//        Out::valore($this->nameForm . '_PRAMITDEST[NOME]', $anades_rec['DESNOM']);
//        Out::valore($this->nameForm . '_PRAMITDEST[FISCALE]', $anades_rec['DESFIS']);
//        Out::valore($this->nameForm . '_PRAMITDEST[INDIRIZZO]', $anades_rec['DESIND']);
//        Out::valore($this->nameForm . '_PRAMITDEST[COMUNE]', $anades_rec['DESCIT']);
//        Out::valore($this->nameForm . '_PRAMITDEST[PROVINCIA]', $anades_rec['DESPRO']);
//        Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $anades_rec['DESCAP']);
//        if ($anades_rec['DESPEC']) {
//            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $anades_rec['DESPEC']);
//        } else {
//            Out::valore($this->nameForm . '_PRAMITDEST[MAIL]', $anades_rec['DESEMA']);
//        }
//    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_destinatario');
        App::$utente->removeKey($this->nameForm . '_rowid');
        App::$utente->removeKey($this->nameForm . '_mode');
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_giorniScadenza');
        App::$utente->removeKey($this->nameForm . '_rowidMail');
        Out::closeDialog($this->nameForm);
    }

    function AggiungiDaAnagrafe($soggetto) {
        $this->Nascondi();
        Out::show($this->nameForm . '_Aggiungi');
        Out::valore($this->nameForm . '_PRAMITDEST[CODICE]', $soggetto['CODICEUNIVOCO']);
        Out::valore($this->nameForm . '_PRAMITDEST[NOME]', $soggetto['NOME'] . " " . $soggetto['COGNOME']);
        Out::valore($this->nameForm . '_PRAMITDEST[FISCALE]', $soggetto['CODICEFISCALE']);
        Out::valore($this->nameForm . '_PRAMITDEST[CAP]', $soggetto['CAP']);
        Out::valore($this->nameForm . '_PRAMITDEST[PROVINCIA]', $soggetto['PROVINCIA']);
        Out::valore($this->nameForm . '_PRAMITDEST[COMUNE]', $soggetto['RESIDENZA']);
        Out::valore($this->nameForm . '_PRAMITDEST[INDIRIZZO]', $soggetto['INDIRIZZO'] . " " . $soggetto['CIVICO']);
        if ($soggetto['CODICEFISCALE']) {
            Out::show($this->nameForm . "_consultaAnagrafe");
        } else {
            Out::hide($this->nameForm . "_consultaAnagrafe");
        }
    }

    private function inizializza() {
        if ($this->destinatario['TIPOINVIO'] == "CART"){
            
            Out::disableContainerFields($this->nameForm . '_divGestione');
            
//            Out::disableField($this->nameForm . '_PRAMITDEST[TIPOINVIO]');
//            Out::hide($this->nameForm . '_PRAMITDEST[NOME]_butt');

            //Out::disableButton($this->nameForm . '_PRAMITDEST[NOME]_butt');
            Out::disableButton($this->nameForm . '_CercaAnagrafe');
            Out::disableButton($this->nameForm . '_VediFamiglia');
            Out::disableButton($this->nameForm . '_CercaAnagrafeProtocollo');
            Out::disableButton($this->nameForm . '_CercaIPA');
            Out::disableButton($this->nameForm . '_Comint');
 

        }
    }    

    public function DecodAnatsp($codTsp, $tipo = 'codice') {
        $anatsp_rec = $this->proLib->GetAnatsp($codTsp, $tipo);
        Out::valore($this->nameForm . '_PRAMITDEST[TIPOINVIO]', $anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_Tspdes', $anatsp_rec['TSPDES']);
        // Stampa analogica e attivi parametro spedizione obbligatoria:

        return $anatsp_rec;
    }

}

