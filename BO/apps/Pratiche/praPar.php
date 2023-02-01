<?php

/**
 *
 * PARAMETRI APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    21.25.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
// CARICO LE LIBRERIE NECESSARIE
include_once ITA_BASE_PATH . '/apps/Pratiche/praRic.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praDizionario.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLibEnvVars.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaPDFAUtil.class.php');

function praPar() {
    $praPar = new praPar();
    $praPar->parseEvent();
    return;
}

class praPar extends itaModel {

    public $PRAM_DB;
    public $praLib;
    public $praDizionario;
    public $utiEnte;
    public $ITALWEB_DB;
    public $nameForm = "praPar";
    public $divGes = "praPar_divGestione";
    public $confAccount;
    public $maxFilentRec = 52;
    public $gridTemplateMail = "praPar_gridTemplateMail";
    public $oggettoRichiedente;
    public $bodyRichiedente;
    public $oggettoResponsabile;
    public $bodyResponsabile;
    public $oggettoAnn;
    public $bodyAnn;
    public $oggettoRichInfocamere;
    public $bodyRichInfocamere;
    public $oggettoIntRich;
    public $bodyIntRich;
    public $oggettoIntResp;
    public $bodyIntResp;
    public $oggettoARicPareri;
    public $bodyARicPareri;
    public $oggettoAEntiTerzi;
    public $mailTemplates;
    public $ITAFO_DB;
    public $paramFO;
    public $gridParametriFO = "praPar_gridParametriFronOffice";
    public $rigaParametroFO;
    public $paramVA;
    public $gridParametriVA = "praPar_gridParametriVA";
    public $rigaParametroVA;
    public $gridTipiFO = "praPar_gridTipiFO";
    public $arrTipiFO = array();

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->praLib = new praLib();
            $this->praDizionario = new praDizionario();
            $this->PRAM_DB = $this->praLib->getPRAMDB();
            $this->ITAFO_DB = $this->praLib->getITAFODB();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->confAccount = App::$utente->getKey($this->nameForm . "_confAccount");
        $this->mailTemplates = App::$utente->getKey($this->nameForm . "_mailTemplates");
        $this->paramFO = App::$utente->getKey($this->nameForm . "_paramFO");
        $this->rigaParametroFO = App::$utente->getKey($this->nameForm . "_rigaParametroFO");
        $this->paramVA = App::$utente->getKey($this->nameForm . "_paramVA");
        $this->rigaParametroVA = App::$utente->getKey($this->nameForm . "_rigaParametroVA");
        $this->arrTipiFO = App::$utente->getKey($this->nameForm . "_arrTipiFO");
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_confAccount", $this->confAccount);
            App::$utente->setKey($this->nameForm . "_mailTemplates", $this->mailTemplates);
            App::$utente->setKey($this->nameForm . "_paramFO", $this->paramFO);
            App::$utente->setKey($this->nameForm . "_rigaParametroFO", $this->rigaParametroFO);
            App::$utente->setKey($this->nameForm . "_paramVA", $this->paramVA);
            App::$utente->setKey($this->nameForm . "_rigaParametroVA", $this->rigaParametroVA);
            App::$utente->setKey($this->nameForm . "_arrTipiFO", $this->arrTipiFO);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform': // Visualizzo la form di ricerca
                $this->CreaFilentRec();
                $this->CreaCombo();
                $this->CaricaTemplateMail();
                $this->PopolaTabellaTipoMail();
                $this->CaricaParametriFO();
                $this->PopolaTabellaParametri();
                $this->CaricaVariabiliAmbiente();
                $this->PopolaTabellaAmbiente();
                $this->OpenRicerca();
                break;
            case 'onClick': // Evento Onclick
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        if ($ditta == '')
                            $ditta = App::$utente->getKey('ditta');
                        $Nome_file = Config::getPath('general.itaProc') . "ente" . $ditta . "/mail.xml";
                        $this->ScriviFileXML($Nome_file);
                        $Nome_fileAnn = Config::getPath('general.itaProc') . "ente" . $ditta . "/annullamento.xml";
                        $this->AggiornaFilent();
                        Out::msgBlock("", 1000, true, "Parametri Aggiornati Correttamente");
                        break;
                    case $this->nameForm . '_FILENT[FILDE3]_butt':
                        praRic::ricEnteMaster($this->nameForm);
                        break;
                    case $this->nameForm . '_SvuotaMaster':
                        Out::valore($this->nameForm . '_FILENT[FILDE3]', '');
                        break;
                    case $this->nameForm . '_SelezionaAccount':
                        emlRic::emlRicAccount($this->nameForm, '', 'Smtp');
                        break;
                    case $this->nameForm . '_SvuotaAccount':
                        Out::valore($this->nameForm . '_AccountSMTP', '');
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmpt':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->confAccount, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_AccountSMTP', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
//                    case $this->nameForm . '_oggettoProtocollo_butt':
//                        $contenuto = $_POST[$this->nameForm . '_oggettoProtocollo'];
//                        $Dictionary = $this->praDizionario->GetDictionary();
//                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnoggettoProtocollo', $contenuto);
//                        break;
                    case $this->nameForm . '_oggettoResponsabile_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoResponsabile'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoResponsabile', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyResponsabile':
                        $contenuto = $_POST[$this->nameForm . '_bodyResponsabile'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyResponsabile', $contenuto);
                        break;
                    case $this->nameForm . '_oggettoRichiedente_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoRichiedente'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoRichiedente', $contenuto);
                        break;
                    case $this->nameForm . '_oggettoRichInfocamere_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoRichInfocamere'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoRichInfocamere', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyRichiedente':
                        $contenuto = $_POST[$this->nameForm . '_bodyRichiedente'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyRichiedente', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyRichInfocamere':
                        $contenuto = $_POST[$this->nameForm . '_BodyRichInfocamere'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyRichInfocamere', $contenuto);
                        break;
                    case $this->nameForm . '_oggettoAnn_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoRichiedente'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoAnn', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyAnn':
                        $contenuto = $_POST[$this->nameForm . '_bodyRichiedente'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyAnn', $contenuto);
                        break;
                    case $this->nameForm . '_bodyRichiedente_edit':
                        $this->OpenEdit($_POST[$this->nameForm . '_bodyRichiedente'], $this->nameForm . '_bodyRichiedente');
                        break;
                    case $this->nameForm . '_bodyResponsabile_edit':
                        $this->OpenEdit($_POST[$this->nameForm . '_bodyResponsabile'], $this->nameForm . '_bodyResponsabile');
                        break;
                    case $this->nameForm . '_oggettoIntRich_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoIntRich'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoIntRich', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyIntRich':
                        $contenuto = $_POST[$this->nameForm . '_bodyIntRich'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyIntRich', $contenuto);
                        break;
                    case $this->nameForm . '_oggettoIntResp_butt':
                        $contenuto = $_POST[$this->nameForm . '_oggettoIntResp'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnOggettoIntResp', $contenuto);
                        break;
                    case $this->nameForm . '_dizBodyIntResp':
                        $contenuto = $_POST[$this->nameForm . '_bodyIntResp'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnBodyIntResp', $contenuto);
                        break;
                    case $this->nameForm . '_FILENT[FILDE5]_butt':
                        $enteMaster = $this->praLib->GetEnteMaster();
                        if ($enteMaster) {
                            praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_FILENT[FILDE5]', "", $enteMaster);
                        } else {
                            praRic::praRicAnapra($this->nameForm, "Ricerca procedimento", $this->nameForm . '_FILENT[FILDE5]');
                        }
                        break;
                    case $this->nameForm . '_icoOggettoRichiesta':
                        $contenuto = $_POST[$this->nameForm . '_oggettoProtocollo'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnoggettoProtocollo', $contenuto);
                        break;
                    case $this->nameForm . '_icoOggettoPratica':
                        $this->embedVars("returnPratica");
                        break;
                    case $this->nameForm . '_icoOggettoComP':
                        $this->embedVars("returnComP");
                        break;
                    case $this->nameForm . '_icoOggettoComA':
                        $this->embedVars("returnComA");
                        break;
                    case $this->nameForm . '_icoOggettoFascicolo':
                        $this->embedVars("returnFascicolo");
                        break;
                    case $this->nameForm . '_icoOggettoFascicoloFO':
                        $contenuto = $_POST[$this->nameForm . '_oggettoFascicoloFO'];
                        $Dictionary = $this->praDizionario->GetDictionary();
                        $this->praDizionario->CaricaDizionario($Dictionary, 'praPar', 'returnoggettoFascicoloFO', $contenuto);
                        break;
                    case $this->nameForm . '_buttCorpoMail':
                        $this->embedVars("returnCorpoMail");
                        break;
                    case $this->nameForm . '_buttOggettoMail':
                        $this->embedVars("returnOggettoMail");
                        break;
                    case $this->nameForm . '_icoSegProtocollo':
                        $this->embedVars("returnSegProtocollo");
                        break;
                    case $this->nameForm . '_icoSegProtocolloP7m':
                        $this->embedVars("returnSegProtocolloP7m");
                        break;
                    case $this->nameForm . '_tmlIcoOggettoPratica':
                        $this->embedVars("returnTmlOggPratica");
                        break;
                    case $this->nameForm . '_tmlIcoLinkArticolo':
                        $this->embedVars("returnTmlIcoLinkArticolo");
                        break;
                    case $this->nameForm . '_icoCorpoMail':
                        $this->embedVars("returnIcoCorpoMail");
                        break;
                    case $this->nameForm . '_oggettoMailDiff_butt':
                        $this->embedVars("returnOggettooMailDiff");
                        break;
                    case $this->nameForm . '_svuotaProcedimento':
                        Out::valore($this->nameForm . "_FILENT[FILDE5]", "");
                        Out::valore($this->nameForm . "_Desc_proc", "");
                        break;
                    case $this->nameForm . '_filde1_1_butt':
                        $Filent_Rec = $this->praLib->GetFilent(1);
                        $valori[] = array(
                            'label' => array(
                                'value' => "Ultimo progressivo occupato",
                                'style' => 'width:200px;maxlength:6;size:10;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_newProgressivoPrat',
                            'name' => $this->nameForm . '_newProgressivoPrat',
                            'type' => 'text',
                            'style' => 'margin:2px;width:80px;',
                            'maxlength' => '6',
                            'value' => $Filent_Rec['FILDE1']
                        );
                        Out::msgInput(
                                'Modifica progressivo pratiche.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaProgressivoPrat',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->nameForm . '_ConfermaProgressivoPrat':
                        $Filent_Rec = $this->praLib->GetFilent(1);
                        $Filent_Rec['FILDE1'] = $_POST[$this->nameForm . '_newProgressivoPrat'];
                        $update_Info = 'Aggiornameto progressivo filent: ' . $Filent_Rec['FILKEY'];
                        $this->updateRecord($this->PRAM_DB, 'FILENT', $Filent_Rec, $update_Info);
                        $this->OpenRicerca();
                        break;
                    case $this->nameForm . '_annoPraticaPartenza_butt':
                        praRic::praRicProges($this->nameForm, "");
                        break;
                    case $this->nameForm . '_AggiornaParametriFO':
                        $parametriSel = $this->paramFO[$this->rigaParametroFO];
                        $classe = $parametriSel['TIPOPARM'];
                        foreach ($parametriSel['LABEL'] as $key => $lab) {
                            switch ($parametriSel['DB']) {
                                case "PRAM":
                                    $sql = "SELECT * FROM ANAPAR WHERE PARKEY = '$key'";
                                    $param_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                                    $parval = $_POST[$this->nameForm . '_' . $key];

                                    /*
                                     * Per i casi speciali delle 3 scelte del mittente, prendo come valore i primi 4 caratteri (RUOCOD)
                                     */
                                    switch ($key) {
                                        case "FIRST_CHOICE_MITTPROT":
                                        case "SECOND_CHOICE_MITTPROT":
                                        case "THIRD_CHOICE_MITTPROT":
                                            $parval = substr($_POST[$this->nameForm . '_' . $key], 0, 4);
                                            break;
                                    }
                                    if ($param_rec) {
                                        $param_rec['PARVAL'] = $parval;
                                        $update_Info = 'Aggiornameto parametro ' . $key;
                                        if (!$this->updateRecord($this->PRAM_DB, 'ANAPAR', $param_rec, $update_Info)) {
                                            Out::msgStop('ATTENZIONE', 'Errore in aggiornamento del parametro ' . $key);
                                            break;
                                        }
                                    } else {
                                        $param_rec = array();
                                        $param_rec['PARKEY'] = $key;
                                        $param_rec['PARCLA'] = $classe;
                                        $param_rec['PARVAL'] = $parval;
                                        $insert_Info = 'Inserimento parametro ' . $key;
                                        if (!$this->insertRecord($this->PRAM_DB, 'ANAPAR', $param_rec, $insert_Info)) {
                                            Out::msgStop('ATTENZIONE', 'Errore in inserimento del parametro ' . $key);
                                            break;
                                        }
                                    }
                                    break;
                                case "ITAFO":
                                    $sql = "SELECT * FROM ENV_CONFIG WHERE CHIAVE = '$key'";
                                    $param_rec = ItaDB::DBSQLSelect($this->ITAFO_DB, $sql, false);
                                    if ($param_rec) {
                                        $param_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $key];
                                        $update_Info = 'Aggiornameto parametro ' . $key;
                                        if (!$this->updateRecord($this->ITAFO_DB, 'ENV_CONFIG', $param_rec, $update_Info)) {
                                            Out::msgStop('ATTENZIONE', 'Errore in aggiornamento del parametro ' . $key);
                                            break;
                                        }
                                    } else {
                                        $param_rec = array();
                                        $param_rec['CHIAVE'] = $key;
                                        $param_rec['CLASSE'] = $classe;
                                        $param_rec['CONFIG'] = $_POST[$this->nameForm . '_' . $key];
                                        $insert_Info = 'Inserimento parametro ' . $key;
                                        if (!$this->insertRecord($this->ITAFO_DB, 'ENV_CONFIG', $param_rec, $insert_Info)) {
                                            Out::msgStop('ATTENZIONE', 'Errore in inserimento del parametro ' . $key);
                                            break;
                                        }
                                    }

                                    break;
                            }
                        }
                        break;
                    case $this->nameForm . '_AggiornaParametriVA':
                        $classe = $this->paramVA[$this->rigaParametroVA]['TIPOPARM'];
                        $sql = "SELECT * FROM VARIABILIAMBIENTE WHERE VARKEY = '$classe'";
                        $parVA = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if (!$parVA) {
                            $parVA['VARKEY'] = $classe;
                            $parVA['VARVAL'] = $_POST[$this->nameForm . '_' . $classe];
                            if (!$this->insertRecord($this->PRAM_DB, 'VARIABILIAMBIENTE', $parVA, $insert_Info)) {
                                Out::msgStop("ATTENZIONE!", "Errore di Inserimento su VARIABILIAMBIENTE.");
                                return false;
                            }
                        } else {
                            $parVA['VARVAL'] = $_POST[$this->nameForm . '_' . $classe];
                            $update_Info = 'Aggiornameto parametro ' . $classe;
                            if (!$this->updateRecord($this->PRAM_DB, 'VARIABILIAMBIENTE', $parVA, $update_Info)) {
                                Out::msgStop('ATTENZIONE', 'Errore in aggiornamento del parametro ' . $classe);
                                break;
                            }
                        }
                        break;
                    case $this->nameForm . '_FIRST_CHOICE_MITTPROT_butt':
                        praRic::praRicRuoli($this->nameForm, "1");
                        break;
                    case $this->nameForm . '_SECOND_CHOICE_MITTPROT_butt':
                        praRic::praRicRuoli($this->nameForm, "2");
                        break;
                    case $this->nameForm . '_THIRD_CHOICE_MITTPROT_butt':
                        praRic::praRicRuoli($this->nameForm, "3");
                        break;
                    case $this->nameForm . '_ConfermaAggiungiTipiFO':
                        $rigasel = $this->formData[$this->gridTipiFO]['gridParam']['selrow'];
                        $tipo = $this->formData[$this->nameForm . '_tipo'];
                        $attivo = $this->formData[$this->nameForm . '_attivo'];
                        $scarico = $this->formData[$this->nameForm . '_scarico'];
                        $istanza = $this->formData[$this->nameForm . '_istanza'];
                        $new_dato = array(
                            "TIPO" => $tipo,
                            "ATTIVO" => $attivo,
                            "SCARICO" => $scarico,
                            "ISTANZA" => $istanza
                        );
                        if ($rigasel != '' && $rigasel != 'null') {
                            $this->arrTipiFO[$rigasel] = $new_dato;
                        } else {
                            $this->arrTipiFO[] = $new_dato;
                        }
                        $this->caricaGrigliaTipiFO();
                        break;
                    case $this->nameForm . '_ConfermaCancellaTipoFO':
                        foreach ($this->arrTipiFO as $key => $dato) {
                            unset($this->arrTipiFO[$key]);
                            break;
                        }
                        $this->arrTipiFO = array_values($this->arrTipiFO);
                        $this->caricaGrigliaTipiFO();
                        break;
                    case $this->nameForm . '_quietanza_butt':
                        praRic::ricAnaquiet($this->nameForm);
                        break;
                    case $this->nameForm . '_tipoimporto_butt':
                        praRic::ricAnatipimpo($this->nameForm);
                        break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridTemplateMail:
                        $rigaMail = $_POST['rowid'];
                        if ($rigaMail) {
                            $_POST = array();
                            $model = 'praTemplateMail';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnMethod'] = 'returnBodyMail';
                            $_POST['RIGAMAIL'] = $rigaMail;
                            $_POST['TIPOMAIL'] = $this->mailTemplates[$rigaMail]['TIPOMAIL'];
                            $_POST['OGGETTOMAIL'] = $this->mailTemplates[$rigaMail]['DATA']['SUBJECT'];
                            $_POST['BODYMAIL'] = $this->mailTemplates[$rigaMail]['DATA']['BODY'];
                            itaLib::openDialog($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                    case $this->gridParametriFO:
                        $rigaParam = $_POST['rowid'];
                        if ($rigaParam) {
                            $this->rigaParametroFO = $rigaParam - 1;
                            $this->parametriFO();
                        }
                        break;
                    case $this->gridParametriVA:
                        $rigaParam = $_POST['rowid'];
                        if ($rigaParam) {
                            $this->rigaParametroVA = $rigaParam - 1;
                            $this->parametriVA();
                        }
                        break;
                    case $this->gridTipiFO:
                        $riga = $_POST['rowid'];
                        $record = array();
                        foreach ($this->arrTipiFO as $key => $dato) {
                            if ($key == $riga) {
                                $record = $dato;
                                break;
                            }
                        }
                        if (!$record) {
                            Out::msgStop("Attenzione", "record non trovato");
                            break;
                        }

                        $checkedAttivo = "";
                        if ($record['ATTIVO'] == 1) {
                            $checkedAttivo = 'checked';
                        }
                        $checkedScarico = "";
                        if ($record['SCARICO'] == 1) {
                            $checkedScarico = 'checked';
                        }
                        $options[] = array("", "");
                        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES_DESCRIPTIONS as $key => $value) {
                            $sel = false;
                            if ($record['TIPO'] == $key) {
                                $sel = true;
                            }
                            $options[] = array($key, $value, $sel);
                        }
                        $campi[] = array(
                            'label' => array(
                                'value' => 'TIPO ',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_tipo',
                            'name' => $this->nameForm . '_tipo',
                            'type' => 'select',
                            'options' => $options,
                            'value' => $record['TIPO']);
                        $campi[] = array(
                            'label' => array(
                                'value' => 'ATTIVO',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_attivo',
                            'name' => $this->nameForm . '_attivo',
                            'type' => 'checkbox',
                            $checkedAttivo => $checkedAttivo);
                        $campi[] = array(
                            'label' => array(
                                'value' => 'SCARICO AUTOMATICO',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_scarico',
                            'name' => $this->nameForm . '_scarico',
                            'type' => 'checkbox',
                            $checkedScarico => $checkedScarico);

                        $envLib = new envLib();
                        $istanzeParams = $envLib->getIstanze('BACKENDFASCICOLIITALSOFT');
                        $options1[] = array("", "");
                        foreach ($istanzeParams as $istanza) {
                            $sel = false;
                            if ($record['ISTANZA'] == $istanza['CLASSE']) {
                                $sel = true;
                            }
                            $options1[] = array($istanza['CLASSE'], $istanza['DESCRIZIONE_ISTANZA'], $sel);
                        }
                        $campi[] = array(
                            'label' => array(
                                'value' => 'CODICE ISTANZA',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_istanza',
                            'name' => $this->nameForm . '_istanza',
                            'type' => 'select',
                            'options' => $options1,
                            'value' => $record['ISTANZA']);
                        Out::msgInput(
                                'Compilare i campi', $campi, array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiungiTipiFO', 'model' => $this->nameForm)
                                ), $this->nameForm . "_paneParametriFrontOffice"
                        );
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridTipiFO:
                        $options[] = array("", "");
                        foreach (praFrontOfficeManager::$FRONT_OFFICE_TYPES_DESCRIPTIONS as $key => $value) {
                            $options[] = array($key, $value);
                        }
                        $campi[] = array(
                            'label' => array(
                                'value' => 'TIPO ',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_tipo',
                            'name' => $this->nameForm . '_tipo',
                            'type' => 'select',
                            'options' => $options);
                        $campi[] = array(
                            'label' => array(
                                'value' => 'ATTIVO',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_attivo',
                            'name' => $this->nameForm . '_attivo',
                            'type' => 'checkbox');
                        $campi[] = array(
                            'label' => array(
                                'value' => 'SCARICO AUTOMATICO',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_scarico',
                            'name' => $this->nameForm . '_scarico',
                            'type' => 'checkbox');

                        $envLib = new envLib();
                        $istanzeParams = $envLib->getIstanze('BACKENDFASCICOLIITALSOFT');
                        $options1[] = array("", "");
                        foreach ($istanzeParams as $istanza) {
                            $sel = false;
                            if ($record['ISTANZA'] == $istanza['CLASSE']) {
                                $sel = true;
                            }
                            $options1[] = array($istanza['CLASSE'], $istanza['DESCRIZIONE_ISTANZA'], $sel);
                        }
                        $campi[] = array(
                            'label' => array(
                                'value' => 'CODICE ISTANZA',
                                'style' => 'width:140px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_istanza',
                            'name' => $this->nameForm . '_istanza',
                            'type' => 'select',
                            'options' => $options1);
                        Out::msgInput(
                                'Compilare i campi', $campi, array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAggiungiTipiFO', 'model' => $this->nameForm)
                                ), $this->nameForm . "_paneParametriFrontOffice"
                        );
                        break;
                }
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridTipiFO:
                        $campo = $_POST['rowid'];
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione del campo $campo?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaTipoFO', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaTipoFO', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_FlagMaster':
                    case $this->nameForm . '_FlagSlave':
                        switch ($_POST[$this->nameForm . '_TipoEnte']) {
                            case "M":
                                Out::hide($this->nameForm . '_FILENT[FILDE3]_field');
                                Out::hide($this->nameForm . '_SvuotaMaster');
                                Out::hide($this->nameForm . '_FILENT[FILCOD]');
                                Out::hide($this->nameForm . '_FILENT[FILCOD]_lbl');
                                break;
                            case "S":
                                Out::show($this->nameForm . '_FILENT[FILDE3]_field');
                                Out::show($this->nameForm . '_SvuotaMaster');
                                Out::show($this->nameForm . '_FILENT[FILCOD]');
                                Out::show($this->nameForm . '_FILENT[FILCOD]_lbl');
                                break;
                        }
                        break;
                    case $this->nameForm . "_AttivaAssegnaPassi":
                        if ($_POST[$this->nameForm . '_AttivaAssegnaPassi'] == 1) {
                            Out::show($this->nameForm . '_mettiAllaFirma_field');
                        } elseif ($_POST[$this->nameForm . '_TipoEnte'] == 0) {
                            Out::hide($this->nameForm . '_mettiAllaFirma_field');
                        }
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_quietanza':
                        $key = intval($_POST[$this->nameForm . '_quietanza']);
                        $anaquiet_rec = $this->praLib->GetAnaquiet($key);
                        Out::valore($this->nameForm . '_quietanza', $key);
                        Out::valore($this->nameForm . '_descQuietanza', $anaquiet_rec['QUIETANZATIPO']);
                        break;
                    case $this->nameForm . '_tipoimporto':
                        $key = intval($_POST[$this->nameForm . '_tipoimporto']);
                        $anatipimpo_rec = $this->praLib->GetAnatipimpo($key);
                        Out::valore($this->nameForm . '_tipoimporto', $key);
                        Out::valore($this->nameForm . '_descImporto', $anatipimpo_rec['DESCTIPOIMPO']);
                        break;
                }
                break;
            case 'returnAccountSmtp':
                $this->confAccount = $_POST['retKey'];
                $valori[] = array(
                    'label' => array(
                        'value' => "Password:",
                        'style' => 'margin-top:10px;width:80px;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                    ),
                    'id' => $this->nameForm . '_passwordAccount',
                    'name' => $this->nameForm . '_passwordAccount',
                    'type' => 'password',
                    'style' => 'margin-top:10px;width:550px;',
                    'value' => ''
                );
                $messaggio = "Inserisci la password dell'account selezionato";
                Out::msgInput(
                        'Account Email', $valori
                        , array(
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordSmpt', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;

            case 'returnoggettoProtocollo':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoProtocollo' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnOggettoResponsabile':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoResponsabile' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnEnteMaster':
                Out::valore($this->nameForm . '_FILENT[FILDE3]', $_POST['CODICE']);
                break;
            case 'returnBodyResponsabile':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyResponsabile","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnOggettoRichiedente':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoRichiedente' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnBodyRichiedente':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyRichiedente","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnOggettoRichInfocamere':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoRichInfocamere' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnBodyRichInfocamere':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyRichInfocamere","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnOggettoAnn':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoAnn' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnBodyAnn':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyAnn","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnTextEdit':
                Out::valore($_POST['returnField'], $_POST['returnText']);
                break;
            case 'returnOggettoIntRich':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoIntRich' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnBodyIntRich':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyIntRich","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnOggettoIntResp':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoIntResp' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnBodyIntResp':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice('tinyInsertContent("' . $this->nameForm . '_bodyIntResp","' . $Dictionary[$_POST['retKey']]['chiave'] . '");');
                break;
            case 'returnOggRichiesta':
                Out::codice("$('#" . $this->nameForm . '_oggettoProtocollo' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnPratica':
                Out::codice("$('#" . $this->nameForm . '_oggettoPratica' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnComP':
                Out::codice("$('#" . $this->nameForm . '_oggettoComP' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnComA':
                Out::codice("$('#" . $this->nameForm . '_oggettoComA' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnFascicolo':
                Out::codice("$('#" . $this->nameForm . '_oggettoFascicolo' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnoggettoFascicoloFO':
                $Dictionary = $this->praDizionario->GetDictionary();
                Out::codice("$('#" . $this->nameForm . '_oggettoFascicoloFO' . "').replaceSelection('" . $Dictionary[$_POST['retKey']]['chiave'] . "', true);");
                break;
            case 'returnCorpoMail':
                Out::codice('tinyInsertContent("' . $this->nameForm . '_corpoMail","' . $_POST["rowData"]['markupkey'] . '");');
                break;
            case 'returnOggettoMail':
                Out::codice("$('#" . $this->nameForm . '_oggettoMail' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnSegProtocollo':
                Out::codice("$('#" . $this->nameForm . '_segProtocollo' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnSegProtocolloP7m':
                Out::codice("$('#" . $this->nameForm . '_segProtocolloP7m' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnTmlOggPratica':
                Out::codice("$('#" . $this->nameForm . '_tmlOggettoPratica' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnTmlIcoLinkArticolo':
                Out::codice("$('#" . $this->nameForm . '_tmlLinkArticolo' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnOggettooMailDiff':
                Out::codice("$('#" . $this->nameForm . '_oggettoMailDiff' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnIcoCorpoMail':
                Out::codice("$('#" . $this->nameForm . '_corpoMailDiff' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnAnapra':
                $this->DecodeAnapra($_POST['rowData']['ID_ANAPRA'], "rowid");
                break;
            case 'returnProges':
                $this->DecodProges($_POST['rowData']['ROWID'], 'rowid');
                break;
            case 'returnBodyMail':
                //$tipoMail = $this->TipoMail();
                //$keyOggetto = 'oggetto' . $tipoMail[$_POST['RIGAMAIL']]['CHIAVE'];
                //$keyBody = 'body' . $tipoMail[$_POST['RIGAMAIL']]['CHIAVE'];

                $this->mailTemplates[$_POST['RIGAMAIL']]['DATA']['SUBJECT'] = $_POST['OGGETTOMAIL'];
                $this->mailTemplates[$_POST['RIGAMAIL']]['DATA']['BODY'] = $_POST['BODYMAIL'];


//                $this->$keyOggetto = $_POST['OGGETTOMAIL'];
//                $this->$keyBody = $_POST['BODYMAIL'];
                break;
            case 'returnAnaruo':
                $this->DecodAnaruo($_POST['retKey'], 'rowid', $_POST['retid']);
                break;
            case 'retRicAnaquiet':
                $anaquiet_rec = $this->praLib->GetAnaquiet($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_quietanza', $anaquiet_rec['CODQUIET']);
                Out::valore($this->nameForm . '_descQuietanza', $anaquiet_rec['QUIETANZATIPO']);
                break;
            case 'retRicAnatipimpo':
                $anatipimpo_rec = $this->praLib->GetAnatipimpo($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_tipoimporto', $anatipimpo_rec['CODTIPOIMPO']);
                Out::valore($this->nameForm . '_descImporto', $anatipimpo_rec['DESCTIPOIMPO']);
                break;
        }
    }

    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_confAccount');
        App::$utente->removeKey($this->nameForm . '_mailTemplates');
        App::$utente->removeKey($this->nameForm . '_paramFO');
        App::$utente->removeKey($this->nameForm . '_rigaParametroFO');
        App::$utente->removeKey($this->nameForm . '_paramVA');
        App::$utente->removeKey($this->nameForm . '_rigaParametroVA');
        App::$utente->removeKey($this->nameForm . '_arrTipiFO');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    function DecodeAnapra($codice, $tipo = "codice") {
        $enteMaster = $this->praLib->GetEnteMaster();
        if ($enteMaster) {
            $praLib_master = new praLib($enteMaster);
            $anapra_rec = $praLib_master->GetAnapra($codice, $tipo);
        } else {
            $anapra_rec = $this->praLib->GetAnapra($codice, $tipo);
        }
        Out::valore($this->nameForm . "_FILENT[FILDE5]", $anapra_rec['PRANUM']);
        Out::valore($this->nameForm . "_Desc_proc", $anapra_rec['PRADES__1']);
    }

    function DecodProges($Codice, $tipoRic = 'codice') {
        $proges_rec = $this->praLib->GetProges($Codice, $tipoRic);
        Out::valore($this->nameForm . "_numPraticaPartenza", substr($proges_rec['GESNUM'], 4));
        Out::valore($this->nameForm . "_annoPraticaPartenza", substr($proges_rec['GESNUM'], 0, 4));
    }

    function OpenRicerca() {
        $this->arrTipiFO = array();
        Out::show($this->divGes, '');
        Out::clearFields($this->nameForm, $this->divGes);
        Out::show($this->nameForm . '_Aggiorna');
        Out::show($this->nameForm);
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');

        $managerType = itaPDFAUtil::getManagerType();
        if ($managerType == 'none' || $managerType == false) {
            Out::block($this->nameForm . "_divPDFA", "#000000", "0.07");
        } else {
            Out::unBlock($this->nameForm . "_divPDFA");
        }
        $Nome_file = Config::getPath('general.itaProc') . "ente" . $ditta . "/mail.xml";
        if (!file_exists($Nome_file)) {
            $this->ScriviFileXML($Nome_file);
        } else {
            $xmlObj = new QXML;
            $xmlObj->setXmlFromFile($Nome_file);
            //$arrayXml = $xmlObj->getArray();
            $arrayXml = $xmlObj->toArray($xmlObj->asObject());
            foreach ($this->mailTemplates as $key => $template) {
                $this->mailTemplates[$key]['DATA']['SUBJECT'] = $arrayXml['SUBJECT_' . $this->mailTemplates[$key]['CHIAVE']]['@textNode'];
                $this->mailTemplates[$key]['DATA']['BODY'] = $arrayXml['BODY_' . $this->mailTemplates[$key]['CHIAVE']]['@textNode'];
            }
            Out::valore($this->nameForm . '_oggettoProtocollo', $arrayXml['SUBJECT_PROTOCOLLO']['@textNode']);
            Out::valore($this->nameForm . '_oggettoFascicoloFO', $arrayXml['SUBJECT_FASCICOLO']['@textNode']);
        }
        Out::codice('tinyActivate("' . $this->nameForm . '_bodyRichiedente");');
        Out::codice('tinyActivate("' . $this->nameForm . '_corpoMail");');
        $Filent_Rec = $this->praLib->GetFilent(1);
        $Filent_Rec_2 = $this->praLib->GetFilent(2);
        $Filent_Rec_3 = $this->praLib->GetFilent(3);
        $Filent_Rec_4 = $this->praLib->GetFilent(4);
        $Filent_Rec_5 = $this->praLib->GetFilent(5);
        $Filent_Rec_6 = $this->praLib->GetFilent(6);
        $Filent_Rec_7 = $this->praLib->GetFilent(7);
        $Filent_Rec_8 = $this->praLib->GetFilent(8);
        $Filent_Rec_9 = $this->praLib->GetFilent(9);
        $Filent_Rec_10 = $this->praLib->GetFilent(10);
        $Filent_Rec_11 = $this->praLib->GetFilent(11);
        $Filent_Rec_12 = $this->praLib->GetFilent(12);
        $Filent_Rec_13 = $this->praLib->GetFilent(13);
        $Filent_Rec_14 = $this->praLib->GetFilent(14);
        $Filent_Rec_15 = $this->praLib->GetFilent(15);
        $Filent_Rec_16 = $this->praLib->GetFilent(16);
        $Filent_Rec_17 = $this->praLib->GetFilent(17);
        $Filent_Rec_18 = $this->praLib->GetFilent(18);
        $Filent_Rec_19 = $this->praLib->GetFilent(19);
        $Filent_Rec_20 = $this->praLib->GetFilent(20);
        $Filent_Rec_21 = $this->praLib->GetFilent(21);
        $Filent_Rec_22 = $this->praLib->GetFilent(22);
        $Filent_Rec_23 = $this->praLib->GetFilent(23);
        $Filent_Rec_24 = $this->praLib->GetFilent(24);
        $Filent_Rec_25 = $this->praLib->GetFilent(25);
        $Filent_Rec_26 = $this->praLib->GetFilent(26);
        $Filent_Rec_27 = $this->praLib->GetFilent(27);
        $Filent_Rec_28 = $this->praLib->GetFilent(28);
        $Filent_Rec_29 = $this->praLib->GetFilent(29);
        $Filent_Rec_30 = $this->praLib->GetFilent(30);
        $Filent_Rec_31 = $this->praLib->GetFilent(31);
        $Filent_Rec_32 = $this->praLib->GetFilent(32);
        $Filent_Rec_33 = $this->praLib->GetFilent(33);
        $Filent_Rec_34 = $this->praLib->GetFilent(34);
        $Filent_Rec_35 = $this->praLib->GetFilent(35);
        $Filent_Rec_36 = $this->praLib->GetFilent(36);
        $Filent_Rec_37 = $this->praLib->GetFilent(37);
        $Filent_Rec_38 = $this->praLib->GetFilent(38);
        $Filent_Rec_39 = $this->praLib->GetFilent(39);
        $Filent_Rec_40 = $this->praLib->GetFilent(40);
        $Filent_Rec_41 = $this->praLib->GetFilent(41);

        $Filent_Rec_42 = $this->praLib->GetFilent(42);
        $Filent_Rec_43 = $this->praLib->GetFilent(43);
        $Filent_Rec_44 = $this->praLib->GetFilent(44);
        $Filent_Rec_45 = $this->praLib->GetFilent(45);
        $Filent_Rec_46 = $this->praLib->GetFilent(46);
        $Filent_Rec_47 = $this->praLib->GetFilent(47);
        $Filent_Rec_49 = $this->praLib->GetFilent(49);
        $Filent_Rec_50 = $this->praLib->GetFilent(50);
        $Filent_Rec_51 = $this->praLib->GetFilent(51);
        $Filent_Rec_52 = $this->praLib->GetFilent(52);


        Out::valore($this->nameForm . '_filde1_1', $Filent_Rec['FILDE1']);
        Out::valore($this->nameForm . '_filval_1', $Filent_Rec['FILVAL']);
        Out::valore($this->nameForm . '_FILENT[FILCOD]', $Filent_Rec['FILCOD']);
        Out::valore($this->nameForm . '_filde1_2', $Filent_Rec_2['FILDE1']);
        Out::valore($this->nameForm . '_filde2', $Filent_Rec_2['FILDE2']);
        Out::valore($this->nameForm . '_FILENT[FILVAL]', $Filent_Rec_2['FILVAL']);
        Out::valore($this->nameForm . '_FILENT[FILDE3]', $Filent_Rec['FILDE3']);
        Out::valore($this->nameForm . '_oggettoPratica', $Filent_Rec_3['FILVAL']);
        Out::valore($this->nameForm . '_oggettoComP', $Filent_Rec_4['FILVAL']);
        Out::valore($this->nameForm . '_oggettoComA', $Filent_Rec_5['FILVAL']);
        Out::valore($this->nameForm . '_corpoMail', $Filent_Rec_6['FILVAL']);
        Out::valore($this->nameForm . '_TipoNewPratica', $Filent_Rec_9['FILVAL']);
        Out::valore($this->nameForm . '_StampaDove', $Filent_Rec_10['FILDE1']);
        Out::valore($this->nameForm . '_Rotazione', $Filent_Rec_10['FILDE2']);
        Out::valore($this->nameForm . '_CoordinataX', $Filent_Rec_10['FILDE3']);
        Out::valore($this->nameForm . '_CoordinataY', $Filent_Rec_10['FILDE4']);
        Out::valore($this->nameForm . '_segProtocollo', $Filent_Rec_10['FILVAL']);
        Out::valore($this->nameForm . '_Abilita', $Filent_Rec_11['FILDE1']);
        Out::valore($this->nameForm . '_ProgressivoAna', $Filent_Rec_11['FILDE2']);
        Out::valore($this->nameForm . '_Natura', $Filent_Rec_11['FILDE3']);
        Out::valore($this->nameForm . '_AnnoDati', $Filent_Rec_11['FILDE4']);
        Out::valore($this->nameForm . '_Tempo', $Filent_Rec_13['FILVAL']);
        Out::valore($this->nameForm . '_Unita', $Filent_Rec_13['FILDE1']);
        Out::valore($this->nameForm . '_OraInizio', $Filent_Rec_14['FILVAL']);
        Out::valore($this->nameForm . '_Tempo2', $Filent_Rec_14['FILDE1']);
        Out::valore($this->nameForm . '_Unita2', $Filent_Rec_14['FILDE2']);
        Out::valore($this->nameForm . '_VisualizzaPassiEndo', $Filent_Rec_15['FILVAL']);
        Out::valore($this->nameForm . '_VisualizzaAllegatiSha', $Filent_Rec_15['FILDE1']);
        Out::valore($this->nameForm . '_AttivaMisuratore', $Filent_Rec_16['FILVAL']);
        Out::valore($this->nameForm . '_PersonalizzaCla', $Filent_Rec_17['FILVAL']);
        Out::valore($this->nameForm . '_RicSoggetti', $Filent_Rec_18['FILVAL']);
        Out::valore($this->nameForm . '_NoValoResp', $Filent_Rec_19['FILVAL']);
        Out::valore($this->nameForm . '_AttivaTabAss', $Filent_Rec_20['FILVAL']);
        Out::valore($this->nameForm . '_AttivaTabPagamenti', $Filent_Rec_20['FILDE1']);
        //
        Out::valore($this->nameForm . '_StampaDoveP7m', $Filent_Rec_21['FILDE1']);
        Out::valore($this->nameForm . '_RotazioneP7m', $Filent_Rec_21['FILDE2']);
        Out::valore($this->nameForm . '_CoordinataXP7m', $Filent_Rec_21['FILDE3']);
        Out::valore($this->nameForm . '_CoordinataYP7m', $Filent_Rec_21['FILDE4']);
        Out::valore($this->nameForm . '_segProtocolloP7m', $Filent_Rec_21['FILVAL']);
        //
        Out::valore($this->nameForm . '_CancMailDopoAcqu', $Filent_Rec_23['FILVAL']);
        //
        Out::valore($this->nameForm . '_daPortale', $Filent_Rec_24['FILDE1']);
        Out::valore($this->nameForm . '_daPec', $Filent_Rec_24['FILDE2']);
        Out::valore($this->nameForm . '_Altro', $Filent_Rec_24['FILDE3']);
        Out::css($this->nameForm . '_daPortale', 'background-color', $Filent_Rec_24['FILDE1']);
        Out::css($this->nameForm . '_daPec', 'background-color', $Filent_Rec_24['FILDE2']);
        Out::css($this->nameForm . '_Altro', 'background-color', $Filent_Rec_24['FILDE3']);
        Out::attributo($this->nameForm . '_daPortale', "readonly", '0');
        Out::attributo($this->nameForm . '_daPec', "readonly", '0');
        Out::attributo($this->nameForm . '_Altro', "readonly", '0');
        //
        Out::valore($this->nameForm . '_tmlOggettoPratica', $Filent_Rec_25['FILVAL']);
        //
        Out::valore($this->nameForm . '_maxFilesize', $Filent_Rec_26['FILVAL']);
        Out::valore($this->nameForm . '_attivaProfiliProtocollazione', $Filent_Rec_26['FILDE1']);
        Out::valore($this->nameForm . '_CaricaSoloPassiAttivati', $Filent_Rec_27['FILDE1']);
        //
        Out::valore($this->nameForm . "_protRicevute", $Filent_Rec_28['FILDE1']);
        Out::valore($this->nameForm . "_numPraticaPartenza", $Filent_Rec_28['FILDE2']);
        Out::valore($this->nameForm . "_annoPraticaPartenza", $Filent_Rec_28['FILDE3']);
        //
        Out::valore($this->nameForm . "_attivaFascicolazione", $Filent_Rec_29['FILVAL']);
        //
        Out::valore($this->nameForm . '_oggettoFascicolo', $Filent_Rec_30['FILVAL']);
        //
        Out::valore($this->nameForm . '_oggettoMail', $Filent_Rec_31['FILVAL']);
        //
        Out::valore($this->nameForm . '_importAlleDaMail', $Filent_Rec_32['FILVAL']);
        //
        Out::valore($this->nameForm . '_creaFascioloDaPasso', $Filent_Rec_33['FILVAL']);
        //
        Out::valore($this->nameForm . '_Login', $Filent_Rec_34['FILVAL']);
        //
        Out::valore($this->nameForm . '_Password', $Filent_Rec_35['FILVAL']);
        //
        Out::valore($this->nameForm . '_Codice_ente', $Filent_Rec_36['FILVAL']);
        //
        Out::valore($this->nameForm . '_UrlSuap', $Filent_Rec_37['FILVAL']);
        //
        Out::valore($this->nameForm . '_WsSuap', $Filent_Rec_38['FILVAL']);
        //
        Out::valore($this->nameForm . '_oggettoMailDiff', $Filent_Rec_39['FILVAL']);
        //
        Out::valore($this->nameForm . '_corpoMailDiff', $Filent_Rec_40['FILVAL']);
        //
        Out::valore($this->nameForm . '_AttivaAssegnaPassi', $Filent_Rec_41['FILVAL']);
        //
        Out::valore($this->nameForm . '_AttivaControllaFOAdvanced', $Filent_Rec_42['FILVAL']);
        //
        Out::valore($this->nameForm . '_Etichette', $Filent_Rec_43['FILVAL']);
        //
        Out::valore($this->nameForm . '_caricaDatiAgg', $Filent_Rec_44['FILVAL']);
        //
        Out::valore($this->nameForm . '_tmlLinkArticolo', $Filent_Rec_45['FILVAL']);
        //
        Out::valore($this->nameForm . '_lanciaPragest', $Filent_Rec_47['FILVAL']);
        //
        Out::valore($this->nameForm . '_hideMettiAllaFirma', $Filent_Rec_50['FILVAL']);
        //
        Out::valore($this->nameForm . '_quietanza', $Filent_Rec_51['FILVAL']);
        $anaquiet_rec = $this->praLib->GetAnaquiet($Filent_Rec_51['FILVAL']);
        Out::valore($this->nameForm . '_descQuietanza', $anaquiet_rec['QUIETANZATIPO']);
        //
        Out::valore($this->nameForm . '_tipoimporto', $Filent_Rec_51['FILDE1']);
        $anatipimpo_rec = $this->praLib->GetAnatipimpo($Filent_Rec_51['FILDE1']);
        Out::valore($this->nameForm . '_descImporto', $anatipimpo_rec['DESCTIPOIMPO']);
        //
        Out::valore($this->nameForm . '_visualizzazioneClassificazioni', $Filent_Rec_52['FILVAL']);

        $this->arrTipiFO = $this->praLib->getArrayTipiFO();
        $this->caricaGrigliaTipiFO();

        Out::valore($this->nameForm . '_CreazioneFascicoloArch', $Filent_Rec_46['FILVAL']);

        switch ($Filent_Rec['FILDE4']) {
            case "M":
                Out::attributo($this->nameForm . '_FlagMaster', 'checked', '0', 'checked');
                Out::attributo($this->nameForm . '_FlagSlave', 'checked', '1');
                Out::hide($this->nameForm . '_FILENT[FILDE3]_field');
                Out::hide($this->nameForm . '_FILENT[FILCOD]');
                Out::hide($this->nameForm . '_FILENT[FILCOD]_lbl');
                Out::hide($this->nameForm . '_SvuotaMaster');
                break;
            case "S":
                Out::attributo($this->nameForm . '_FlagMaster', 'checked', '1');
                Out::attributo($this->nameForm . '_FlagSlave', 'checked', '0', 'checked');
                Out::show($this->nameForm . '_FILENT[FILDE3]_field');
                Out::show($this->nameForm . '_FILENT[FILCOD]');
                Out::show($this->nameForm . '_FILENT[FILCOD]_lbl');
                Out::show($this->nameForm . '_SvuotaMaster');
                break;
            default: Out::hide($this->nameForm . '_FILENT[FILDE3]_field');
                Out::hide($this->nameForm . '_FILENT[FILCOD]');
                Out::hide($this->nameForm . '_FILENT[FILCOD]_lbl');
                Out::hide($this->nameForm . '_SvuotaMaster');
                break;
        }
        $this->DecodeAnapra($Filent_Rec['FILDE5']);



        Out::valore($this->nameForm . '_FILENT[FILDE6]', $Filent_Rec['FILDE6']);
        //
        // Funzioni Automatiche PDF/A
        //
        $Filde2 = $Filent_Rec['FILDE2'];
        if (!$Filde2) {
            $Filde2 = "00A";
        }
        $verifyPDFA = substr($Filde2, 0, 1);
        $convertPDFA = substr($Filde2, 1, 1);
        $PDFLevel = substr($Filde2, 2, 1);
        Out::valore($this->nameForm . '_verifyPDFA', $verifyPDFA);
        Out::valore($this->nameForm . '_convertPDFA', $convertPDFA);
        switch ($PDFLevel) {
            case "A":
                Out::attributo($this->nameForm . '_LevelA', 'checked', "0", 'checked');
                break;
            case "B":
                Out::attributo($this->nameForm . '_LevelB', 'checked', "0", 'checked');
                break;
            default:
                Out::attributo($this->nameForm . '_LevelA', 'checked', "0", 'checked');
                break;
        }

        Out::hide($this->nameForm . '_mettiAllaFirma_field');
        if ($Filent_Rec_41['FILVAL'] == 1) {
            Out::show($this->nameForm . '_mettiAllaFirma_field');
            Out::valore($this->nameForm . '_mettiAllaFirma', $Filent_Rec_49['FILVAL']);
        }

        //
        // Parametri invio mail
        //
        Out::valore($this->nameForm . '_AccountSMTP', $Filent_Rec_12['FILVAL']);
        Out::valore($this->nameForm . '_RicevutaPECBreve', $Filent_Rec_12['FILDE1']);
        Out::valore($this->nameForm . '_InvMailRespProc', $Filent_Rec_12['FILDE2']);
        Out::valore($this->nameForm . '_NoMailDaGestione', $Filent_Rec_22['FILDE1']);

        //
        //
        //Url Misuratore
        //
        Out::valore($this->nameForm . '_UrlMisuratorePlanimetrie', $Filent_Rec_7['FILVAL']);

        //
        //Path Allegati
        //
        Out::valore($this->nameForm . '_PathAllegati', $Filent_Rec_8['FILVAL']);
    }

    /**
     * 
     * @return boolean
     */
    function CreaFilentRec() {
        for ($index = 1; $index <= $this->maxFilentRec; $index++) {
            $Filent_Rec = $this->praLib->GetFilent($index);
            if (!$Filent_Rec) {
                $Filent_Rec = array();
                $Filent_rec['FILKEY'] = $index;
                $insert_Info = "Oggetto : Inserisco record filent";
                if (!$this->insertRecord($this->PRAM_DB, 'FILENT', $Filent_rec, $insert_Info)) {
                    Out::msgStop("ATTENZIONE!", "Errore di Inserimento su FILENT.");
                    return false;
                }
            }
        }
    }

    function embedVars($ritorno) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibVariabili.class.php';
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        $praLibVar = new praLibVariabili();
        $dictionaryLegend = $praLibVar->getLegendaPratica('adjacency', 'smarty');
        docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
        return true;
    }

    function CreaCombo() {
        Out::select($this->nameForm . '_TipoNewPratica', 1, '', "1", '');
        Out::select($this->nameForm . '_TipoNewPratica', 1, 'newPraDaPro', "", 'Nuova Pratica da Protocollo');
        Out::select($this->nameForm . '_TipoNewPratica', 1, 'newProDaPra', "", 'Nuovo Protocollo da Pratica');
        //
        Out::select($this->nameForm . '_Rotazione', 1, "0", "1", "0°");
        Out::select($this->nameForm . '_Rotazione', 1, "90", "0", "90°");
        Out::select($this->nameForm . '_Rotazione', 1, "180", "0", "180°");
        Out::select($this->nameForm . '_Rotazione', 1, "270", "0", "270°");
        //
        Out::select($this->nameForm . '_RotazioneP7m', 1, "0", "1", "0°");
        Out::select($this->nameForm . '_RotazioneP7m', 1, "90", "0", "90°");
        Out::select($this->nameForm . '_RotazioneP7m', 1, "180", "0", "180°");
        Out::select($this->nameForm . '_RotazioneP7m', 1, "270", "0", "270°");
        //
        Out::select($this->nameForm . '_Ettichette', 1, "1", "0", "Attiva Stampa Multipla su Foglio A4");
        Out::select($this->nameForm . '_Ettichette', 1, "2", "0", "Attiva Stampa su Ettichetta 36x89");
        //
        $unit = array('60' => 'Minuti', '3600' => 'Ore', '86400' => 'Giorni', '604800' => 'Settimane');
        foreach ($unit as $key => $value) {
            $sel = $key == 'minuti' ? '1' : '0';
            Out::select($this->nameForm . '_Unita', 1, $key, $sel, $value);
        }
        //
        $unit2 = array('60' => 'Minuti', '3600' => 'Ore');
        foreach ($unit2 as $key => $value) {
            $sel = $key == 'minuti' ? '1' : '0';
            Out::select($this->nameForm . '_Unita2', 1, $key, $sel, $value);
        }
        //
        Out::select($this->nameForm . '_protocollazioneDifferita', 1, "1", "1", "Diretta");
        Out::select($this->nameForm . '_protocollazioneDifferita', 1, "2", "0", "Differita");
        //
        Out::select($this->nameForm . '_Etichette', 1, "A4", "1", "Etichetta Multipla su A4");
        Out::select($this->nameForm . '_Etichette', 1, "SINGOLA", "0", "Singola");
        //
        Out::select($this->nameForm . '_lanciaPragest', 1, "praGest", "1", "praGest");
        Out::select($this->nameForm . '_lanciaPragest', 1, "praGestElenco", "0", "praGestElenco");
        //
        Out::select($this->nameForm . '_mettiAllaFirma', 1, "", "1", "");
        Out::select($this->nameForm . '_mettiAllaFirma', 1, "GESRES", "0", "Responsabile del Fascicolo");
        Out::select($this->nameForm . '_mettiAllaFirma', 1, "PRORPA", "0", "Responsabile del Passo");

        Out::select($this->nameForm . '_CreazioneFascicoloArch', 1, '', "", 'Non creare');
        Out::select($this->nameForm . '_CreazioneFascicoloArch', 1, '1', "", 'Crea alla chiusura della Pratica');
    }

    function OpenEdit($testo, $retField) {
        $model = 'utiEditDiag';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['edit_text'] = $testo;
        $_POST['returnModel'] = $this->nameForm;
        $_POST['returnEvent'] = "returnTextEdit";
        $_POST['returnField'] = $retField;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    function GetArrayXml() {
        $xmlArray = array();
        foreach ($this->mailTemplates as $key => $template) {
            $xmlArray['SUBJECT_' . $this->mailTemplates[$key]['CHIAVE']]['@textNode'] = $this->mailTemplates[$key]['DATA']['SUBJECT'];
            $xmlArray['BODY_' . $this->mailTemplates[$key]['CHIAVE']]['@textNode'] = $this->mailTemplates[$key]['DATA']['BODY'];
        }
        $xmlArray['OGGETTO_PRATICA']['@textNode'] = $_POST[$this->nameForm . '_oggettoPratica'];
        $xmlArray['SUBJECT_PROTOCOLLO']['@textNode'] = $_POST[$this->nameForm . '_oggettoProtocollo'];
        $xmlArray['SUBJECT_FASCICOLO']['@textNode'] = $_POST[$this->nameForm . '_oggettoFascicoloFO'];
        return $xmlArray;
    }

    public function AggiornaFilent($progressivo = "") {
        //for ($i = 1; $i <= 22; $i++) {
        for ($i = 1; $i <= $this->maxFilentRec; $i++) {
            //il conto fino a 12 è dovuto alla presenza di 5 record a database.
            //se si inserisce un nuovo parametro aumentare tale valore.
            $Filent_Rec[$i] = $this->praLib->GetFilent($i);
            switch ($i) {
                case 1:
//                    if ($progressivo != "") {
//                        $Filent_Rec[1]['FILDE1'] = $progressivo;
//                    } else {
//                        $Filent_Rec[1]['FILDE1'] = $_POST[$this->nameForm . "_filde1_1"];
//                    }
                    $Filent_Rec[1]['FILVAL'] = $_POST[$this->nameForm . "_filval_1"];
                    $Filent_Rec[1]['FILDE2'] = $_POST[$this->nameForm . "_verifyPDFA"] . $_POST[$this->nameForm . "_convertPDFA"] . $_POST[$this->nameForm . "_PDFLevel"];
                    $Filent_Rec[1]['FILDE3'] = $_POST[$this->nameForm . "_FILENT"]['FILDE3'];
                    $Filent_Rec[1]['FILDE4'] = $_POST[$this->nameForm . "_TipoEnte"];
                    $Filent_Rec[1]['FILDE5'] = $_POST[$this->nameForm . "_FILENT"]['FILDE5'];
                    $Filent_Rec[1]['FILDE6'] = $_POST[$this->nameForm . "_FILENT"]['FILDE6'];
                    $Filent_Rec[1]['FILCOD'] = $_POST[$this->nameForm . "_FILENT"]['FILCOD'];
                    if ($_POST[$this->nameForm . "_TipoEnte"] == "M") {
                        $Filent_Rec[1]['FILCOD'] = 0;
                    }
//                    $update_Info[1] = 'Progressivo: ' . $Filent_Rec[1]['FILDE1'];
                    $update_Info[1] = 'Oggetto: Aggiorno Archivio Procedimenti/Funzioni automatiche PDF/Livello di Conformità';
                    break;
                case 2:
                    $Filent_Rec[2]['FILDE1'] = $_POST[$this->nameForm . "_filde1_2"];
                    $Filent_Rec[2]['FILDE2'] = $_POST[$this->nameForm . "_filde2"];
                    $Filent_Rec[2]['FILVAL'] = $_POST[$this->nameForm . "_FILENT"]['FILVAL'];
                    $update_Info[2] = 'Oggetto: Aggiorno parametri FTP';
                    break;
                case 3:
                    $Filent_Rec[3]['FILVAL'] = $_POST[$this->nameForm . "_oggettoPratica"];
                    $update_Info[3] = 'Oggetto: Aggiorno oggetto Pratica';
                    break;
                case 4:
                    $Filent_Rec[4]['FILVAL'] = $_POST[$this->nameForm . "_oggettoComP"];
                    $update_Info[4] = 'Oggetto: Aggiorno oggetto Comunicazione Partenza';
                    break;
                case 5:
                    $Filent_Rec[5]['FILVAL'] = $_POST[$this->nameForm . "_oggettoComA"];
                    $update_Info[5] = 'Oggetto: Aggiorno oggetto Comunicazione Arrivo';
                    break;
                case 6:
                    $Filent_Rec[6]['FILVAL'] = $_POST[$this->nameForm . "_corpoMail"];
                    $update_Info[6] = 'Oggetto: Aggiorno corpo com partenza';
                    break;
                case 7:
                    $Filent_Rec[7]['FILVAL'] = $_POST[$this->nameForm . "_UrlMisuratorePlanimetrie"];
                    $update_Info[7] = 'Oggetto: Aggiorno url misuratore';
                    break;
                case 8:
                    $Filent_Rec[8]['FILVAL'] = $_POST[$this->nameForm . "_PathAllegati"];
                    $update_Info[8] = 'Oggetto: Aggiorno path allegati TIFF';
                    break;
                case 9:
                    $Filent_Rec[9]['FILVAL'] = $_POST[$this->nameForm . "_TipoNewPratica"];
                    $update_Info[9] = 'Oggetto: Aggiorno Param Protocollazione Iride';
                    break;
                case 10:
                    $Filent_Rec[10]['FILDE1'] = $_POST[$this->nameForm . "_StampaDove"];
                    $Filent_Rec[10]['FILDE2'] = $_POST[$this->nameForm . "_Rotazione"];
                    $Filent_Rec[10]['FILDE3'] = $_POST[$this->nameForm . "_CoordinataX"];
                    $Filent_Rec[10]['FILDE4'] = $_POST[$this->nameForm . "_CoordinataY"];
                    $Filent_Rec[10]['FILVAL'] = $_POST[$this->nameForm . "_segProtocollo"];
                    $update_Info[10] = 'Oggetto: Aggiorno Param Stampa Segnatura su PDF';
                    break;
                case 11:
                    $Filent_Rec[11]['FILDE1'] = $_POST[$this->nameForm . "_Abilita"];
                    $Filent_Rec[11]['FILDE2'] = $_POST[$this->nameForm . "_ProgressivoAna"];
                    $Filent_Rec[11]['FILDE3'] = $_POST[$this->nameForm . "_Natura"];
                    $Filent_Rec[11]['FILDE4'] = $_POST[$this->nameForm . "_AnnoDati"];
                    $update_Info[11] = 'Oggetto: Aggiorno Param Anagrafe Tributaria';
                    break;
                case 12:
                    $Filent_Rec[12]['FILVAL'] = $_POST[$this->nameForm . "_AccountSMTP"];
                    $Filent_Rec[12]['FILDE1'] = $_POST[$this->nameForm . "_RicevutaPECBreve"];
                    //Nuovo Parametro per invio mail a resp. procedimento
                    $Filent_Rec[12]['FILDE2'] = $_POST[$this->nameForm . "_InvMailRespProc"];
                    $update_Info[12] = 'Oggetto: Aggiorno Param SMTP';
                    break;
                case 13:
                    $Filent_Rec[13]['FILVAL'] = $_POST[$this->nameForm . "_Tempo"];
                    $Filent_Rec[13]['FILDE1'] = $_POST[$this->nameForm . "_Unita"];
                    $update_Info[13] = 'Oggetto: Aggiorno Param Promemoria Calendario';
                    break;
                case 14:
                    $Filent_Rec[14]['FILVAL'] = $_POST[$this->nameForm . "_OraInizio"];
                    $Filent_Rec[14]['FILDE1'] = $_POST[$this->nameForm . "_Tempo2"];
                    $Filent_Rec[14]['FILDE2'] = $_POST[$this->nameForm . "_Unita2"];
                    $update_Info[14] = 'Oggetto: Aggiorno Param Ora Inizio Calendario';
                    break;
                case 15:
                    $Filent_Rec[15]['FILVAL'] = $_POST[$this->nameForm . "_VisualizzaPassiEndo"];
                    $Filent_Rec[15]['FILDE1'] = $_POST[$this->nameForm . "_VisualizzaAllegatiSha"];
                    $update_Info[15] = 'Oggetto: Aggiorno Param Visualizzazione Passi FO';
                    break;
                case 16:
                    $Filent_Rec[16]['FILVAL'] = $_POST[$this->nameForm . "_AttivaMisuratore"];
                    $update_Info[16] = 'Oggetto: Aggiorno Param Attiva Misuratore p7m';
                    break;
                case 17:
                    $Filent_Rec[17]['FILVAL'] = $_POST[$this->nameForm . "_PersonalizzaCla"];
                    $update_Info[17] = 'Oggetto: Aggiorno Param Personalizzo Classificazione';
                    break;
                case 18:
                    $Filent_Rec[18]['FILVAL'] = $_POST[$this->nameForm . "_RicSoggetti"];
                    $update_Info[18] = 'Oggetto: Aggiorno Param su ricerca mitt/dest';
                    break;
                case 19:
                    $Filent_Rec[19]['FILVAL'] = $_POST[$this->nameForm . "_NoValoResp"];
                    $update_Info[19] = 'Oggetto: Aggiorno Param valorizzazione responsabile';
                    break;
                case 20:
                    $Filent_Rec[20]['FILDE1'] = $_POST[$this->nameForm . "_AttivaTabPagamenti"];
                    $Filent_Rec[20]['FILVAL'] = $_POST[$this->nameForm . "_AttivaTabAss"];
                    $update_Info[20] = 'Oggetto: Aggiorno Param Attivazione Tab Funzioni aggiuntive';
                    break;
                case 21:
                    $Filent_Rec[21]['FILDE1'] = $_POST[$this->nameForm . "_StampaDoveP7m"];
                    $Filent_Rec[21]['FILDE2'] = $_POST[$this->nameForm . "_RotazioneP7m"];
                    $Filent_Rec[21]['FILDE3'] = $_POST[$this->nameForm . "_CoordinataXP7m"];
                    $Filent_Rec[21]['FILDE4'] = $_POST[$this->nameForm . "_CoordinataYP7m"];
                    $Filent_Rec[21]['FILVAL'] = $_POST[$this->nameForm . "_segProtocolloP7m"];
                    $update_Info[21] = 'Oggetto: Aggiorno Param Stampa Segnatura su cartaceo P7m';
                    break;
                case 22:
                    $Filent_Rec[22]['FILDE1'] = $_POST[$this->nameForm . "_NoMailDaGestione"];
                    $update_Info[22] = 'Oggetto: Aggiorno Blocco ricevi mail da mail da Gestione Pratiche';
                    break;
                case 23:
                    $Filent_Rec[23]['FILVAL'] = $_POST[$this->nameForm . "_CancMailDopoAcqu"];
                    $update_Info[23] = 'Oggetto: Aggiorno Cancella mail dopo acquisizione';
                    break;
                case 24:
                    $Filent_Rec[24]['FILDE1'] = $_POST[$this->nameForm . "_daPortale"];
                    $Filent_Rec[24]['FILDE2'] = $_POST[$this->nameForm . "_daPec"];
                    $Filent_Rec[24]['FILDE3'] = $_POST[$this->nameForm . "_Altro"];
                    $update_Info[24] = 'Oggetto: Aggiorno Parametri colorazione pratiche portlet';
                    break;
                case 25:
                    $Filent_Rec[25]['FILVAL'] = $_POST[$this->nameForm . "_tmlOggettoPratica"];
                    $update_Info[25] = 'Oggetto: Aggiorno Parametri Template Oggetto pratica';
                    break;
                case 26:
                    $Filent_Rec[26]['FILVAL'] = $_POST[$this->nameForm . "_maxFilesize"];
                    $Filent_Rec[26]['FILDE1'] = $_POST[$this->nameForm . "_attivaProfiliProtocollazione"];
                    $update_Info[26] = 'Oggetto: Aggiorno Parametri Grandezza Massima allegati per protocollazione';
                    break;
                case 27:
                    $Filent_Rec[27]['FILDE1'] = $_POST[$this->nameForm . "_CaricaSoloPassiAttivati"];
                    $update_Info[27] = 'Oggetto: Aggiorno Parametri Carica Passi fascicolo Automatica solo se attivata';
                    break;
                case 28:
                    $Filent_Rec[28]['FILDE1'] = $_POST[$this->nameForm . "_protRicevute"];
                    $Filent_Rec[28]['FILDE2'] = "";
                    if ($_POST[$this->nameForm . "_numPraticaPartenza"]) {
                        $Filent_Rec[28]['FILDE2'] = str_pad($_POST[$this->nameForm . "_numPraticaPartenza"], 6, "0", STR_PAD_LEFT);
                    }
                    $Filent_Rec[28]['FILDE3'] = $_POST[$this->nameForm . "_annoPraticaPartenza"];
                    $update_Info[28] = 'Oggetto: Aggiorno Parametri protocollazione ricevute';
                    break;
                case 29:
                    $Filent_Rec[29]['FILVAL'] = $_POST[$this->nameForm . "_attivaFascicolazione"];
                    $update_Info[29] = 'Oggetto: Aggiorno Parametro attiva fascicolazione';
                    break;
                case 30:
                    $Filent_Rec[30]['FILVAL'] = $_POST[$this->nameForm . "_oggettoFascicolo"];
                    $update_Info[30] = 'Oggetto: Aggiorno oggetto Fascicolo';
                    break;
                case 31:
                    $Filent_Rec[31]['FILVAL'] = $_POST[$this->nameForm . "_oggettoMail"];
                    $update_Info[31] = 'Oggetto: Aggiorno oggetto mail in partenza';
                    break;
                case 32:
                    $Filent_Rec[32]['FILVAL'] = $_POST[$this->nameForm . "_importAlleDaMail"];
                    $update_Info[32] = 'Oggetto: Aggiorno parametro import allegati da mail FO';
                    break;
                case 33:
                    $Filent_Rec[33]['FILVAL'] = $_POST[$this->nameForm . "_creaFascioloDaPasso"];
                    $update_Info[33] = 'Oggetto: Aggiorno parametro crea fascicolo da passo';
                    break;
                case 34:
                    $Filent_Rec[34]['FILVAL'] = $_POST[$this->nameForm . "_Login"];
                    $update_Info[34] = 'Oggetto: Aggiorno parametro login remoto';
                    break;
                case 35:
                    $Filent_Rec[35]['FILVAL'] = $_POST[$this->nameForm . "_Password"];
                    $update_Info[35] = 'Oggetto: Aggiorno parametro password remota';
                    break;
                case 36:
                    $Filent_Rec[36]['FILVAL'] = $_POST[$this->nameForm . "_Codice_ente"];
                    $update_Info[36] = 'Oggetto: Aggiorno parametro codice ente remota';
                    break;
                case 37:
                    $Filent_Rec[37]['FILVAL'] = $_POST[$this->nameForm . "_UrlSuap"];
                    $update_Info[37] = 'Oggetto: Aggiorno parametro url suap remota';
                    break;
                case 38:
                    $Filent_Rec[38]['FILVAL'] = $_POST[$this->nameForm . "_WsSuap"];
                    $update_Info[38] = 'Oggetto: Aggiorno parametro ws suap remota';
                    break;
                case 39:
                    $Filent_Rec[39]['FILVAL'] = $_POST[$this->nameForm . "_oggettoMailDiff"];
                    $update_Info[39] = 'Oggetto: Aggiorno parametro oggetto mail prot. remota';
                    break;
                case 40:
                    $Filent_Rec[40]['FILVAL'] = $_POST[$this->nameForm . "_corpoMailDiff"];
                    $update_Info[40] = 'Oggetto: Aggiorno parametro corpo mail prot. remota';
                    break;
                case 41:
                    $Filent_Rec[41]['FILVAL'] = $_POST[$this->nameForm . "_AttivaAssegnaPassi"];
                    $update_Info[41] = 'Oggetto: Aggiorno parametro attivazione assegnazione passi';
                    break;
                case 42:
                    $Filent_Rec[42]['FILVAL'] = $_POST[$this->nameForm . "_AttivaControllaFOAdvanced"];
                    $update_Info[42] = 'Oggetto: Aggiorno parametro corpo mail prot. remota';
                case 43:
                    $Filent_Rec[43]['FILVAL'] = $_POST[$this->nameForm . "_Etichette"];
                    $update_Info[43] = 'Oggetto: Aggiorno parametro Stampa Etichette';
                    break;
                case 44:
                    $Filent_Rec[44]['FILVAL'] = $_POST[$this->nameForm . "_caricaDatiAgg"];
                    $update_Info[44] = 'Oggetto: Aggiorno parametro carica dati aggiuntivi';
                    break;
                case 45:
                    $Filent_Rec[45]['FILVAL'] = $_POST[$this->nameForm . "_tmlLinkArticolo"];
                    $update_Info[45] = 'Oggetto: Aggiorno parametro template link articolo';
                    break;
                case 46:
                    $Filent_Rec[46]['FILVAL'] = $_POST[$this->nameForm . "_CreazioneFascicoloArch"];
                    $update_Info[46] = 'Oggetto: Aggiorno parametro creazione fascicolo archivistico';
                    break;
                case 47:
                    $Filent_Rec[47]['FILVAL'] = $_POST[$this->nameForm . "_lanciaPragest"];
                    $update_Info[47] = 'Oggetto: Aggiorno parametro del programma che lancia la gestione dei fascioli';
                    break;
                case 48:
                    $Filent_Rec[48]['FILVAL'] = "";
                    if ($this->arrTipiFO) {
                        $datiTitiFO = array('TIPIFO' => array());
                        $datiTitiFO['TIPIFO'] = $this->arrTipiFO;
                        $Filent_Rec[48]['FILVAL'] = json_encode($datiTitiFO);
                    }
                    $update_Info[48] = 'Oggetto: Aggiorno parametri Tipi FO';
                    break;
                case 49:
                    $Filent_Rec[49]['FILVAL'] = $_POST[$this->nameForm . "_mettiAllaFirma"];
                    $update_Info[49] = 'Oggetto: Aggiorno parametro metti alla firma';
                    break;
                case 50:
                    $Filent_Rec[50]['FILVAL'] = $_POST[$this->nameForm . "_hideMettiAllaFirma"];
                    $update_Info[50] = 'Oggetto: Aggiorno parametro nascondi check metti alla firma';
                    break;
                case 51:
                    $Filent_Rec[51]['FILVAL'] = $_POST[$this->nameForm . "_quietanza"];
                    $Filent_Rec[51]['FILDE1'] = $_POST[$this->nameForm . "_tipoimporto"];
                    $update_Info[51] = 'Oggetto: Aggiorno parametri pagamento default';
                    break;
                case 52:
                    $Filent_Rec[52]['FILVAL'] = $_POST[$this->nameForm . '_visualizzazioneClassificazioni'];
                    $update_Info[52] = 'Oggetto: Aggiorno parametro visualizzazione classificazioni';
                    break;
            }
            if (!$this->updateRecord($this->PRAM_DB, 'FILENT', $Filent_Rec[$i], $update_Info[$i])) {
                Out::msgStop("Attenzione", "Errore nell'aggiornamento record parametri $i");
                return false;
            }
        }
    }

    public function ScriviFileXML($Nome_file) {
        $arrayXml = $this->GetArrayXml();
        $xmlObj = new QXML;
        $rootTag = "";
        $xmlObj->toXML($arrayXml, $rootTag);
        $fileXml = $xmlObj->getXml();
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');
        $File = fopen($Nome_file, "w+");
        fwrite($File, "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>");
        fwrite($File, "<ROOT>");
        fwrite($File, $fileXml);
        fwrite($File, "</ROOT>");
        fclose($File);
    }

    public function CaricaTemplateMail() {
        $this->mailTemplates = array();
        $this->mailTemplates[1] = array('RIGAMAIL' => 1, 'TIPOMAIL' => "Mail al richiedente di conferma inoltro richiesta OnLine", 'CHIAVE' => "RICHIEDENTE");
        $this->mailTemplates[2] = array('RIGAMAIL' => 2, 'TIPOMAIL' => "Mail ai responsabili procedimento di conferma inoltro richiesta OnLine", 'CHIAVE' => "RESPONSABILE");
        $this->mailTemplates[3] = array('RIGAMAIL' => 3, 'TIPOMAIL' => "Mail al richiedente di conferma inoltro richiesta OnLine a Infocamere", 'CHIAVE' => "RICHINFOCAMERE");
        $this->mailTemplates[4] = array('RIGAMAIL' => 4, 'TIPOMAIL' => "Mail ai responsabili procedimento di conferma dell'annullamento della richiesta OnLine", 'CHIAVE' => "ANNULLAMENTO");
        $this->mailTemplates[5] = array('RIGAMAIL' => 5, 'TIPOMAIL' => "Mail al richiedente di conferma inoltro richiesta di integrazione", 'CHIAVE' => "INT_RICH");
        $this->mailTemplates[6] = array('RIGAMAIL' => 6, 'TIPOMAIL' => "Mail ai responsabili procedimento di conderma inoltro richiesta di Integrazione", 'CHIAVE' => "INT_RESP");
        $this->mailTemplates[7] = array('RIGAMAIL' => 7, 'TIPOMAIL' => "Mail all'ente terzo di conferma inoltro parere espresso", 'CHIAVE' => "ARICPARERI");
        $this->mailTemplates[8] = array('RIGAMAIL' => 8, 'TIPOMAIL' => "Mail ai responsabili procedimento di inoltro parere espresso", 'CHIAVE' => "AENTITERZI");
        $this->mailTemplates[9] = array('RIGAMAIL' => 9, 'TIPOMAIL' => "Mail notifica al nuovo esibente della presa in carico della richiesta OnLine", 'CHIAVE' => "CAMBIOESIB_ESIBENTE");
        $this->mailTemplates[10] = array('RIGAMAIL' => 10, 'TIPOMAIL' => "Mail notifica al dichiarante del cambio esibente della richiesta OnLine", 'CHIAVE' => "CAMBIOESIB_DICH");

        $this->mailTemplates[11] = array('RIGAMAIL' => 11, 'TIPOMAIL' => "Mail notifica assegnazione inserimento integrazioni a soggetto esterno alla richiesta OnLine", 'CHIAVE' => "ACL_ASSEGNATARIO_INTEG");
        $this->mailTemplates[12] = array('RIGAMAIL' => 12, 'TIPOMAIL' => "Mail notifica al dichiarante dell'assegnazione integrazioni a soggetto esterno alla richiesta OnLine", 'CHIAVE' => "ACL_DICH_INTEG");

        $this->mailTemplates[13] = array('RIGAMAIL' => 13, 'TIPOMAIL' => "Mail notifica assegnazione passo a soggetto esterno alla richiesta OnLine", 'CHIAVE' => "ACL_ASSEGNATARIO_PASSO");
        $this->mailTemplates[14] = array('RIGAMAIL' => 14, 'TIPOMAIL' => "Mail notifica al dichiarante dell'assegnazione passo a soggetto esterno alla richiesta OnLine", 'CHIAVE' => "ACL_DICH_PASSO");

        $this->mailTemplates[15] = array('RIGAMAIL' => 15, 'TIPOMAIL' => "Mail notifica assegnazione visualizzazione a soggetto esterno della richiesta OnLine", 'CHIAVE' => "ACL_ASSEGNATARIO_VISUAL");
        $this->mailTemplates[16] = array('RIGAMAIL' => 16, 'TIPOMAIL' => "Mail notifica al dichiarante dell'assegnazione visualizzazione a soggetto esterno della richiesta OnLine", 'CHIAVE' => "ACL_DICH_VISUAL");
    }

    public function PopolaTabellaTipoMail() {
        $ita_grid01 = new TableView(
                $this->gridTemplateMail, array('arrayTable' => $this->mailTemplates,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('50');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridTemplateMail);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridTemplateMail);
        }
    }

    public function CaricaParametriFO() {
        $this->paramFO = array();
        $this->paramFO[] = array(
            'TIPOPARM' => 'MAIL',
            'DB' => 'PRAM',
            'DESPARM' => 'Gestione Parametri Mail',
            'LABEL' => array(
                'ITA_MAIL_ACCOUNT' => 'E-Mail Mittente',
                'ITA_NAME_ACCOUNT' => 'Nome Mittente',
                'ITA_SMTP_HOST' => 'Server SMTP',
                'ITA_SMTP_USER' => 'Nome Utente Server',
                'ITA_SMTP_PASSWORD' => 'Password Utente Server',
                'ITA_PORT' => 'Porta',
                'ITA_SMTP_SECURE' => 'Protocollo Sicurezza (ssl)'
            ),
            'ATTRIBUTI' => array(
                'ITA_MAIL_ACCOUNT' => 'text',
                'ITA_NAME_ACCOUNT' => 'text',
                'ITA_SMTP_HOST' => 'text',
                'ITA_SMTP_USER' => 'text',
                'ITA_SMTP_PASSWORD' => 'password',
                'ITA_PORT' => 'text',
                'ITA_SMTP_SECURE' => 'select'
            ),
            'SELECT' => array(
                array('ITA_SMTP_SECURE' => 'tls|ssl')
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'ERROR',
            'DB' => 'PRAM',
            'DESPARM' => 'Mail CED per Invio Segnalazioni',
            'LABEL' => array(
                'ITA_MAIL_ERROR' => 'Indirizzo E-mail',
                'ITA_MAIL_ERROR_ACCOUNT' => 'E-Mail Mittente',
                'ITA_NAME_ERROR_ACCOUNT' => 'Nome Mittente',
                'ITA_SMTP_ERROR_HOST' => 'Server SMTP',
                'ITA_SMTP_ERROR_USER' => 'Nome Utente Server',
                'ITA_SMTP_ERROR_PASSWORD' => 'Password Utente Server',
                'ITA_PORT_ERROR' => 'Porta',
                'ITA_SMTP_ERROR_SECURE' => 'Protocollo Sicurezza (ssl) '
            ),
            'ATTRIBUTI' => array(
                'ITA_MAIL_ERROR' => 'text',
                'ITA_MAIL_ERROR_ACCOUNT' => 'text',
                'ITA_NAME_ERROR_ACCOUNT' => 'text',
                'ITA_SMTP_ERROR_HOST' => 'text',
                'ITA_SMTP_ERROR_USER' => 'text',
                'ITA_SMTP_ERROR_PASSWORD' => 'password',
                'ITA_PORT_ERROR' => 'text',
                'ITA_SMTP_ERROR_SECURE' => 'select'
            ),
            'SELECT' => array(
                array('ITA_SMTP_ERROR_SECURE' => 'tls|ssl')
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'BLOCK',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri E-mail Invio Pratica',
            'LABEL' => array(
                'BLOCK_MAIL_RESP' => 'Blocca Mail Responsabile',
                'BLOCK_MAIL_RICH' => 'Blocca Mail Richiedente',
                'BLOCK_INVIO_INFO' => 'Blocca invio file .info',
                'BLOCK_MAIL_STARWEB' => 'Blocca Mail Starweb',
                'BLOCK_MAX_FILESIZE' => 'Grandezza Massima(MB) Allegati',
                'BLOCK_INOLTRO_CW' => 'Notifica Invio Richiesta Cityware',
                'BLOCK_INIZIO_RICHIESTA' => 'Notifica Inizio Richiesta',
                'BLOCK_INTEGRAZIONI' => 'Blocco integrazioni di pratiche chiuse',
                'BLOCK_ALLEGATI_RICHIEDENTE' => 'Blocco invio allegati al Richiedente',
                'BLOCK_ANNULLA_RICHIESTA' => 'Blocco annullamento richiesta tramite busta',
                'BLOCK_INVIO_STARWEB' => 'Blocco invio starweb',
            ),
            'ATTRIBUTI' => array(
                'BLOCK_MAIL_RESP' => 'select',
                'BLOCK_MAIL_RICH' => 'select',
                'BLOCK_INVIO_INFO' => 'select',
                'BLOCK_MAIL_STARWEB' => 'select',
                'BLOCK_MAX_FILESIZE' => 'text',
                'BLOCK_INOLTRO_CW' => 'select',
                'BLOCK_INIZIO_RICHIESTA' => 'select',
                'BLOCK_INTEGRAZIONI' => 'select',
                'BLOCK_ALLEGATI_RICHIEDENTE' => 'select',
                'BLOCK_ANNULLA_RICHIESTA' => 'select',
                'BLOCK_INVIO_STARWEB' => 'select',
            ),
            'SELECT' => array(
                array('BLOCK_MAIL_RESP' => 'No|Si'),
                array('BLOCK_MAIL_RICH' => 'No|Si'),
                array('BLOCK_INVIO_INFO' => 'No|Si'),
                array('BLOCK_MAIL_STARWEB' => 'No|Si'),
                array('BLOCK_INOLTRO_CW' => 'No|Si'),
                array('BLOCK_INIZIO_RICHIESTA' => 'No|Si'),
                array('BLOCK_INTEGRAZIONI' => 'No|Si'),
                array('BLOCK_ALLEGATI_RICHIEDENTE' => 'No|Si'),
                array('BLOCK_ANNULLA_RICHIESTA' => 'No|Si'),
                array('BLOCK_INVIO_STARWEB' => 'No|Si'),
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'FIRMA',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri Firma Digitale',
            'LABEL' => array(
                'VERIFICA_FIRMA' => 'Verifica Firma Digitale',
                'VERIFICA_INTEGRITA_DRR' => 'Verifica Integrità Rapporto',
                'TIPO_VERIFICA_FIRMA' => 'Tipo Verifica Firma',
                'ENDPOINT_WS_ARUBA' => 'End Point web service ARSS',
                'NAMESPACE_WS_ARUBA' => 'Name Space web service ARSS',
                'TIMEOUT_WS_ARUBA' => 'Tempo Massimo attesa chiamate web service ARSS',
                'DEBUGLEVEL_WS_ARUBA' => 'Livello di debug per chiamate al ws',
                'URLDECOD_PAPERTOKEN_WS_ARUBA' => 'Url decodica papertoken',
            ),
            'ATTRIBUTI' => array(
                'VERIFICA_FIRMA' => 'select',
                'VERIFICA_INTEGRITA_DRR' => 'select',
                'TIPO_VERIFICA_FIRMA' => 'select',
                'ENDPOINT_WS_ARUBA' => 'text',
                'NAMESPACE_WS_ARUBA' => 'text',
                'TIMEOUT_WS_ARUBA' => 'text',
                'DEBUGLEVEL_WS_ARUBA' => 'text',
                'URLDECOD_PAPERTOKEN_WS_ARUBA' => 'text',
            ),
            'SELECT' => array(
                array('VERIFICA_FIRMA' => 'No|Si'),
                array('VERIFICA_INTEGRITA_DRR' => 'No|Si'),
                array('TIPO_VERIFICA_FIRMA' => 'j4sign|ARSS|DSS|j4sign-DSS'),
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'FIRMAREMOTA',
            'DB' => 'PRAM',
            'DESPARM' => 'Firma Remota Aruba',
            'LABEL' => array(
                'ENDPOINT_ARSS' => 'END POINT DEL WEB SERVICE ARSS',
                'NAMESPACE_ARSS' => 'NAME SPACE PER LE RICHIESTE AL WEB SERVICE ARSS',
                'TIMEOUT_ARSS' => 'TEMPO MASSIMO DI ATTESA PER LE CHIAMATE AL WEB SERVICE ARSS',
                'DEBUGLEVEL_ARSS' => 'LIVELLO DI ATTIVAZIONE DEBUG CHIAMATE',
                'DECODEURLPAPERTOKEN_ARSS' => 'URL DECODIFICA COORD. PAPERTOKEN',
                'DOMINIODEFAULT_ARSS' => 'DEFAULT GENERALE DOMINIO DI FIRMA',
            ),
            'ATTRIBUTI' => array(
                'ENDPOINT_ARSS' => 'text',
                'NAMESPACE_ARSS' => 'text',
                'TIMEOUT_ARSS' => 'text',
                'DEBUGLEVEL_ARSS' => 'text',
                'DECODEURLPAPERTOKEN_ARSS' => 'text',
                'DOMINIODEFAULT_ARSS' => 'text',
            ),
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'CNA',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri inoltro agenzia CNA',
            'LABEL' => array(
                'AGENZIA_CNA_ATTIVA' => 'Attiva inoltro a CNA',
                'AGENZIA_CNA_WSURL' => 'end-point ws notifica',
                'AGENZIA_CNA_WSWSDL' => 'wsdl ws notifica',
                'AGENZIA_CNA_WSUSER' => 'utente accesso ws',
                'AGENZIA_CNA_WSPASSWD' => 'password acesso ws',
                'AGENZIA_CNA_ENDPOINTWS' => 'end-point ws pratiche',
                'AGENZIA_CNA_SECRETWORD' => 'parola segreta'
            ),
            'ATTRIBUTI' => array(
                'AGENZIA_CNA_ATTIVA' => 'select',
                'AGENZIA_CNA_WSURL' => 'text',
                'AGENZIA_CNA_WSWSDL' => 'text',
                'AGENZIA_CNA_WSUSER' => 'text',
                'AGENZIA_CNA_WSPASSWD' => 'password',
                'AGENZIA_CNA_ENDPOINTWS' => 'text',
                'AGENZIA_CNA_SECRETWORD' => 'text'
            ),
            'SELECT' => array(
                array('AGENZIA_CNA_ATTIVA' => 'No|Si')
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'PROTOCOLLO',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri Protocollazione Remota',
            'LABEL' => array(
                'PROTOCOLLO_REMOTO' => 'Attiva Protocollo Remoto',
                'TIPO_PROTOCOLLO' => 'Tipo Protocollo',
                'PROTOCOLLA_RAPPORTO' => 'Protocolla solo Rapporto',
                'MAX_FILESIZE_PROTALLEGATO' => 'Grandezza Massima Allegato da inviare al Protocollo (MB)',
                'FASCICOLAZIONE_REMOTA' => 'Attiva Fascicolazione Remota',
                'TIPO_COMUNICAZIONE_WS' => 'Tipo comunicazione con ws di protocollazione',
                'FIRST_CHOICE_MITTPROT' => 'Prima Preferenza Mittente',
                'SECOND_CHOICE_MITTPROT' => 'Seconda Preferenza Mittente',
                'THIRD_CHOICE_MITTPROT' => 'Terza Preferenza Mittente',
            ),
            'ATTRIBUTI' => array(
                'PROTOCOLLO_REMOTO' => 'select',
                'TIPO_PROTOCOLLO' => 'select',
                'PROTOCOLLA_RAPPORTO' => 'select',
                'MAX_FILESIZE_PROTALLEGATO' => 'text',
                'FASCICOLAZIONE_REMOTA' => 'select',
                'TIPO_COMUNICAZIONE_WS' => 'select',
                'FIRST_CHOICE_MITTPROT' => 'text',
                'SECOND_CHOICE_MITTPROT' => 'text',
                'THIRD_CHOICE_MITTPROT' => 'text',
            ),
            'CLASSI' => array(
                'FIRST_CHOICE_MITTPROT' => 'ita-edit-lookup ita-readonly',
                'SECOND_CHOICE_MITTPROT' => 'ita-edit-lookup ita-readonly',
                'THIRD_CHOICE_MITTPROT' => 'ita-edit-lookup ita-readonly',
            ),
            'SELECT' => array(
                array('PROTOCOLLO_REMOTO' => 'No|Si'),
                array('TIPO_PROTOCOLLO' => 'Iride|Jiride|Paleo|Paleo4|Paleo41|Italsoft|Halley|Infor|Sici|HyperSic|Leonardo|Kibernetes|CiviliaNext'),
                array('PROTOCOLLA_RAPPORTO' => 'No|Si'),
                array('FASCICOLAZIONE_REMOTA' => 'No|Si'),
                array('TIPO_COMUNICAZIONE_WS' => 'Diretta|Differita'),
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'PRAVIS',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri Visualizzazione Elenco Richieste on-line',
            'LABEL' => array(
                'PRAVIS_SELECTSTATI' => 'Stati visibili su Select di ricerca',
            ),
            'ATTRIBUTI' => array(
                'PRAVIS_SELECTSTATI' => 'textarea',
            ),
            'HEADER' => "
                99 = Richieste in corso
                98 = Richieste annullate
                01 = Richieste inoltrate
                81 = Richieste inoltrate ad Agenzia
                91 = Richieste inviate per la comunicazione unica d'impresa
                02 = Richieste acquisite
                03 = Richieste chiuse
                PD = Richieste in attesa di protocollazione remota
                WITHATTACH = Richieste con allegati
                PROTOCOLLATE = Richieste protocollate
                ",
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'IRIDEWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Iride',
            'LABEL' => array(
                'WSIRIDEENDPOINT' => 'End Point',
                'WSIRIDEWSDL' => 'WSDL',
                'UTENTE' => 'Utente per le chiamate',
                'RUOLO' => 'Ruolo utente',
                'WSIRIDENAMESPACE' => 'Name Space',
                'USERNAME' => 'Utente',
                'PASSWORD' => 'Password',
                'WSIRIDETIMEOUT' => 'Time Out',
                'TIPODOCUMENTO' => 'Tipo Documento',
                'AGGIORNAANAGRAFICHE' => 'Aggiorna Anagrafiche',
                'CODICEAMMINISTRAZIONE' => 'Codice Amministrazione',
                'CODICEAOO' => 'Codice AOO',
                'DENOM_DEST_SEP' => 'Denominazione Destinatario separata (cognome e nome)',
            ),
            'ATTRIBUTI' => array(
                'WSIRIDEENDPOINT' => 'text',
                'WSIRIDEWSDL' => 'text',
                'UTENTE' => 'text',
                'RUOLO' => 'text',
                'WSIRIDENAMESPACE' => 'text',
                'USERNAME' => 'text',
                'PASSWORD' => 'password',
                'WSIRIDETIMEOUT' => 'text',
                'TIPODOCUMENTO' => 'text',
                'AGGIORNAANAGRAFICHE' => 'text',
                'CODICEAMMINISTRAZIONE' => 'text',
                'CODICEAOO' => 'text',
                'DENOM_DEST_SEP' => 'select'
            ),
            'SELECT' => array(
                array('DENOM_DEST_SEP' => 'No|Si'),
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'JIRIDEWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Jiride',
            'LABEL' => array(
                'WSJIRIDEENDPOINT' => 'End Point',
                'WSJIRIDEWSDL' => 'WSDL',
                'WSJIRIDEUTENTE' => 'Utente per le chiamate',
                'WSJIRIDERUOLO' => 'Ruolo utente',
                'WSJIRIDENAMESPACE' => 'Name Space',
                'WSJIRIDEUSERNAME' => 'Utente',
                'WSJIRIDEPASSWORD' => 'Password',
                'WSJIRIDETIMEOUT' => 'Time Out',
                'WSJIRIDETIPODOCUMENTO' => 'Tipo Documento',
                'WSJIRIDEAGGIORNAANAGRAFICHE' => 'Aggiorna Anagrafiche',
                'WSJIRIDECODICEAMMINISTRAZIONE' => 'Codice Amministrazione',
                'WSJIRIDECODICEAOO' => 'Codice AOO',
                'WSJIRIDEMEZZOINVIO' => 'Mezzo Invio'
            ),
            'ATTRIBUTI' => array(
                'WSJIRIDEENDPOINT' => 'text',
                'WSJIRIDEWSDL' => 'text',
                'WSJIRIDEUTENTE' => 'text',
                'WSJIRIDERUOLO' => 'text',
                'WSJIRIDENAMESPACE' => 'text',
                'WSJIRIDEUSERNAME' => 'text',
                'WSJIRIDEPASSWORD' => 'password',
                'WSJIRIDETIMEOUT' => 'text',
                'WSJIRIDETIPODOCUMENTO' => 'text',
                'WSJIRIDEAGGIORNAANAGRAFICHE' => 'text',
                'WSJIRIDECODICEAMMINISTRAZIONE' => 'text',
                'WSJIRIDECODICEAOO' => 'text',
                'WSJIRIDEMEZZOINVIO' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'PALEOWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Paleo',
            'LABEL' => array(
                'WSPALEOENDPOINT' => 'End Point WS',
                'WSPALEOWSDL' => 'WSDL',
                'WSPALEOCODAMM' => 'Codice Amministrazione',
                'WSPALEOUSERNAME' => 'Utente',
                'WSPALEOPASSWORD' => 'Password',
                'WSPALEOUNITAOPE' => 'Unità Operativa',
                'WSPALEORUOLO' => 'Ruolo operatore',
                'WSPALEOCOGNOME' => 'Cognome Operatore',
                'WSPALEONOME' => 'Nome Operatore',
                'WSPALEOTIMEOUT' => 'Time Out'
            ),
            'ATTRIBUTI' => array(
                'WSPALEOENDPOINT' => 'text',
                'WSPALEOWSDL' => 'text',
                'WSPALEOCODAMM' => 'text',
                'WSPALEOUSERNAME' => 'text',
                'WSPALEOPASSWORD' => 'password',
                'WSPALEOUNITAOPE' => 'text',
                'WSPALEORUOLO' => 'text',
                'WSPALEOCOGNOME' => 'text',
                'WSPALEONOME' => 'text',
                'WSPALEOTIMEOUT' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'PALEO4WSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Paleo Versione 4',
            'LABEL' => array(
                'WSPALEO4ENDPOINT' => 'End Point WS',
                'WSPALEO4WSDL' => 'WSDL',
                'WSPALEO4CODAMM' => 'Codice Amministrazione',
                'WSPALEO4USERNAME' => 'Utente',
                'WSPALEO4PASSWORD' => 'Password',
                'WSPALEO4UNITAOPE' => 'Unità Operativa',
                'WSPALEO4RUOLO' => 'Ruolo operatore',
                'WSPALEO4COGNOME' => 'Cognome Operatore',
                'WSPALEO4NOME' => 'Nome Operatore',
                'WSPALEO4TIMEOUT' => 'Time Out',
                'WSPALEO4CURLCIPHER' => 'cURL ssl cipher (vedi comando #openssl ciphers \'ALL:eNULL\')'
            ),
            'ATTRIBUTI' => array(
                'WSPALEO4ENDPOINT' => 'text',
                'WSPALEO4WSDL' => 'text',
                'WSPALEO4CODAMM' => 'text',
                'WSPALEO4USERNAME' => 'text',
                'WSPALEO4PASSWORD' => 'password',
                'WSPALEO4UNITAOPE' => 'text',
                'WSPALEO4RUOLO' => 'text',
                'WSPALEO4COGNOME' => 'text',
                'WSPALEO4NOME' => 'text',
                'WSPALEO4TIMEOUT' => 'text',
                'WSPALEO4CURLCIPHER' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'ITALSOFTWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Italsoft',
            'LABEL' => array(
                'WSITALSOFTENDPOINT' => 'End Point WS',
                'WSITALSOFTWSDL' => 'WSDL',
                'WSITALSOFTDOMAIN' => 'Dominio',
                'WSITALSOFTUSERNAME' => 'Utente',
                'WSITALSOFTPASSWORD' => 'Password',
                'WSITALSOFTTIMEOUT' => 'Time Out',
                'WSFASCICOLOENDPOINT' => 'End Point WS Fascicolo',
                'WSFASCICOLOWSDL' => 'WSDL WS Fascicolo'
            ),
            'ATTRIBUTI' => array(
                'WSITALSOFTENDPOINT' => 'text',
                'WSITALSOFTWSDL' => 'text',
                'WSITALSOFTDOMAIN' => 'text',
                'WSITALSOFTUSERNAME' => 'text',
                'WSITALSOFTPASSWORD' => 'password',
                'WSITALSOFTTIMEOUT' => 'text',
                'WSFASCICOLOENDPOINT' => 'text',
                'WSFASCICOLOWSDL' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'HALLEYWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Protocollazione Halley - E-Lios',
            'LABEL' => array(
                'WSHALLEYENDPOINT' => 'End Point WS',
                'WSHALLEYWSDL' => 'WSDL',
                'WSHALLEYENDPOINTDIZ' => 'End Point WS Dizionario',
                'WSHALLEYWSDLDIZ' => 'WSDL Dizionario',
                'WSHALLEYUSERNAME' => 'Utente',
                'WSHALLEYPASSWORD' => 'Password',
                'WSHALLEYCODICEAOO' => 'Codice AOO',
                'WSHALLEYCODICEDITTA' => 'Codice Ditta'
            ),
            'ATTRIBUTI' => array(
                'WSHALLEYENDPOINT' => 'text',
                'WSHALLEYWSDL' => 'text',
                'WSHALLEYENDPOINTDIZ' => 'text',
                'WSHALLEYWSDLDIZ' => 'text',
                'WSHALLEYUSERNAME' => 'text',
                'WSHALLEYPASSWORD' => 'password',
                'WSHALLEYCODICEAOO' => 'text',
                'WSHALLEYCODICEDITTA' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'JIRIDEWSFASCICOLICONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Fascicolazione Jiride',
            'LABEL' => array(
                'WSJIRIDEFASCICOLIENDPOINT' => 'End Point',
                'WSJIRIDEFASCICOLIWSDL' => 'WSDL',
                'WSJIRIDEFASCICOLIUTENTE' => 'Utente per le chiamate',
                'WSJIRIDEFASCICOLIRUOLO' => 'Ruolo utente',
                'WSJIRIDEFASCICOLICODICEAMMINISTRAZIONE' => 'Codice Amministrazione',
                'WSJIRIDEFASCICOLICODICEAOO' => 'Codice AOO'
            ),
            'ATTRIBUTI' => array(
                'WSJIRIDEFASCICOLIENDPOINT' => 'text',
                'WSJIRIDEFASCICOLIWSDL' => 'text',
                'WSJIRIDEFASCICOLIUTENTE' => 'text',
                'WSJIRIDEFASCICOLIRUOLO' => 'text',
                'WSJIRIDEFASCICOLICODICEAMMINISTRAZIONE' => 'text',
                'WSJIRIDEFASCICOLICODICEAOO' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'INFORWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Infor',
            'LABEL' => array(
                'WSINFORENDPOINT' => 'End Point',
                'WSINFORWSDL' => 'WSDL',
                'WSINFORUTENTE' => 'Utente per le chiamate',
                'WSINFORUTENTEINTERNO' => 'Utente corrispondente interno',
            ),
            'ATTRIBUTI' => array(
                'WSINFORENDPOINT' => 'text',
                'WSINFORWSDL' => 'text',
                'WSINFORUTENTE' => 'text',
                'WSINFORUTENTEINTERNO' => 'text',
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'HYPERSICWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Hyper-Sic',
            'LABEL' => array(
                'WSHYPERSICENDPOINT' => 'End Point',
                'WSHYPERSICWSDL' => 'WSDL',
                'WSHYPERSICNAMESPACE' => 'Name Space',
                'WSHYPERSICTIMEOUT' => 'Time Out',
                'WSHYPERSICUSERNAME' => 'Utente',
                'WSHYPERSICPASSWORD' => 'Password',
            ),
            'ATTRIBUTI' => array(
                'WSHYPERSICENDPOINT' => 'text',
                'WSHYPERSICWSDL' => 'text',
                'WSHYPERSICNAMESPACE' => 'text',
                'WSHYPERSICTIMEOUT' => 'text',
                'WSHYPERSICUSERNAME' => 'text',
                'WSHYPERSICPASSWORD' => 'text',
            )
        );



        $this->paramFO[] = array(
            'TIPOPARM' => 'SICIWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Sici-Studio-K',
            'LABEL' => array(
                'SICIWSENDPOINT' => 'End Point',
                'SICIWSWSDL' => 'WSDL',
                'SICIWSAPPLICATIVO' => 'Applicativo',
                'SICIWSENTE' => 'Ente',
                'SICIWSCODUTE' => 'Utente',
                'SICIWSPASSWORD' => 'Password',
                'SICIWSNAMESPACES' => 'Name Spaces',
                'SICIWSNAMESPACE' => 'Name Space Soap Action',
                'SICIWSCODICEAMMINISTRAZIONE' => 'Codice Amministrazione',
                'SICIWSCODICEAOO' => 'Codice AOO',
            ),
            'ATTRIBUTI' => array(
                'SICIWSENDPOINT' => 'text',
                'SICIWSWSDL' => 'text',
                'SICIWSAPPLICATIVO' => 'text',
                'SICIWSENTE' => 'text',
                'SICIWSCODUTE' => 'text',
                'SICIWSPASSWORD' => 'text',
                'SICIWSNAMESPACES' => 'text',
                'SICIWSNAMESPACE' => 'text',
                'SICIWSCODICEAMMINISTRAZIONE' => 'text',
                'SICIWSCODICEAOO' => 'text',
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'LEONARDOWSCONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Leonardo',
            'LABEL' => array(
                'LEONARDOWSENDPOINT' => 'End Point',
                'LEONARDOWSWSDL' => 'WSDL',
                'LEONARDOWSENTE' => 'Codice Ente',
                'LEONARDOWSCODICEAOO' => 'Codice AOO',
                'LEONARDOWSCODUTE' => 'Utente',
                'LEONARDOWSPASSWORD' => 'Password',
            ),
            'ATTRIBUTI' => array(
                'LEONARDOWSENDPOINT' => 'text',
                'LEONARDOWSWSDL' => 'text',
                'LEONARDOWSENTE' => 'text',
                'LEONARDOWSCODICEAMMINISTRAZIONE' => 'text',
                'LEONARDOWSCODUTE' => 'text',
                'LEONARDOWSPASSWORD' => 'text',
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'WSADS',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri inoltro Iscrizione Scuola (ADS)',
            'LABEL' => array(
                'SCUOLA_ADS_WSURL' => 'ENDPOINT WS ADS',
                'SCUOLA_ADS_WSWSDL' => 'WSDL WS SOL',
                'SCUOLA_ADS_TIMEOUT' => 'Time Out',
                'SCUOLA_ADS_NAMESPACE' => 'Name Space'
            ),
            'ATTRIBUTI' => array(
                'SCUOLA_ADS_WSURL' => 'text',
                'SCUOLA_ADS_WSWSDL' => 'text',
                'SCUOLA_ADS_TIMEOUT' => 'text',
                'SCUOLA_ADS_NAMESPACE' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'CUSTOMCLASS',
            'DB' => 'PRAM',
            'DESPARM' => 'Parametri per CustomClass',
            'LABEL' => array(
                'CUSTOMCLASS_LOCAL_PATH' => 'Path locale',
                'CUSTOMCLASS_WS_URL' => 'WS URI',
                'CUSTOMCLASS_WS_USER' => 'WS utente',
                'CUSTOMCLASS_WS_PASS' => 'WS password',
                'CUSTOMCLASS_WS_DOMAIN' => 'WS dominio'
            ),
            'ATTRIBUTI' => array(
                'CUSTOMCLASS_LOCAL_PATH' => 'text',
                'CUSTOMCLASS_WS_URL' => 'text',
                'CUSTOMCLASS_WS_USER' => 'text',
                'CUSTOMCLASS_WS_PASS' => 'password',
                'CUSTOMCLASS_WS_DOMAIN' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'IRIDEWSFASCICOLICONNECTION',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Fascicolazione Iride',
            'LABEL' => array(
                'WSIRIDEFASCICOLIENDPOINT' => 'End Point',
                'WSIRIDEFASCICOLIWSDL' => 'WSDL',
                'WSIRIDEFASCICOLIUTENTE' => 'Utente per le chiamate',
                'WSIRIDEFASCICOLIRUOLO' => 'Ruolo utente',
                'WSIRIDEFASCICOLICODICEAMMINISTRAZIONE' => 'Codice Amministrazione',
                'WSIRIDEFASCICOLICODICEAOO' => 'Codice AOO'
            ),
            'ATTRIBUTI' => array(
                'WSIRIDEFASCICOLIENDPOINT' => 'text',
                'WSIRIDEFASCICOLIWSDL' => 'text',
                'WSIRIDEFASCICOLIUTENTE' => 'text',
                'WSIRIDEFASCICOLIRUOLO' => 'text',
                'WSIRIDEFASCICOLICODICEAMMINISTRAZIONE' => 'text',
                'WSIRIDEFASCICOLICODICEAOO' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'KIBERNETESWSPROTOCOLLAZIONE',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Kibernetes',
            'LABEL' => array(
                'WSKIBERNETSPROTOCOLLOENDPOINT' => 'End Point',
                'WSKIBERNETSPROTOCOLLOWSDL' => 'WSDL',
                'WSKIBERNETSPROTOCOLLOUTENTE' => 'Utente per le chiamate',
                'WSKIBERNETSPROTOCOLLOPASSWORD' => 'Password per le chiamate',
                'WSKIBERNETSPROTOCOLLOCODICEUO' => 'Codice UO',
                'WSKIBERNETSPROTOCOLLOCODICEISTATAMMINISTRAZIONE' => 'Codice Istat Amministrazione',
                'WSKIBERNETSPROTOCOLLOCODICEFUNZIONARIO' => 'Funzionario'
            ),
            'ATTRIBUTI' => array(
                'WSKIBERNETSPROTOCOLLOENDPOINT' => 'text',
                'WSKIBERNETSPROTOCOLLOWSDL' => 'text',
                'WSKIBERNETSPROTOCOLLOUTENTE' => 'text',
                'WSKIBERNETSPROTOCOLLOPASSWORD' => 'text',
                'WSKIBERNETSPROTOCOLLOCODICEUO' => 'text',
                'WSKIBERNETSPROTOCOLLOCODICEISTATAMMINISTRAZIONE' => 'text',
                'WSKIBERNETSPROTOCOLLOCODICEFUNZIONARIO' => 'text'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'CIVILIANEXTWSPROTOCOLLAZIONE',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Protocollazione Civilia-Next',
            'LABEL' => array(
                'CIVILIANEXTCLIENTID' => 'Client Identificator',
                'CIVILIANEXTCLIENTSECRET' => 'Client Secret',
                'CIVILIANEXTURLACCESSTOKEN' => 'Url Access Token',
                'CIVILIANEXTSCOPE' => 'Scope',
                'CIVILIANEXTENDPOINT' => 'End Point',
                'CIVILIANEXTCODORGANIGRAMMA' => 'Codice Organigramma',
                'CIVILIANEXTIDOPERATORE' => 'ID Operatore',
            ),
            'ATTRIBUTI' => array(
                'CIVILIANEXTCLIENTID' => 'text',
                'CIVILIANEXTCLIENTSECRET' => 'text',
                'CIVILIANEXTURLACCESSTOKEN' => 'text',
                'CIVILIANEXTSCOPE' => 'text',
                'CIVILIANEXTENDPOINT' => 'text',
                'CIVILIANEXTCODORGANIGRAMMA' => 'text',
                'CIVILIANEXTIDOPERATORE' => 'text',
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'ACQUISIZIONEAUTOMATICA',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Acquisizione Automatica',
            'LABEL' => array(
                'ACQAUT' => 'Acquisizione Automatica dopo inoltro',
                'ACQAUTENDPOINT' => 'End Point',
                'ACQAUTWSDL' => 'Link Wsdl',
                'ACQAUTUSER' => 'Utente',
                'ACQAUTPWD' => 'Password',
            ),
            'ATTRIBUTI' => array(
                'ACQAUT' => 'select',
                'ACQAUTENDPOINT' => 'text',
                'ACQAUTWSDL' => 'text',
                'ACQAUTUSER' => 'text',
                'ACQAUTPWD' => 'text',
            ),
            'SELECT' => array(
                array('ACQAUT' => 'No|Si'),
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'QWSCATASTO',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Catasto(QWS)',
            'LABEL' => array(
                'QWSENDPOINT' => 'End Point',
                'QWSPASSWORD' => 'Password',
            ),
            'ATTRIBUTI' => array(
                'QWSENDPOINT' => 'text',
                'QWSPASSWORD' => 'text',
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'RICHIESTEWSSOAP',
            'DB' => 'PRAM',
            'DESPARM' => 'Gestione Parametri per Richieste inserite tramite WS SOAP',
            'LABEL' => array(
                'RICHIESTEWSSOAPBLOCCOMAIL' => 'Blocca invio mail al responsabile',
                'RICHIESTEWSSOAPBLOCCOPROT' => 'Blocca protocollazione'
            ),
            'ATTRIBUTI' => array(
                'RICHIESTEWSSOAPBLOCCOMAIL' => 'select',
                'RICHIESTEWSSOAPBLOCCOPROT' => 'select'
            ),
            'SELECT' => array(
                array('RICHIESTEWSSOAPBLOCCOMAIL' => 'No|Si'),
                array('RICHIESTEWSSOAPBLOCCOPROT' => 'No|Si')
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'WSDOMUS',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri WS Pratiche Domus',
            'LABEL' => array(
                'DOMUS_URI' => 'URI',
                'DOMUS_WSDL' => 'WSDL',
                'DOMUS_ACTION' => 'Action',
                'DOMUS_NAMESPACE' => 'Namespace',
                'DOMUS_DOM' => 'NS dom',
                'DOMUS_DOM1' => 'NS dom1',
                'DOMUS_USERNAME' => 'Username',
                'DOMUS_PASSWORD' => 'Password'
            ),
            'ATTRIBUTI' => array(
                'DOMUS_URI' => 'text',
                'DOMUS_WSDL' => 'text',
                'DOMUS_ACTION' => 'text',
                'DOMUS_NAMESPACE' => 'text',
                'DOMUS_DOM' => 'text',
                'DOMUS_DOM1' => 'text',
                'DOMUS_USERNAME' => 'text',
                'DOMUS_PASSWORD' => 'password'
            )
        );
        $this->paramFO[] = array(
            'TIPOPARM' => 'WSAGENTFO_DEFAULTS',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri ws pratiche FO',
            'LABEL' => array(
                'CTRRICHIESTE_DEFAULT_STATO' => 'Default Stato richiesta in caso di estrazione:'
            ),
            'ATTRIBUTI' => array(
                'CTRRICHIESTE_DEFAULT_STATO' => 'select'
            ),
            'SELECT' => array(
                array('CTRRICHIESTE_DEFAULT_STATO' => 'TUTTE|ACQUISITE_BO|NON_ACQUISITE_BO')
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'LIMITIUPLOAD',
            'DB' => 'PRAM',
            'DESPARM' => 'Gestione dei limiti in upload',
            'LABEL' => array(
                'LIMUPL_SINGOLO' => 'Dimensione massima singolo file (MB)',
                'LIMUPL_MULTIUPL' => 'Dimensione massima passo multi-upload (MB)'
            ),
            'ATTRIBUTI' => array(
                'LIMUPL_SINGOLO' => 'text',
                'LIMUPL_MULTIUPL' => 'text'
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'PAGOPA',
            'DB' => 'ITAFO',
            'DESPARM' => 'Parametri Pago PA',
            'LABEL' => array(
                'PAGOPA_MODULO_URI' => 'Url Modulo',
                'PAGOPA_MODULO_URI_LOGIN' => 'Url Modulo per Login',
                'PAGOPA_MODULO_USER' => 'Utente',
                'PAGOPA_MODULO_PWD' => 'Password',
                'PAGOPA_MODULO_DOMAIN' => 'Codice Ditta',
            ),
            'ATTRIBUTI' => array(
                'PAGOPA_MODULO_URI' => 'text',
                'PAGOPA_MODULO_URI_LOGIN' => 'text',
                'PAGOPA_MODULO_USER' => 'text',
                'PAGOPA_MODULO_PWD' => 'text',
                'PAGOPA_MODULO_DOMAIN' => 'text',
            )
        );

        $this->paramFO[] = array(
            'TIPOPARM' => 'ACL',
            'DB' => 'ITAFO',
            'DESPARM' => 'Condivisione Accessi',
//            'DESPARM' => 'Parametri Condivisione Accessi Pratiche OnLine',
            'LABEL' => array(
                'ACL_ATTIVAZIONE' => 'Attivazione Condivisione Accessi',
                'ACL_CAMBIO_ESIBENTE' => 'Attivazione Cambio Esibente',
                'ACL_INTEGRAZIONE' => 'Attivazione Integrazione',
                'ACL_VISIBILITA' => 'Attivazione Visibilità richiesta OnLine',
                'ACL_GESTIONE_PASSO' => 'Attivazione Gestione Passo',
            ),
            'ATTRIBUTI' => array(
                'ACL_ATTIVAZIONE' => 'select',
                'ACL_CAMBIO_ESIBENTE' => 'select',
                'ACL_INTEGRAZIONE' => 'select',
                'ACL_VISIBILITA' => 'select',
                'ACL_GESTIONE_PASSO' => 'select',
            ),
            'SELECT' => array(
                array('ACL_ATTIVAZIONE' => 'No|Si'),
                array('ACL_CAMBIO_ESIBENTE' => 'No|Si'),
                array('ACL_INTEGRAZIONE' => 'No|Si'),
                array('ACL_VISIBILITA' => 'No|Si'),
                array('ACL_GESTIONE_PASSO' => 'No|Si'),
            )
        );
        
        $this->paramFO[] = array(
            'TIPOPARM' => 'SCADENZARICHIESTAONLINE',
            'DB' => 'ITAFO',
            'DESPARM' => 'Gestione Parametri Scadenza Richiesta OnLine',
            'LABEL' => array(
                'SCADRICATTIVA' => 'Attivare la gestione della scadenza delle richieste online',
                'SCADRICGIORNI' => 'Numero giorni scadenza',
            ),
            'ATTRIBUTI' => array(
                'SCADRICATTIVA' => 'select',
                'SCADRICGIORNI' => 'text',
            ),
            'SELECT' => array(
                array('SCADRICATTIVA' => 'No|Si'),
            )
        );
    }

    public function PopolaTabellaParametri() {
        $ita_grid01 = new TableView(
                $this->gridParametriFO, array('arrayTable' => $this->paramFO,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('50');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridParametriFO);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridParametriFO);
        }
    }

    public function parametriFO() {
        $classe = $this->paramFO[$this->rigaParametroFO]['TIPOPARM'];
        $label = $this->paramFO[$this->rigaParametroFO]['LABEL'];
        $attributi = $this->paramFO[$this->rigaParametroFO]['ATTRIBUTI'];
        $classi = $this->paramFO[$this->rigaParametroFO]['CLASSI'];
        $select = $this->paramFO[$this->rigaParametroFO]['SELECT'];
        $header = $this->paramFO[$this->rigaParametroFO]['HEADER'];
        $valoriSelect = array();
        $msg = array();
        switch ($this->paramFO[$this->rigaParametroFO]['DB']) {
            case 'PRAM':
                foreach ($label as $key => $lab) {
                    $sql = "SELECT * FROM ANAPAR WHERE PARCLA = '$classe' AND PARKEY = '" . $key . "'";
                    $parPram = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    $value = $parPram['PARVAL'];

                    /*
                     * Decodifca speciali del ruolo per le 3 scelte del mittente (Codice-Descrizione)
                     */
                    switch ($key) {
                        case "FIRST_CHOICE_MITTPROT":
                            if ($parPram['PARVAL']) {
                                $anaruo_rec = $this->praLib->GetAnaruo($parPram['PARVAL']);
                                $value = $parPram['PARVAL'] . "-" . $anaruo_rec['RUODES'];
                            }
                            break;
                        case "SECOND_CHOICE_MITTPROT":
                            if ($parPram['PARVAL']) {
                                $anaruo_rec = $this->praLib->GetAnaruo($parPram['PARVAL']);
                                $value = $parPram['PARVAL'] . "-" . $anaruo_rec['RUODES'];
                            }
                            break;
                        case "THIRD_CHOICE_MITTPROT":
                            if ($parPram['PARVAL']) {
                                $anaruo_rec = $this->praLib->GetAnaruo($parPram['PARVAL']);
                                $value = $parPram['PARVAL'] . "-" . $anaruo_rec['RUODES'];
                            }
                            break;
                    }

                    $size = '60';
                    if ($attributi[$key] == 'select') {
                        $valoriSelect[$key] = $parPram['PARVAL'];
                        $size = '';
                    }
                    $msgCampo = array('label' => array(
                            "value" => $lab, //$label[$parPram['PARKEY']],
                            "style" => "width:215px;",
                        ),
                        'id' => $this->nameForm . '_' . $key, //$parPram['PARKEY'],
                        'name' => $this->nameForm . '_' . $key, //$parPram['PARKEY'],
                        'type' => $attributi[$key],
                        'size' => $size,
                        'maxlength' => "300",
                        'value' => $value,
                        'class' => $classi[$key]);
                    if ($msgCampo['type'] == "textarea") {
                        $msgCampo['@textNode@'] = $value;
                    }
                    $msg[] = $msgCampo;
                }
                break;
            case 'ITAFO':
                foreach ($label as $key => $lab) {
                    $sql = "SELECT * FROM ENV_CONFIG WHERE CLASSE = '$classe' AND CHIAVE = '" . $key . "'";
                    $parFO = ItaDB::DBSQLSelect($this->ITAFO_DB, $sql, false);
                    $size = '60';
                    if ($attributi[$key] == 'select') {
                        $valoriSelect[$key] = $parFO['CONFIG'];
                        $size = '';
                    }
                    $msg[] = array('label' => array(
                            "value" => $lab, //$label[$parFO['CHIAVE']],
                            "style" => "width:215px;",
                        ),
                        'id' => $this->nameForm . '_' . $key, //$parFO['CHIAVE'],
                        'name' => $this->nameForm . '_' . $key, //$parFO['CHIAVE'],
                        'type' => $attributi[$key],
                        'size' => $size,
                        'maxlength' => "300",
                        'value' => $parFO['CONFIG']);
                }
                break;
        }
        Out::msgInput($this->paramFO[$this->rigaParametroFO]['DESPARM'] . ' - Classe ' . $classe, $msg, array(
            'Aggiorna' => array('id' => $this->nameForm . '_AggiornaParametriFO', 'model' => $this->nameForm, 'shortCut' => "f8")
                ), $this->nameForm . "_divGestione", "auto", "auto", true, $header, "", true
        );
        if ($select) {
            foreach ($select as $sel) {
                foreach ($sel as $key => $value) {
                    $singoli = explode('|', $value);
                    Out::select($this->nameForm . '_' . $key, 1, '', 0, '');
                    foreach ($singoli as $singolo) {
                        $selected = 0;
                        if ($valoriSelect[$key] == $singolo) {
                            $selected = 1;
                        }
                        Out::select($this->nameForm . '_' . $key, 1, $singolo, $selected, $singolo);
                    }
                }
            }
        }
    }

    public function CaricaVariabiliAmbiente() {
        $this->paramVA = array();

        $this->paramVA[] = array(
            'TIPOPARM' => 'CATASTO_SEZIONE',
            'LABEL' => array(
                'CATASTO_SEZIONE' => 'Vuoi gestire la Sezione nella raccolta dati catastali ?'
            ),
            'ATTRIBUTI' => array(
                'CATASTO_SEZIONE' => 'select'
            ),
            'SELECT' => array(
                array('CATASTO_SEZIONE' => 'si|no')
            )
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'CARICAMENTO_AUTOCERTIFICAZIONE',
            'LABEL' => array(
                'CARICAMENTO_AUTOCERTIFICAZIONE' => 'Permettere il caricamento di un\'autocertificazione nei passi di accorpamento richieste?'
            ),
            'ATTRIBUTI' => array(
                'CARICAMENTO_AUTOCERTIFICAZIONE' => 'select'
            ),
            'SELECT' => array(
                array('CARICAMENTO_AUTOCERTIFICAZIONE' => 'si|no')
            )
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'RICHIESTE_ACCORPABILI',
            'LABEL' => array(
                'RICHIESTE_ACCORPABILI' => 'Selezionare con quale stato le richieste sono accorpabili'
            ),
            'ATTRIBUTI' => array(
                'RICHIESTE_ACCORPABILI' => 'select'
            ),
            'SELECT' => array(
                array('RICHIESTE_ACCORPABILI' => 'in corso|inoltrate|entrambi')
            )
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'GIORNI_MANIFESTAZIONE',
            'LABEL' => array(
                'GIORNI_MANIFESTAZIONE' => "Giorni minimi di anticipo per manifestazioni.<br>(L'ultimo giorno non si ritiene idoneo per l'invio)"
            ),
            'ATTRIBUTI' => array(
                'GIORNI_MANIFESTAZIONE' => 'text'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'DISABILITA_CONFERMA_ANNULLA_RACCOLTA',
            'LABEL' => array(
                'DISABILITA_CONFERMA_ANNULLA_RACCOLTA' => "Disabilita la conferma dell'annullamento di una raccolta"
            ),
            'ATTRIBUTI' => array(
                'DISABILITA_CONFERMA_ANNULLA_RACCOLTA' => 'checkbox'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'DENOMINAZIONE_RESP_PRIVACY',
            'LABEL' => array(
                'DENOMINAZIONE_RESP_PRIVACY' => "Denominazione Responsabile Privacy"
            ),
            'ATTRIBUTI' => array(
                'DENOMINAZIONE_RESP_PRIVACY' => 'text'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'SEDE_RESP_PRIVACY',
            'LABEL' => array(
                'SEDE_RESP_PRIVACY' => "Sede Responsabile Privacy"
            ),
            'ATTRIBUTI' => array(
                'SEDE_RESP_PRIVACY' => 'text'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'MAIL_RESP_PRIVACY',
            'LABEL' => array(
                'MAIL_RESP_PRIVACY' => "Mail Responsabile Privacy"
            ),
            'ATTRIBUTI' => array(
                'MAIL_RESP_PRIVACY' => 'text'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'RICHIEDENTE_DATI_PRIVACY',
            'LABEL' => array(
                'RICHIEDENTE_DATI_PRIVACY' => "Richiedente per Dati Privacy"
            ),
            'ATTRIBUTI' => array(
                'RICHIEDENTE_DATI_PRIVACY' => 'text'
            ),
        );

        $this->paramVA[] = array(
            'TIPOPARM' => 'ATTIVA_SEMPRE_PASSO_DIRITTI',
            'LABEL' => array(
                'ATTIVA_SEMPRE_PASSO_DIRITTI' => "Attiva sempre il passo diritti di istruttoria"
            ),
            'ATTRIBUTI' => array(
                'ATTIVA_SEMPRE_PASSO_DIRITTI' => 'select'
            ),
            'SELECT' => array(
                array('ATTIVA_SEMPRE_PASSO_DIRITTI' => '|Si|No')
            )
        );

        foreach ($this->paramVA as $key => $parametro) {
            $chiave = $parametro['TIPOPARM'];
            $this->paramVA[$key]['DESPARMVA'] = praLibEnvVars::$SISTEM_ENVIRONMENT_VARIABLES[$chiave]['ENVDES'];
        }
    }

    public function PopolaTabellaAmbiente() {
        $ita_grid01 = new TableView(
                $this->gridParametriVA, array('arrayTable' => $this->paramVA,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageRows('50');
        $ita_grid01->setPageNum(1);
        TableView::enableEvents($this->gridParametriVA);
        if ($ita_grid01->getDataPage('json', true)) {
            TableView::enableEvents($this->gridParametriVA);
        }
    }

    public function parametriVA() {
        $classe = $this->paramVA[$this->rigaParametroVA]['TIPOPARM'];
        $label = $this->paramVA[$this->rigaParametroVA]['LABEL'];
        $attributi = $this->paramVA[$this->rigaParametroVA]['ATTRIBUTI'];
        $select = $this->paramVA[$this->rigaParametroVA]['SELECT'];
        $valoriSelect = array();
        $msg = array();
        foreach ($label as $key => $lab) {
            $sql = "SELECT * FROM VARIABILIAMBIENTE WHERE VARKEY = '$classe'";
            $parPram = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            $size = '60';
            if ($attributi[$classe] == 'select') {
                $valoriSelect[$parPram['VARKEY']] = $parPram['VARVAL'];
                $size = '';
            }

            $inputElement = array('label' => array(
                    "value" => $label[$classe],
                    "style" => "width:215px;",
                ),
                'id' => $this->nameForm . '_' . $classe,
                'name' => $this->nameForm . '_' . $classe,
                'type' => $attributi[$classe],
                'size' => $size,
                'maxlength' => "300"
            );

            switch ($attributi[$classe]) {
                case 'checkbox':
                    if ($parPram['VARVAL'] == 1) {
                        $inputElement['checked'] = 'true';
                    }
                    break;

                default:
                    $inputElement['value'] = $parPram['VARVAL'];
                    break;
            }

            $msg[] = $inputElement;
        }
        Out::msgInput($this->paramVA[$this->rigaParametroVA]['DESPARMVA'] . ' - Classe ' . $classe, $msg, array(
            'Aggiorna' => array('id' => $this->nameForm . '_AggiornaParametriVA', 'model' => $this->nameForm, 'shortCut' => "f8")
                ), $this->nameForm . "_divGestione", "auto", "auto", true, "", "", true
        );
        if ($select) {
            foreach ($select as $sel) {
                foreach ($sel as $key => $value) {
                    $singoli = explode('|', $value);
                    Out::select($this->nameForm . '_' . $key, 1, '', 0, '');
                    foreach ($singoli as $singolo) {
                        $selected = 0;
                        if ($valoriSelect[$key] == $singolo) {
                            $selected = 1;
                        }
                        Out::select($this->nameForm . '_' . $key, 1, $singolo, $selected, $singolo);
                    }
                }
            }
        }
    }

    function DecodAnaruo($Codice, $tipoRic, $retid) {
        $anaruo_rec = $this->praLib->GetAnaruo($Codice, $tipoRic);
        switch ($retid) {
            case "1":
                Out::valore($this->nameForm . "_FIRST_CHOICE_MITTPROT", $anaruo_rec['RUOCOD'] . "-" . $anaruo_rec['RUODES']);
                break;
            case "2":
                Out::valore($this->nameForm . "_SECOND_CHOICE_MITTPROT", $anaruo_rec['RUOCOD'] . "-" . $anaruo_rec['RUODES']);
                break;
            case "3":
                Out::valore($this->nameForm . "_THIRD_CHOICE_MITTPROT", $anaruo_rec['RUOCOD'] . "-" . $anaruo_rec['RUODES']);
                break;
        }
        return $anaruo_rec;
    }

    private function caricaGriglia($id, $opts, $page = null, $rows = null, $sidx = null, $sord = null) {
        TableView::clearGrid($id);

        $sortIndex = $sidx ?: $_POST['sidx'] ?: $this->gridOptions[$id]['sidx'] ?: '';
        $sortOrder = $sord ?: $_POST['sord'] ?: $this->gridOptions[$id]['sord'] ?: '';

        $gridObj = new TableView($id, $opts);
        $gridObj->setPageNum($page ?: $_POST['page'] ?: $_POST[$id]['gridParam']['page'] ?: 1);
        $gridObj->setPageRows($rows ?: $_POST['rows'] ?: $_POST[$id]['gridParam']['rowNum'] ?: 50);
        $gridObj->setSortIndex($sortIndex);
        $gridObj->setSortOrder($sortOrder);

        $this->gridOptions[$id] = array('sidx' => $sortIndex, 'sord' => $sortOrder);

        $elaboraRecords = 'elaboraRecords' . ucfirst(substr($id, strlen($this->nameForm) + 1));
        if (method_exists($this, $elaboraRecords)) {
            return $gridObj->getDataPageFromArray('json', $this->$elaboraRecords($gridObj->getDataArray()));
        }

        return $gridObj->getDataPage('json');
    }

    private function caricaGrigliaTipiFO() {
        $this->caricaGriglia($this->gridTipiFO, array(
            'arrayTable' => $this->arrTipiFO ?: array(),
            'rowIndex' => 'idx'
                ), 1, '1000');

        TableView::enableEvents($this->gridTipiFO);
    }

}
