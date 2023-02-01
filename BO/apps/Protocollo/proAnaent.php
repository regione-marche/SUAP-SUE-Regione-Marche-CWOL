<?php

/**
 *
 * Paramentri Protocollo
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    05.08.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once (ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
include_once ITA_BASE_PATH . '/apps/Documenti/docRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiGridMetaData.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaFormHelper.class.php';

function proAnaent() {
    $proAnaent = new proAnaent();
    $proAnaent->parseEvent();
    return;
}

class proAnaent extends itaModel {

    const MAX_ANAENT_REC = 61;

    /*
     * Non  usare il record 21-22-60-61 usati per l'albo.
     */

    public $PROT_DB;
    public $proLib;
    public $nameForm = "proAnaent";
    public $divGes = "proAnaent_divGestione";
    public $gridAccountMail = "proAnaent_gridAccountMail";
    public $gridMailSdi = "proAnaent_gridMailSdi";
    public $gridMailGiornaliero = "proAnaent_gridMailGiornaliero";
    public $gridSegnature = "proAnaent_gridSegnature";
    public $gridMailInoltro = "proAnaent_gridMailInoltro";
    public $gridTemplateMail = "proAnaent_gridTemplateMail";
    public $gridEstensioni = "proAnaent_gridEstensioni";
    public $account = array();
    public $mailSdi = array();
    public $appoggio;
    public $mailGiornaliero = array();
    public $posizioniSegnatura = array();
    public $elencoMailInoltro = array();
    public $arrTemplateMail = array();
    public $estensioniConservazione = array();
    public $utiGridMetaDataNameForm;

    function __construct() {
        parent::__construct();
// Apro il DB
        try {
            $this->proLib = new proLib();
            $this->PROT_DB = $this->proLib->getPROTDB();
            $this->account = App::$utente->getKey($this->nameForm . "_account");
            $this->appoggio = App::$utente->getKey($this->nameForm . "_appoggio");
            $this->mailSdi = App::$utente->getKey($this->nameForm . "_mailSdi");
            $this->mailGiornaliero = App::$utente->getKey($this->nameForm . "_mailGiornaliero");
            $this->posizioniSegnatura = App::$utente->getKey($this->nameForm . "_posizioniSegnatura");
            $this->elencoMailInoltro = App::$utente->getKey($this->nameForm . "_elencoMailInoltro");
            $this->arrTemplateMail = App::$utente->getKey($this->nameForm . "_arrTemplateMail");
            $this->estensioniConservazione = App::$utente->getKey($this->nameForm . "_estensioniConservazione");
            $this->utiGridMetaDataNameForm = App::$utente->getKey($this->nameForm . '_utiGridMetaDataNameForm');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_account", $this->account);
            App::$utente->setKey($this->nameForm . "_appoggio", $this->appoggio);
            App::$utente->setKey($this->nameForm . "_mailSdi", $this->mailSdi);
            App::$utente->setKey($this->nameForm . "_mailGiornaliero", $this->mailGiornaliero);
            App::$utente->setKey($this->nameForm . "_posizioniSegnatura", $this->posizioniSegnatura);
            App::$utente->setKey($this->nameForm . "_elencoMailInoltro", $this->elencoMailInoltro);
            App::$utente->setKey($this->nameForm . "_arrTemplateMail", $this->arrTemplateMail);
            App::$utente->setKey($this->nameForm . "_estensioniConservazione", $this->estensioniConservazione);
            App::$utente->setKey($this->nameForm . '_utiGridMetaDataNameForm', $this->utiGridMetaDataNameForm);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                //@TODO Nascondo campi inutilizzati. Da decidere che fare.
                Out::hide($this->nameForm . '_ENTDE_3_28_field');
                Out::hide($this->nameForm . '_ENTDE_4_28_field');
                Out::hide($this->nameForm . '_ENTDE_5_28_field');
                Out::hide($this->nameForm . '_ENTDE_1_29_field');
                Out::hide($this->nameForm . '_ENTDE_VAL_40_field');
                // <-- Fine HIDE
                $this->CreaCombo();
                $this->OpenForm();
                Out::hide($this->nameForm . '_BloccaProtocollo');
                include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';
                $menLib = new menLib();
                $gruppi = $menLib->getNomiGruppi($menLib->utenteAttuale);
                foreach ($gruppi as $gruppo) {
                    if ($gruppo == 'ITALSOFT' || $gruppo == 'italsoft') {
                        Out::show($this->nameForm . '_BloccaProtocollo');
                        break;
                    }
                }
                //$this->CaricaSubFormMetaData();//Sospeso non serve per namirial
                break;
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->gridAccountMail:
                        unset($this->account[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridAccountMail, $this->account);
                        break;
                    case $this->gridMailSdi:
                        unset($this->mailSdi[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridMailSdi, $this->mailSdi);
                        break;
                    case $this->gridMailGiornaliero:
                        unset($this->mailGiornaliero[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridMailGiornaliero, $this->mailGiornaliero);
                        break;
                    case $this->gridMailInoltro:
                        unset($this->elencoMailInoltro[$_POST['rowid']]);
                        $this->CaricaGriglia($this->gridMailInoltro, $this->elencoMailInoltro);
                        break;

                    case $this->gridEstensioni:
                        Out::msgQuestion("Cancellazione", "Confermi la Cancellazione dell'estensione selezionata?", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaCancellaExt', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaCancellaExt', 'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                }
                break;
            case 'addGridRow':
                switch ($_POST['id']) {
                    case $this->gridAccountMail:
                        emlRic::emlRicAccount($this->nameForm, '', 'Pop');
                        break;

                    case $this->gridMailSdi:
                        $valori[] = array(
                            'label' => array(
                                'value' => "Email:",
                                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_addMailSdi',
                            'name' => $this->nameForm . '_addMailSdi',
                            'type' => 'text',
                            'style' => 'margin:2px;width:380px;',
                            'value' => ''
                        );
                        Out::msgInput(
                                'Aggiunta Email in elenco Mail SDI.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAddMailSdi',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->gridMailGiornaliero:
                        $valori[] = array(
                            'label' => array(
                                'value' => "Email:",
                                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_addMailGiorno',
                            'name' => $this->nameForm . '_addMailGiorno',
                            'type' => 'text',
                            'style' => 'margin:2px;width:380px;',
                            'value' => ''
                        );
                        Out::msgInput(
                                'Aggiunta Email in elenco destinatari report giornaliero.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaAddMailGiornaliero',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->gridMailInoltro:
                        emlRic::emlRicAccount($this->nameForm, '', 'Inoltro');
                        break;

                    case $this->gridEstensioni:
                        $this->InputEstensione();
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridTemplateMail:
                        $rigaMail = $_POST['rowid'];
                        if ($rigaMail) {
                            $anaent_rec = $this->proLib->GetAnaent('53');
                            $TipoMail = $this->arrTemplateMail[$rigaMail]['TEMPLATE'];
                            $OggettoMail = '';
                            $BodyMail = '';
                            $templateMail = array();

                            if ($anaent_rec['ENTVAL']) {
                                $ElencoTemplateMail = unserialize($anaent_rec['ENTVAL']);
                                $templateMail = $ElencoTemplateMail[$rigaMail];
                                $OggettoMail = $templateMail['OGGETTOMAIL'];
                                $BodyMail = $templateMail['BODYMAIL'];
                            }

                            $_POST = array();
                            $model = 'proTemplateMail';
                            $_POST['event'] = 'openform';
                            $_POST['returnModel'] = $this->nameForm;
                            $_POST['returnMethod'] = 'returnBodyMail';
                            $_POST['RIGAMAIL'] = $rigaMail;
                            $_POST['TIPOMAIL'] = $TipoMail;
                            $_POST['OGGETTOMAIL'] = $OggettoMail;
                            $_POST['BODYMAIL'] = $BodyMail;
                            itaLib::openDialog($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;

                    case $this->gridAccountMail:
//                        Out::msgInfo('post', print_r($_POST, true));
                        $this->ApriAccountMail();
                        break;

                    case $this->gridEstensioni:
                        $this->InputEstensione($_POST['rowid']);
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        if (!$this->CtrFirmatario()) {
                            break;
                        }
                        if (!$this->CtrResponsabile()) {
                            break;
                        }
                        $anaent_1 = $this->proLib->GetAnaent('1');
                        $anaent_1['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_1'];
                        $anaent_1['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_1'];
                        $anaent_1['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_1'];
                        $anaent_1['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_1'];
                        $anaent_1['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6A_1'] . $_POST[$this->nameForm . '_ENTDE_6B_1'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_1['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_1, $update_Info);
                        //
                        $anaent_2 = $this->proLib->GetAnaent('2');
                        $anaent_2['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_2'];
                        $anaent_2['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_2'];
                        $anaent_2['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_2'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_2['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_2, $update_Info);
                        //
                        $anaent_3 = $this->proLib->GetAnaent('3');
                        $anaent_3['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_3'];
                        $anaent_3['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_3'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_3['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_3, $update_Info);
                        //
                        $anaent_11 = $this->proLib->GetAnaent('11');
                        $anaent_11['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_11'];
                        $anaent_11['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_11'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_11['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_11, $update_Info);
                        //
                        $anaent_12 = $this->proLib->GetAnaent('12');
                        $anaent_12['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_12'];
                        $anaent_12['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_12'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_12['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_12, $update_Info);
                        //
                        $anaent_13 = $this->proLib->GetAnaent('13');
                        $anaent_13['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_13'];
                        $anaent_13['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_13'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_13['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_13, $update_Info);
                        //
                        $anaent_14 = $this->proLib->GetAnaent('14');
                        $anaent_14['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_14'];
                        $anaent_14['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_14'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_14['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_14, $update_Info);
                        //
                        $anaent_15 = $this->proLib->GetAnaent('15');
                        $anaent_15['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_15'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_15['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_15, $update_Info);
                        //
                        // NON USARE 21 E 22 UTILIZZATI PER ALBO
                        //
                        $anaent_24 = $this->proLib->GetAnaent('24');
                        $anaent_24['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_24'];
                        $anaent_24['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_24'];
                        $anaent_24['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_24'];
                        $anaent_24['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_24'];
                        $anaent_24['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_24'];
                        $anaent_24['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_24'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_24['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_24, $update_Info);
                        //
                        $anaent_25 = $this->proLib->GetAnaent('25');
                        $anaent_25['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_25'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_25['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_25, $update_Info);
                        //
                        $anaent_26 = $this->proLib->GetAnaent('26');
                        $anaent_26['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_26'];
                        $anaent_26['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_26'];
                        $anaent_26['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_26'];
                        $anaent_26['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_26'];
                        $anaent_26['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_26']; /* Nuovo 18.02.2016 */
                        $anaent_26['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_26']; /* Nuovo 04.05.2016 */
                        $anaent_26['ENTVAL'] = $_POST[$this->nameForm . '_ENTVAL_26'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_26['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_26, $update_Info);
                        //
                        $anaent_27 = $this->proLib->GetAnaent('27');
                        $anaent_27['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_27'];
                        $anaent_27['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_27'];
                        $anaent_27['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_27'];
                        $anaent_27['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_27'];
                        $anaent_27['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_27'];
                        $anaent_27['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_27'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_27['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_27, $update_Info);
                        //
                        $anaent_28 = $this->proLib->GetAnaent('28');
                        $anaent_28['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_28'];
                        $anaent_28['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_28'];
                        $anaent_28['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_28'];
                        $anaent_28['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_28'];
                        $anaent_28['ENTVAL'] = serialize($this->account);
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_28['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_28, $update_Info);
                        //
                        $anaent_29 = $this->proLib->GetAnaent('29');
                        $anaent_29['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_29'];
                        $anaent_29['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_29'];
                        $anaent_29['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_29'];
                        $anaent_29['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_29'];
                        $anaent_29['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_29'];
                        $anaent_29['ENTVAL'] = $_POST[$this->nameForm . '_ENTVAL_29'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_29['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_29, $update_Info);
                        //
                        $anaent_30 = $this->proLib->GetAnaent('30');
                        $anaent_30['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_30'];
                        $anaent_30['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_30'];
                        $anaent_30['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_30'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_30['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_30, $update_Info);
                        //
                        $anaent_31 = $this->proLib->GetAnaent('31');
                        $anaent_31['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_31'];
                        $anaent_31['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_31'];
                        $anaent_31['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_31'];
                        $anaent_31['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_31'];
                        $anaent_31['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_31'];
                        /* ENTDE_6_31 Valorizzato su proParamSegnatura */
                        $anaent_31['ENTVAL'] = $_POST[$this->nameForm . '_ENTVAL_31'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_31['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_31, $update_Info);
                        //
                        $anaent_32 = $this->proLib->GetAnaent('32');
                        $anaent_32['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_32'];
                        $anaent_32['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_32'];
                        $anaent_32['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_32'];
                        $anaent_32['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_32'];
                        $anaent_32['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_32'];
                        $anaent_32['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_32'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_32['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_32, $update_Info);
                        //
                        $anaent_33 = $this->proLib->GetAnaent('33');
                        $anaent_33['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_33'];
                        $anaent_33['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_33'];
                        $anaent_33['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_33'];
                        $anaent_33['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_33'];
                        $anaent_33['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_33'];
                        $anaent_33['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_33'];
                        $anaent_33['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_33'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_33, $update_Info);
                        //
                        $anaent_34 = $this->proLib->GetAnaent('34');
                        $anaent_34['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_34'];
                        $anaent_34['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_34'];
                        $anaent_34['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_34'];
                        $anaent_34['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_34'];
                        /* ENTDE_5_34 Valorizzato su proParamSegnatura */
                        //$anaent_34['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_34'];// * Nuova Gestione */
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_34, $update_Info);
                        //
                        $anaent_35 = $this->proLib->GetAnaent('35');
                        //$anaent_35['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_35'];// * Nuova Gestione */
                        //$anaent_35['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_35'];// * Nuova Gestione */
                        //$anaent_35['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_35'];// * Nuova Gestione */
                        //$anaent_35['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_35'];// * Nuova Gestione */
                        $anaent_35['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_35'];
                        $anaent_35['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_35'];
                        $anaent_35['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_35'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_35, $update_Info);
                        //
                        $anaent_36 = $this->proLib->GetAnaent('36');
                        $anaent_36['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_36'];
                        $anaent_36['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_36'];
                        $anaent_36['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_36']; // Nuovo per gestione dest. interni obbligatori
                        $anaent_36['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_36']; // Nuovo per gestione dest. interni obbligatori
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_36, $update_Info);
                        //
                        $anaent_37 = $this->proLib->GetAnaent('37');
                        $anaent_37['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_37'];
                        $anaent_37['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_37'];
                        $anaent_37['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_37'];
                        $anaent_37['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_37'];
                        $anaent_37['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_37'];
                        $anaent_37['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_37'];
                        $anaent_37['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_37'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_37['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_37, $update_Info);
                        //
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        $anaent_38['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_38'];
                        $anaent_38['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_38'];
                        $anaent_38['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_38'];
                        $anaent_38['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_38'];
                        $anaent_38['ENTVAL'] = serialize($this->mailSdi);
                        $anaent_38['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_38'];
                        $anaent_38['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_38'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_38['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_38, $update_Info);
                        //
                        //Parametri Anaent Fattura Elettronica 1
                        $anaent_39 = $this->proLib->GetAnaent('39');
                        $anaent_39['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_39'];
                        $anaent_39['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_39'];
                        $anaent_39['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_39'];
                        $anaent_39['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_39'];
                        $anaent_39['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_39']; // Nuovo parametro stile fattura
                        $anaent_39['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_39'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_39['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_39, $update_Info);
                        //
                        $anaent_40 = $this->proLib->GetAnaent('40');
                        $anaent_40['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_40'];
                        $anaent_40['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_40'];
                        $anaent_40['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_40'];
                        $anaent_40['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_40'];
                        $anaent_40['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_40'];
                        $anaent_40['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_40'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_40['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_40, $update_Info);

                        // Parametri Registro Giornaliero:
                        $anaent_41 = $this->proLib->GetAnaent('41');
                        $anaent_41['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_41'];
                        $anaent_41['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_41'];
                        $anaent_41['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_41'];
                        $anaent_41['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_41'];
                        $anaent_41['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_41'];
                        $anaent_41['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_41'];
                        $anaent_41['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_41'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_41['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_41, $update_Info);

                        // Parametri Registro Giornaliero2:
                        $anaent_42 = $this->proLib->GetAnaent('42');
                        $anaent_42['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_42'];
                        $anaent_42['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_42'];
                        $anaent_42['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_42'];
                        $anaent_42['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_42'];
                        $anaent_42['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_42'];
                        $anaent_42['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_42'];
                        $anaent_42['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_42'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_42['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_42, $update_Info);

                        // Parametri Registro Giornaliero 3:
                        $anaent_43 = $this->proLib->GetAnaent('43');
                        $anaent_43['ENTVAL'] = serialize($this->mailGiornaliero);
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_43['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_43, $update_Info);

                        // Parametri Codici di Registro:
                        $anaent_44 = $this->proLib->GetAnaent('44');
                        $anaent_44['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_44'];
                        $anaent_44['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_44'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_44['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_44, $update_Info);

                        //
                        //  Parametri Anaent Fattura Elettronica 2
                        //
                        $anaent_45 = $this->proLib->GetAnaent('45');
                        $anaent_45['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_45'];
                        $anaent_45['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_45'];
                        $anaent_45['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_45'];
                        $anaent_45['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_45'];
                        $anaent_45['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_45']; // Mail per inoltro
                        $anaent_45['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_45']; // Tipologia Allegato
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_45['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_45, $update_Info);

                        //
                        //  Parametri Anaent Fattura Elettronica 3 WS Kibernetes
                        //
                        $anaent_46 = $this->proLib->GetAnaent('46');
                        $anaent_46['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_46'];
                        $anaent_46['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_46'];
                        $anaent_46['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_46'];
                        $anaent_46['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_46'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_46['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_46, $update_Info);

                        /* Parametri Anaent Vari: */
                        $anaent_47 = $this->proLib->GetAnaent('47');
                        //$anaent_47['ENTVAL'] = serialize($this->posizioniSegnatura);// * Nuova Gestione */
                        //$anaent_47['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_47'];// * Nuova Gestione */
                        //$anaent_47['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_47'];// * Nuova Gestione */
                        //$anaent_47['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_47'];// * Nuova Gestione */
                        //$anaent_47['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_47'];// * Nuova Gestione */
                        $anaent_47['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_47'];
                        $anaent_47['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_47'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_47['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_47, $update_Info);

                        // Parametri Generali
                        $anaent_48 = $this->proLib->GetAnaent('48');
                        $anaent_48['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_48'];
                        $anaent_48['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_48'];
                        $anaent_48['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_48'];
                        /* Flag attivazione Doc Formali unico progressivo:
                         * Gestito a parte con richiesta password e log. 
                          $anaent_48['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_48'];// quindi da NON utilizzare in form.
                         */
                        $anaent_48['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_48'];
                        $anaent_48['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_48'];
                        $anaent_48['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_48'];/** Nuovo. 23.01.2017: selezione da trasmissioni per invio mail */
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_48['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_48, $update_Info);


                        /* Parametri Gestione Documentaria */
                        $anaent_49 = $this->proLib->GetAnaent('49');
                        $anaent_49['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_49'];
                        $anaent_49['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_49'];
                        $anaent_49['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_49'];
                        $anaent_49['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_49'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_49['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_49, $update_Info);

                        /* Parametri Segnatura fascicolo */
                        $anaent_50 = $this->proLib->GetAnaent('50');
                        $anaent_50['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_50'];
                        $anaent_50['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_50'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_50['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_50, $update_Info);

                        /*
                         * 
                         * ANAENT: 51
                         * Parametri Copia Analoga Documento
                         * GESTITI su GestioneMarcautre 
                         * $anaent_51 = $this->proLib->GetAnaent('51');
                         * 
                         * $anaent_51['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_51'];
                         * $anaent_51['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_51'];
                         * $anaent_51['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_51'];
                         * $anaent_51['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_51'];
                         * 
                          $update_Info = 'Aggiornameto anaent: ' . $anaent_51['ENTKEY'];
                          $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_51, $update_Info);
                         */


                        /*
                         * ANAENT 52:
                         * Parametri Vari.
                         */
                        $anaent_52 = $this->proLib->GetAnaent('52');
                        $anaent_52['ENTVAL'] = serialize($this->elencoMailInoltro);
                        $anaent_52['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_52'];
                        $anaent_52['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_52'];
                        $anaent_52['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_52'];
                        // Parametri Conservazione Aggiuntivi:
                        $anaent_52['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_52'];
                        $anaent_52['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_52'];
                        $anaent_52['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_52'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_52['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_52, $update_Info);

                        /*
                         * ANAENT 53:
                         * Parametri di Conservazione.
                         */
                        $anaent_53 = $this->proLib->GetAnaent('53');
                        $anaent_53['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_53'];
                        $anaent_53['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_53'];
                        $anaent_53['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_53'];
                        $anaent_53['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_53'];
                        $anaent_53['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_53'];
                        $anaent_53['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_53'];
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_53['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_53, $update_Info);

                        /*
                         * ANAENT 54
                         * Parametri Vari (Mail)
                         */
                        $anaent_54 = $this->proLib->GetAnaent('54');
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_54['ENTKEY'];
                        $anaent_54['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_54'];
                        $anaent_54['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_54'];
                        $anaent_54['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_54'];
                        $anaent_54['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_54'];
                        // Altri dati:
                        $anaent_54['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_54'];
                        $anaent_54['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_54'];
                        $anaent_54['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_54'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_54, $update_Info);

                        /*
                         * ANAENT 55
                         * Parametri Vari (Mail)
                         */
                        $anaent_55 = $this->proLib->GetAnaent('55');
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_54['ENTKEY'];
                        $anaent_55['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_55'];
                        $anaent_55['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_55'];
                        $anaent_55['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_55'];
                        $anaent_55['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_55'];
                        $anaent_55['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_55'];
                        // _ENTDE_5_55 --- Utilizzato su Segnature.
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_55, $update_Info);

                        /*
                         * ANAENT 56
                         * Parametri Vari
                         */
                        $anaent_56 = $this->proLib->GetAnaent('56');
                        //_ENTDE_1_56 --- Utilizzato su Segnature per Documentale alla firma.
                        //_ENTDE_2_56 --- Utilizzato su Segnature per Documentale alla firma.
                        $anaent_56['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_56'];
                        $anaent_56['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_56'];
                        $anaent_56['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_56'];
                        $anaent_56['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_56']; // 
                        $anaent_56['ENTVAL'] = $_POST[$this->nameForm . '_ENTVAL_56'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_56, $update_Info);

                        /*
                         * ANAENT 57
                         * Parametri Scanner e Vari
                         */
                        $anaent_57 = $this->proLib->GetAnaent('57');
                        $anaent_57['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_57']; //Scanner
                        $anaent_57['ENTVAL'] = $_POST[$this->nameForm . '_ENTDE_VAL_57']; // Scanner
                        $anaent_57['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_57'];
                        $anaent_57['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_57'];
                        $anaent_57['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_57'];
                        $anaent_57['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_57'];
                        $anaent_57['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_57'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_57, $update_Info);
                        /*
                         * ANAENT 58
                         * Parametri  Vari
                         */
                        $anaent_58 = $this->proLib->GetAnaent('58');
                        $anaent_58['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_58']; //Scanner
                        $anaent_58['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_58'];
                        $anaent_58['ENTDE3'] = $_POST[$this->nameForm . '_ENTDE_3_58'];
                        $anaent_58['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_58'];
                        $anaent_58['ENTDE5'] = $_POST[$this->nameForm . '_ENTDE_5_58'];
                        $anaent_58['ENTDE6'] = $_POST[$this->nameForm . '_ENTDE_6_58'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_58, $update_Info);
                        /*
                         * ANAENT 59
                         * Parametri  Vari e Conservazione
                         */
                        $anaent_59 = $this->proLib->GetAnaent('59');
                        $anaent_59['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_59'];
                        $anaent_59['ENTDE2'] = $_POST[$this->nameForm . '_ENTDE_2_59'];
                        $anaent_59['ENTDE4'] = $_POST[$this->nameForm . '_ENTDE_4_59'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_59, $update_Info);
                        /*
                         * ANAENT 61
                         * Parametri Vari
                         */
                        $anaent_61 = $this->proLib->GetAnaent('61');
                        $anaent_61['ENTDE1'] = $_POST[$this->nameForm . '_ENTDE_1_61'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_61, $update_Info);

                        Out::msgBlock('', 2000, true, "Parametri Registrati correttamente.");
                        $this->OpenForm();
                        break;
                    case $this->nameForm . '_SelezionaAccaunt':
                        emlRic::emlRicAccount($this->nameForm, '', 'Smtp');
                        break;
                    case $this->nameForm . '_SelezionaAccountAlt':
                        emlRic::emlRicAccount($this->nameForm, '', 'SmtpAlt');
                        break;
                    case $this->nameForm . '_SelezionaAccauntSdi':
                        emlRic::emlRicAccount($this->nameForm, '', 'SmtpSdi');
                        break;
                    case $this->nameForm . '_SvuotaAccaunt':
                        Out::valore($this->nameForm . '_ENTDE_4_26', '');
                        break;
                    case $this->nameForm . '_SvuotaAccountAlt':
                        Out::valore($this->nameForm . '_ENTDE_2_37', '');
                        break;
                    case $this->nameForm . '_SvuotaAccountSdi':
                        Out::valore($this->nameForm . '_ENTDE_4_45', '');
                        break;

                    case $this->nameForm . '_ConfermaPasswordPop':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            $trovato = false;
                            foreach ($this->account as $value) {
                                if ($value['EMAIL'] == $mailAccount_rec['MAILADDR']) {
                                    $trovato = true;
                                    break;
                                }
                            }
                            if ($trovato === false) {
                                $this->account[]['EMAIL'] = $mailAccount_rec['MAILADDR'];
                            }
                            $this->CaricaGriglia($this->gridAccountMail, $this->account);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmpt':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_ENTDE_4_26', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmptAlt':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_ENTDE_2_37', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
                    case $this->nameForm . '_ConfermaPasswordSmptSdi':
                        include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                        $emlLib = new emlLib();
                        $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                        if ($mailAccount_rec['PASSWORD'] == $_POST[$this->nameForm . '_passwordAccount']) {
                            Out::valore($this->nameForm . '_ENTDE_4_45', $mailAccount_rec['MAILADDR']);
                        } else {
                            Out::msgStop("Attenzione!", "Non è stata inserita la password corretta.");
                        }
                        break;
                    case $this->nameForm . '_segnaturaVar':
                        $this->embedVars("returnModelloSegnaura");
                        break;

                    case $this->nameForm . '_segnaturaFasVar':
                        $this->embedVarsFas("returnModelloSegnauraFas");
                        break;

                    case $this->nameForm . '_numeroProt_butt':
                        $anaent_1 = $this->proLib->GetAnaent('1');
                        $valori[] = array(
                            'label' => array(
                                'value' => "Ultimo progressivo occupato",
                                'style' => 'width:200px;maxlength:6;size:10;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_newProgressivoProt',
                            'name' => $this->nameForm . '_newProgressivoProt',
                            'type' => 'text',
                            'style' => 'margin:2px;width:80px;',
                            'maxlength' => '6',
                            'value' => $anaent_1['ENTDE1']
                        );
                        Out::msgInput(
                                'Modifica progressivo protocollo.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaProgressivoProt',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->nameForm . '_ConfermaProgressivoProt':
                        $anaent_1 = $this->proLib->GetAnaent('1');
                        $anaent_1['ENTDE1'] = $_POST[$this->nameForm . '_newProgressivoProt'];
                        $update_Info = 'Aggiornameto progressivo anaent: ' . $anaent_1['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_1, $update_Info);
                        $this->OpenForm();
                        break;
                    case $this->nameForm . '_numeroComunicazioni_butt':
                        $anaent_23 = $this->proLib->GetAnaent('23');
                        $valori[] = array(
                            'label' => array(
                                'value' => "Ultimo progressivo Documenti Formali",
                                'style' => 'width:300px;maxlength:6;size:10;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
                            ),
                            'id' => $this->nameForm . '_newProgressivoComun',
                            'name' => $this->nameForm . '_newProgressivoComun',
                            'type' => 'text',
                            'style' => 'margin:2px;width:80px;',
                            'maxlength' => '6',
                            'value' => $anaent_23['ENTDE1']
                        );
                        Out::msgInput(
                                'Modifica progressivo Documenti Formali.', $valori
                                , array(
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaProgressivoComun',
                                'model' => $this->nameForm)
                                ), $this->nameForm . "_workSpace"
                        );
                        break;
                    case $this->nameForm . '_ConfermaProgressivoComun':
                        $anaent_23 = $this->proLib->GetAnaent('23');
                        $anaent_23['ENTDE1'] = $_POST[$this->nameForm . '_newProgressivoComun'];
                        $update_Info = 'Aggiornameto progressivo anaent: ' . $anaent_23['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_23, $update_Info);
                        $this->OpenForm();
                        break;
                    case $this->nameForm . '_ENTDE_3_30_butt':
                        proRic::proRicAnauff($this->nameForm);
                        break;
                    case $this->nameForm . '_ENTDE_1_34_butt':
                        proRic::proRicAnauff($this->nameForm, '', 'returnanauff2');
                        break;
                    case $this->nameForm . '_ENTDE_5_31_butt':
                        proRic::proRicAnauff($this->nameForm, '', 'returnanauffAbOggetto');
                        break;
                    case $this->nameForm . '_BloccaProtocollo':
                        $anaent_29 = $this->proLib->GetAnaent('29');
                        if ($anaent_29['ENTDE3'] != '') {
                            Out::valore($this->nameForm . '_StatoProtocollo', 'ATTIVO');
                            $anaent_29['ENTDE3'] = '';
                        } else {
                            Out::valore($this->nameForm . '_StatoProtocollo', 'IN MANUTENZIONE');
                            $anaent_29['ENTDE3'] = '1';
                        }
                        $update_Info = 'Aggiornameto anaent: ' . $anaent_29['ENTKEY'];
                        $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_29, $update_Info);
                        break;
                    case $this->nameForm . '_ConfermaAddMailSdi':
                        if ($_POST[$this->nameForm . '_addMailSdi']) {
                            $this->mailSdi[]['EMAIL'] = $_POST[$this->nameForm . '_addMailSdi'];
                            $this->CaricaGriglia($this->gridMailSdi, $this->mailSdi);
                        }
                        break;
                    case $this->nameForm . '_ConfermaAddMailGiornaliero':
                        if ($_POST[$this->nameForm . '_addMailGiorno']) {
                            $this->mailGiornaliero[]['EMAIL'] = $_POST[$this->nameForm . '_addMailGiorno'];
                            $this->CaricaGriglia($this->gridMailGiornaliero, $this->mailGiornaliero);
                        }
                        break;

                    case $this->nameForm . '_ENTDE_1_38_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_1_38');
                        break;
                    case $this->nameForm . '_ENTDE_2_38_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_2_38');
                        break;
                    case $this->nameForm . '_ENTDE_3_38_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_3_38');
                        break;
                    case $this->nameForm . '_ENTDE_4_38_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_4_38');
                        break;
                    case $this->nameForm . '_ENTDE_VAL_41_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_41');
                        break;
                    case $this->nameForm . '_ENTDE_5_45_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_5_45');
                        break;

                    case $this->nameForm . '_ENTDE_1_41_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedFirmatario');
                        break;
                    case $this->nameForm . '_ENTDE_2_41_butt':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_41'];
                        if ($codice) {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', 'Firmatario');
                        } else {
                            Out::msgInfo("Attenzione", "Indicare prima il Firmatario.");
                        }
                        break;
                    case $this->nameForm . '_ENTDE_VAL_42_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm);
                        break;
                    case $this->nameForm . '_ENTDE_5_41_butt':
                        if ($_POST[$this->nameForm . '_ENTDE_VAL_42']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_ENTDE_VAL_42'] . "'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_ENTDE_6_41_butt':
                        if ($_POST[$this->nameForm . '_ENTDE_VAL_42']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_ENTDE_VAL_42'] . "'";
                            if ($_POST[$this->nameForm . '_ENTDE_5_41']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_ENTDE_VAL_42']
                                        . $_POST[$this->nameForm . '_ENTDE_5_41'] . "'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where);
                        break;
                    case $this->nameForm . '_ENTDE_2_42_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnanamedResponsabile');
                        break;
                    case $this->nameForm . '_ENTDE_3_42_butt':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_41'];
                        if ($codice) {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', 'Responsabile');
                        } else {
                            Out::msgInfo("Attenzione", "Indicare prima il Responsabile.");
                        }
                        break;

                    case $this->nameForm . '_GestioneSegnatura':
                        $model = 'proParamSegnatura';
                        itaLib::openDialog($model);
                        $proGestPratica = itaModel::getInstance($model);
                        $proGestPratica->setEvent('openform');
                        $proGestPratica->parseEvent();
                        break;

                    case $this->nameForm . '_returnPasswordDocFormali':
                        $anaent_48 = $this->proLib->GetAnaent('48');
                        if (!$this->proLib->CtrPassword($_POST[$this->nameForm . '_password'])) {
                            Out::valore($this->nameForm . '_ENTDE_4_48', $anaent_48['ENTDE4']);
                            break;
                        }

                        /* Salvo e Loggo chi ha eseguito l'operazione. */
                        $ValorePar = $_POST[$this->nameForm . '_ENTDE_4_48'];
                        $Anaent48Rec = array();
                        $Anaent48Rec['ROWID'] = $anaent_48['ROWID'];
                        $Anaent48Rec['ENTDE4'] = $ValorePar;
                        $update_Info = 'Cambio progressivita doc Formali. Anaent 4_48: ' . $ValorePar;
                        if (!$this->updateRecord($this->PROT_DB, 'ANAENT', $Anaent48Rec, $update_Info)) {
                            Out::msgStop("Attenzione", "Errore in aggiornamento Anaent 48 progressivo 4. ");
                        } else {
                            Out::msgInfo('Progressivi', "Progressività per Documenti Formali Aggiornata.");
                        }
                        break;


                    case $this->nameForm . '_ENTDE_1_52_butt':
                        $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        proRic::proRicAnamed($this->nameForm, $filtroUff, '', '', 'returnFirmDefault');
                        break;

                    case $this->nameForm . '_ENTDE_2_52_butt':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_52'];
                        if ($codice) {
                            proRic::proRicUfficiPerDestinatario($this->nameForm, $codice, '', '', 'FirmatarioDefault');
                        } else {
                            Out::msgInfo("Attenzione", "Indicare prima il Firmatario di Default.");
                        }
                        break;

                    case $this->nameForm . '_ENTDE_1_54_butt':
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, '', 'returnTitolarioMail');
                        break;
                    case $this->nameForm . '_ENTDE_2_54_butt':
                        if ($_POST[$this->nameForm . '_ENTDE_1_54']) {
                            $where = array('ANACAT' => " AND CATCOD='" . $_POST[$this->nameForm . '_ENTDE_1_54'] . "'");
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioMail');
                        break;
                    case $this->nameForm . '_ENTDE_3_54_butt':
                        if ($_POST[$this->nameForm . '_ENTDE_1_54']) {
                            $where['ANACAT'] = " AND CATCOD='" . $_POST[$this->nameForm . '_ENTDE_1_54'] . "'";
                            if ($_POST[$this->nameForm . '_ENTDE_2_54']) {
                                $where['ANACLA'] = " AND CLACCA='" . $_POST[$this->nameForm . '_ENTDE_1_54']
                                        . $_POST[$this->nameForm . '_ENTDE_2_54'] . "'";
                            }
                        }
                        proric::proRicTitolario($this->PROT_DB, $this->proLib, $this->nameForm, $where, 'returnTitolarioMail');
                        break;

                    case $this->nameForm . '_ConfermaEstensione':
                        $ProExtConser_rec = $_POST[$this->nameForm . '_PROEXTCONSER'];
                        $ProExtConser_rec['ESTENSIONE'] = trim($ProExtConser_rec['ESTENSIONE']);

                        if (!$ProExtConser_rec['ESTENSIONE'] || !$ProExtConser_rec['DESCRIZIONE']) {
                            Out::msgInfo("Estensione", "Indicare una estensione e la relativa descrizione.");
                            break;
                        }
                        //Controllo esistenza.
                        $ProExtConserTest_rec = $this->proLib->GetProExtConser($ProExtConser_rec['ESTENSIONE'], 'ext');
                        if ($ProExtConserTest_rec) {
                            if ($ProExtConser_rec['ROW_ID'] != $ProExtConserTest_rec['ROW_ID']) {
                                Out::msgInfo("Attenzione", "Estensione " . $ProExtConser_rec['ESTENSIONE'] . " già presente.");
                                break;
                            }
                        }
                        if (!$ProExtConser_rec['ROW_ID']) {
                            $this->insertRecord($this->PROT_DB, 'PROEXTCONSER', $ProExtConser_rec, '', 'ROW_ID');
                        } else {
                            $this->updateRecord($this->PROT_DB, 'PROEXTCONSER', $ProExtConser_rec, '', 'ROW_ID');
                        }
                        $this->CaricaEstensioniConservazione();
                        break;

                    case $this->nameForm . '_ENTDE_1_59_butt':
                        proRic::proRicAnaTipoDoc($this->nameForm, '', 'returnAnaTipoDoc_59');
                        break;

                    case $this->nameForm . '_ConfermaCancellaExt':
                        $rowid = $_POST[$this->gridEstensioni]['gridParam']['selarrrow'];
                        $this->deleteRecord($this->PROT_DB, 'PROEXTCONSER', $rowid, '', 'ROW_ID');
                        $this->CaricaEstensioniConservazione();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'embedVars':
                $this->embedVars('', 'baseprotocollo');
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ENTDE_1_24':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_24'];
                        $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                        Out::valore($this->nameForm . '_ENTDE_1_24', $codice);
                        break;
                    case $this->nameForm . '_ENTDE_1_41':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_41'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . '_ENTDE_1_41', '');
                                Out::valore($this->nameForm . '_DESCRIZIONE_FRIMATARIO', '');
                                break;
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_1_41', $anamed_rec['MEDCOD']);
                                Out::valore($this->nameForm . '_DESCRIZIONE_FRIMATARIO', $anamed_rec['MEDNOM']);
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_1_41', '');
                            Out::valore($this->nameForm . '_DESCRIZIONE_FRIMATARIO', '');
                        }
                        break;
                    case $this->nameForm . '_ENTDE_2_41':
                        $codice = $_POST[$this->nameForm . '_ENTDE_2_41'];
                        if (trim($codice)) {
                            $codice = str_pad($codice, 4, '0', STR_PAD_LEFT);
                            $anauff_rec = $this->proLib->GetAnauff($codice, 'codice');
                            if ($anauff_rec) {
                                Out::valore($this->nameForm . '_ENTDE_2_41', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', $anauff_rec['UFFDES']);
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_2_41', '');
                                Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_2_41', '');
                            Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', '');
                        }
                        break;
                    case $this->nameForm . '_ENTDE_2_42':
                        $codice = $_POST[$this->nameForm . '_ENTDE_2_42'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . '_ENTDE_2_42', '');
                                Out::valore($this->nameForm . '_DESCRIZIONE_RESPONSABILE', '');
                                break;
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_2_42', $anamed_rec['MEDCOD']);
                                Out::valore($this->nameForm . '_DESCRIZIONE_RESPONSABILE', $anamed_rec['MEDNOM']);
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_2_42', '');
                            Out::valore($this->nameForm . '_DESCRIZIONE_RESPONSABILE', '');
                        }
                        break;
                    case $this->nameForm . '_ENTDE_3_42':
                        $codice = $_POST[$this->nameForm . '_ENTDE_3_42'];
                        if (trim($codice)) {
                            $codice = str_pad($codice, 4, '0', STR_PAD_LEFT);
                            $anauff_rec = $this->proLib->GetAnauff($codice, 'codice');
                            if ($anauff_rec) {
                                Out::valore($this->nameForm . '_ENTDE_3_42', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', $anauff_rec['UFFDES']);
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_3_42', '');
                                Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_3_42', '');
                            Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', '');
                        }
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ENTDE_4_11':
                        if ($_POST[$this->nameForm . '_ENTDE_4_11'] == true) {
                            Out::valore($this->nameForm . '_ENTDE_4_12', true);
                            Out::valore($this->nameForm . '_ENTDE_4_13', true);
                            //                    Out::valore($this->nameForm.'_ENTDE_4_14',true);
                        }
                        break;
                    case $this->nameForm . '_ENTDE_4_12':
                        if ($_POST[$this->nameForm . '_ENTDE_4_12'] == true) {
                            Out::valore($this->nameForm . '_ENTDE_4_13', true);
                            //                  Out::valore($this->nameForm.'_ENTDE_4_14',true);
                        }
                        break;
                    case $this->nameForm . '_ENTDE_3_30':
                        $codice = $_POST[$this->nameForm . '_ENTDE_3_30'];
                        if (trim($codice) != "") {
                            $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $this->DecodAnauff($codice);
                        }
                        break;

                    case $this->nameForm . '_ENTDE_6_38':
                        if (!$_POST[$this->nameForm . '_ENTDE_6_38']) {
                            Out::hide($this->nameForm . '_ENTDE_1_39_field');
                            Out::valore($this->nameForm . '_ENTDE_1_39', 0);
                        } else {
                            Out::show($this->nameForm . '_ENTDE_1_39_field');
                        }
                        break;
                    case $this->nameForm . '_ENTDE_VAL_42':
                        $codice = str_pad($_POST[$this->nameForm . '_ENTDE_VAL_42'], 4, '0', STR_PAD_LEFT);
                        $this->DecodAnacat($codice, 'codice');
                        break;
                    case $this->nameForm . '_ENTDE_6_41':
                    case $this->nameForm . '_ENTDE_5_41':
                        Out::valore($this->nameForm . '_ENTDE_VAL_42', '');
                        Out::valore($this->nameForm . '_ENTDE_5_41', '');
                        Out::valore($this->nameForm . '_ENTDE_6_41', '');
                        Out::valore($this->nameForm . '_TitolarioDecod', '');
                        break;

                    case $this->nameForm . '_ENTDE_4_48':
                        $titolo = "Modifica progressività dei Documenti Formali.";
                        $this->proLib->GetMsgInputPassword($this->nameForm, $titolo, 'DocFormali');
                        break;

                    case $this->nameForm . '_ENTDE_1_52':
                        $codice = $_POST[$this->nameForm . '_ENTDE_1_52'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . '_ENTDE_1_52', '');
                                Out::valore($this->nameForm . '_DESC_FIRMATARIO_DEFAULT', '');
                                Out::valore($this->nameForm . '_ENTDE_2_52', '');
                                Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', '');
                                break;
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_1_52', $anamed_rec['MEDCOD']);
                                Out::valore($this->nameForm . '_DESC_FIRMATARIO_DEFAULT', $anamed_rec['MEDNOM']);
                                Out::valore($this->nameForm . '_ENTDE_2_52', '');
                                Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', '');
                                $uffdes_tab = $this->GetUfficiAnamed($anamed_rec['MEDCOD']);
                                if (count($uffdes_tab) == 1) {
                                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                    Out::valore($this->nameForm . '_ENTDE_2_52', $anauff_rec['UFFCOD']);
                                    Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', $anauff_rec['UFFDES']);
                                } else {
                                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'FirmatarioDefault');
                                }
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_1_52', '');
                            Out::valore($this->nameForm . '_DESC_FIRMATARIO_DEFAULT', '');
                            Out::valore($this->nameForm . '_ENTDE_2_52', '');
                            Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', '');
                        }
                        break;

                    case $this->nameForm . '_ENTDE_2_52':
                        $codice = $_POST[$this->nameForm . '_ENTDE_2_52'];
                        if (trim($codice)) {
                            $codice = str_pad($codice, 4, '0', STR_PAD_LEFT);
                            $anauff_rec = $this->proLib->GetAnauff($codice, 'codice');
                            if ($anauff_rec) {
                                Out::valore($this->nameForm . '_ENTDE_2_52', $anauff_rec['UFFCOD']);
                                Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', $anauff_rec['UFFDES']);
                            } else {
                                Out::valore($this->nameForm . '_ENTDE_2_52', '');
                                Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', '');
                            }
                        } else {
                            Out::valore($this->nameForm . '_ENTDE_2_52', '');
                            Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', '');
                        }
                        break;

                    case $this->nameForm . '_ENTDE_1_54':
                        $codice = str_pad($_POST[$this->nameForm . '_ENTDE_1_54'], 4, '0', STR_PAD_LEFT);
                        $this->DecodAnacatMail($codice, 'codice');
                        break;

                    case $this->nameForm . '_ENTDE_2_54':
                    case $this->nameForm . '_ENTDE_3_54':
                        Out::valore($this->nameForm . '_ENTDE_1_54', '');
                        Out::valore($this->nameForm . '_ENTDE_2_54', '');
                        Out::valore($this->nameForm . '_ENTDE_3_54', '');
                        Out::valore($this->nameForm . '_TitolarioDecodMail', '');
                        break;
                    case $this->nameForm . '_ENTDE_6_53':
                        $this->DecodificaFlagEstensioni($_POST[$this->nameForm . '_ENTDE_6_53']);
                        break;
                }
                break;
            case 'returnModelloSegnaura':
                Out::codice("$('#" . $this->nameForm . '_ENTDE_VAL_33' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnModelloSegnauraFas':
                Out::codice("$('#" . $this->nameForm . '_ENTDE_VAL_50' . "').replaceSelection('" . $_POST["rowData"]['markupkey'] . "', true);");
                break;
            case 'returnanauff':
                $this->DecodAnauff($_POST['retKey'], 'rowid');
                break;
            case 'returnanauff2':
                $this->DecodAnauff2($_POST['retKey'], 'rowid');
                break;
            case 'returnanauffAbOggetto':
                $this->DecodAnauffAbOggetto($_POST['retKey'], 'rowid');
                break;
            case 'returnAccountPop':
                $this->appoggio = $_POST['retKey'];
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
                include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                $emlLib = new emlLib();
                $mailAccount_rec = $emlLib->getMailAccount($this->appoggio, 'rowid');
                $messaggio = $mailAccount_rec['MAILADDR'] . " : Inserisci la password dell'account selezionato";
                Out::msgInput(
                        'Account Email', $valori
                        , array(
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordPop', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;
            case 'returnAccountSmtp':
                $this->appoggio = $_POST['retKey'];
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
            case 'returnAccountSmtpAlt':
                $this->appoggio = $_POST['retKey'];
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
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordSmptAlt', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;
            case 'returnAccountSmtpSdi':
                $this->appoggio = $_POST['retKey'];
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
                    'Conferma' => array('id' => $this->nameForm . '_ConfermaPasswordSmptSdi', 'model' => $this->nameForm)
                        ), $this->nameForm . "_workSpace", 'auto', '700', true, "<span style=\"font-size:1.2em;font-weight:bold;\">$messaggio</span>"
                );
                break;

            case 'returnAccountInoltro':
                include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                $emlLib = new emlLib();
                $mailAccount_rec = $emlLib->getMailAccount($_POST['retKey'], 'rowid');
                $trovato = false;
                foreach ($this->elencoMailInoltro as $value) {
                    if ($value['EMAIL'] == $mailAccount_rec['MAILADDR']) {
                        $trovato = true;
                        break;
                    }
                }
                if ($trovato === false) {
                    $this->elencoMailInoltro[]['EMAIL'] = $mailAccount_rec['MAILADDR'];
                }
                $this->CaricaGriglia($this->gridMailInoltro, $this->elencoMailInoltro);
                break;

            case 'returnAnaTipoDoc_1_38':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_1_38', $AnaTipoDoc_rec['CODICE']);
                break;
            case 'returnAnaTipoDoc_2_38':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_2_38', $AnaTipoDoc_rec['CODICE']);
                break;
            case 'returnAnaTipoDoc_3_38':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_3_38', $AnaTipoDoc_rec['CODICE']);
                break;
            case 'returnAnaTipoDoc_4_38':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_4_38', $AnaTipoDoc_rec['CODICE']);
                break;

            case 'returnAnaTipoDoc_41':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_VAL_41', $AnaTipoDoc_rec['CODICE']);
                break;
            case 'returnAnaTipoDoc_5_45':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_5_45', $AnaTipoDoc_rec['CODICE']);
                break;

            case 'returnanamedFirmatario':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_ENTDE_1_41', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESCRIZIONE_FRIMATARIO", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_ENTDE_2_41", '');
                Out::valore($this->nameForm . "_UFFICIO_FIRMATARIO", '');

                $uffdes_tab = $this->GetUfficiAnamed($anamed_rec['MEDCOD']);
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    Out::valore($this->nameForm . '_ENTDE_2_41', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', $anauff_rec['UFFDES']);
                } else {
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Firmatario');
                }
                break;

            case 'returnanamedResponsabile':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_ENTDE_2_42', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESCRIZIONE_RESPONSABILE", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_ENTDE_3_42", '');
                Out::valore($this->nameForm . "_UFFICIO_RESPONSABILE", '');

                $uffdes_tab = $this->GetUfficiAnamed($anamed_rec['MEDCOD']);
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    Out::valore($this->nameForm . '_ENTDE_3_42', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', $anauff_rec['UFFDES']);
                } else {
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'Responsabile');
                }
                break;

            case 'returnUfficiPerDestinatarioFirmatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_2_41', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_UFFICIO_FIRMATARIO");
                break;
            case 'returnUfficiPerDestinatarioResponsabile':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_3_42', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_UFFICIO_RESPONSABILE");
                break;

            case 'returnTitolario':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolario($rowid, $tipoArc);
                break;
            case 'returnTitolarioMail':
                $tipoArc = substr($_POST['rowData']['CHIAVE'], 0, 6);
                $rowid = substr($_POST['rowData']['CHIAVE'], 7, 6);
                $this->decodTitolarioMail($rowid, $tipoArc);
                break;

            case 'returnFirmDefault':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_ENTDE_1_52', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESC_FIRMATARIO_DEFAULT", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_ENTDE_2_52", '');
                Out::valore($this->nameForm . "_UFF_FIRMATARIO_DEFAULT", '');

                $uffdes_tab = $this->GetUfficiAnamed($anamed_rec['MEDCOD']);
                if (count($uffdes_tab) == 1) {
                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                    Out::valore($this->nameForm . '_ENTDE_2_52', $anauff_rec['UFFCOD']);
                    Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', $anauff_rec['UFFDES']);
                } else {
                    proRic::proRicUfficiPerDestinatario($this->nameForm, $anamed_rec['MEDCOD'], '', '', 'FirmatarioDefault');
                }
                break;

            case 'returnUfficiPerDestinatarioFirmatarioDefault':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_2_52', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . "_UFF_FIRMATARIO_DEFAULT");
                break;

            case 'returnBodyMail':
                $RigaMail = $_POST['RIGAMAIL'];
                if ($RigaMail) {
                    $anaent_rec = $this->proLib->GetAnaent('53');
                    $ElencoTemplate = array();
                    $ElencoTemplate = unserialize($anaent_rec['ENTVAL']);
                    $ElencoTemplate[$RigaMail]['OGGETTOMAIL'] = $_POST['OGGETTOMAIL'];
                    $ElencoTemplate[$RigaMail]['BODYMAIL'] = $_POST['BODYMAIL'];

                    $anaent_rec['ENTVAL'] = serialize($ElencoTemplate);
                    // Aggiorno i dati mail:
                    $update_Info = 'Aggiornameto Template mail: ' . $anaent_rec['ENTKEY'];
                    $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_rec, $update_Info);
                }
                break;

            case 'retAggAccountMail':
                if ($_POST['ACCOUNT']) {
                    $this->account[$_POST['INDICEMAIL']] = $_POST['ACCOUNT'];
                }
                $this->CaricaGriglia($this->gridAccountMail, $this->account);
                break;

            case 'returnAnaTipoDoc_59':
                $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_ENTDE_1_59', $AnaTipoDoc_rec['CODICE']);
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_account');
        App::$utente->removeKey($this->nameForm . '_appoggio');
        App::$utente->removeKey($this->nameForm . '_mailSdi');
        App::$utente->removeKey($this->nameForm . '_mailGiornaliero');
        App::$utente->removeKey($this->nameForm . '_posizioniSegnatura');
        App::$utente->removeKey($this->nameForm . '_elencoMailInoltro');
        App::$utente->removeKey($this->nameForm . '_arrTemplateMail');
        App::$utente->removeKey($this->nameForm . '_estensioniConservazione');
        App::$utente->removeKey($this->nameForm . '_utiGridMetaDataNameForm');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    private function Azzera() {
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valore($this->nameForm . '_ENTDE_2_34', '0');
        Out::valore($this->nameForm . '_ENTDE_3_34', '0');
        Out::valore($this->nameForm . '_ENTDE_4_35', '0');
    }

    private function CreaCombo() {
        Out::select($this->nameForm . '_ENTDE_2_1', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_2_1', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_3_1', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_3_1', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_4_1', 1, "0", "1", "Modifica sempre");
        Out::select($this->nameForm . '_ENTDE_4_1', 1, "1", "0", "Modifica solo in giornata");
        Out::select($this->nameForm . '_ENTDE_4_1', 1, "2", "0", "Non modificabile dopo la Registrazione");

        Out::select($this->nameForm . '_ENTDE_5_1', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_5_1', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_6A_1', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_6A_1', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_6B_1', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_6B_1', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_3_24', 1, "0", "1", "");
        Out::select($this->nameForm . '_ENTDE_3_24', 1, "1", "0", "Attiva Stampante Zebra su Cups");
        Out::select($this->nameForm . '_ENTDE_3_24', 1, "2", "0", "Attiva Stampa su Documento 2x6");
        Out::select($this->nameForm . '_ENTDE_3_24', 1, "3", "0", "Attiva Stampa su Documento 3x8");
        Out::select($this->nameForm . '_ENTDE_3_24', 1, "4", "0", "Attiva Stampa su Documento 3x10");
        Out::select($this->nameForm . '_ENTDE_3_24', 1, "5", "0", "Attiva Stampante Zebra su windows");

        Out::select($this->nameForm . '_ENTDE_4_24', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_4_24', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_6_24', 1, "0", "1", "Disattivato");
        Out::select($this->nameForm . '_ENTDE_6_24', 1, "1", "0", "Attivato");

        Out::select($this->nameForm . '_ENTDE_1_25', 1, "0", "0", "Scarica Automaticamente");
        Out::select($this->nameForm . '_ENTDE_1_25', 1, "1", "0", "Non Scaricare Automaticamente");
//        Out::select($this->nameForm.'_ENTDE_1_25', 1,"2","1", "Chiedi se Scaricare");

        Out::select($this->nameForm . '_ENTDE_4_32', 1, "", "1", "Allegati non Obbligatori");
        Out::select($this->nameForm . '_ENTDE_4_32', 1, "1", "0", "Obbligo di Allegare i Documenti - solo avviso");
        Out::select($this->nameForm . '_ENTDE_4_32', 1, "2", "0", "Obbligo di Allegare i Documenti - blocca ");

        Out::select($this->nameForm . '_ENTDE_2_33', 1, "01", "0", "Per assegnazione ufficio (vecchio tipo di gestione)");
        Out::select($this->nameForm . '_ENTDE_2_33', 1, "02", "1", "Per assegnazione destinatario");

        Out::select($this->nameForm . '_ENTDE_2_34', 1, "0", "1", "ANNUALE");
        Out::select($this->nameForm . '_ENTDE_2_34', 1, "1", "0", "ASSOLUTA");

        Out::select($this->nameForm . '_ENTDE_3_34', 1, "0", "1", "TITOLARIO.ANNO.COD_FASCICOLO");
        Out::select($this->nameForm . '_ENTDE_3_34', 1, "1", "0", "ANNO.TITOLARIO.COD_FASCICOLO");

        Out::select($this->nameForm . '_ENTDE_4_35', 1, "0", "1", "0°");
        Out::select($this->nameForm . '_ENTDE_4_35', 1, "90", "0", "90°");
        Out::select($this->nameForm . '_ENTDE_4_35', 1, "180", "0", "180°");
        Out::select($this->nameForm . '_ENTDE_4_35', 1, "270", "0", "270°");

        Out::select($this->nameForm . '_ENTDE_6_34', 1, "", "1", "");
        Out::select($this->nameForm . '_ENTDE_6_34', 1, "1", "0", "Richiedi Marcatura Pdf da Pec");
        Out::select($this->nameForm . '_ENTDE_6_34', 1, "2", "0", "Marca in automatico i Pdf da Pec");

        Out::select($this->nameForm . '_ENTDE_1_36', 1, "", "1", "'Scansione'+data+ora");
        Out::select($this->nameForm . '_ENTDE_1_36', 1, "1", "0", "NumProt+Mit/Dest+data+ora");

        Out::select($this->nameForm . '_ENTDE_2_36', 1, "", "1", "Iter Normale");
        Out::select($this->nameForm . '_ENTDE_2_36', 1, "1", "0", "Non generare Iter se Utente non esistente");
        Out::select($this->nameForm . '_ENTDE_2_36', 1, "2", "0", "Genere Iter Storicizzato se non esiste l'Utente");

        Out::select($this->nameForm . '_ENTDE_4_37', 1, "", "1", "Seleziona...");
        Out::select($this->nameForm . '_ENTDE_4_37', 1, "1", "0", "Solo firme Remote");
        Out::select($this->nameForm . '_ENTDE_4_37', 1, "2", "0", "Solo firme Locali");
        Out::select($this->nameForm . '_ENTDE_4_37', 1, "3", "0", "Firme Remote e Locali");

        Out::select($this->nameForm . '_ENTDE_6_38', 1, "", "0", "Non salvare");
        Out::select($this->nameForm . '_ENTDE_6_38', 1, "1", "0", "Salva mittente busta");
        Out::select($this->nameForm . '_ENTDE_6_38', 1, "2", "0", "Salva mittente mail originale");

        Out::select($this->nameForm . '_ENTDE_2_39', 1, "", "0", "Attiva Sempre");
        Out::select($this->nameForm . '_ENTDE_2_39', 1, "1", "0", "Non Attivare");
        Out::select($this->nameForm . '_ENTDE_2_39', 1, "2", "0", "Solo su Gestione Protocollo");
        Out::select($this->nameForm . '_ENTDE_2_39', 1, "3", "0", "Solo su Gestione Trasmissioni");

        Out::select($this->nameForm . '_ENTDE_2_46', 1, "", "0", "Nessuna");
        Out::select($this->nameForm . '_ENTDE_2_46', 1, "KIBERNETES", "0", "Kibernetes");

        foreach (proLibSdi::$ElencoTipoFileInfoFattura as $key => $value) {
            Out::select($this->nameForm . '_ENTDE_4_39', 1, $key, "0", $value);
        }

        Out::select($this->nameForm . '_ENTDE_6_42', 1, "", "0", "Nessuna");
        Out::select($this->nameForm . '_ENTDE_6_42', 1, "DIGIP_MARCHE", "0", "DIGIP MARCHE");
        Out::select($this->nameForm . '_ENTDE_6_42', 1, "ASMEZDOC", "0", "ASMEZDOC");
        Out::select($this->nameForm . '_ENTDE_6_42', 1, "NAMIRIAL", "0", "NAMIRIAL");


        // Invio conferma ricezione
        Out::select($this->nameForm . '_ENTDE_4_54', 1, "", "0", "Mai");
        Out::select($this->nameForm . '_ENTDE_4_54', 1, "1", "0", "Sempre");
        Out::select($this->nameForm . '_ENTDE_4_54', 1, "2", "0", "Solo se richiesto dall'AOO mittente");

        // Ordine elementi
        Out::select($this->nameForm . '_ENTDE_1_55', 1, "", "0", "Di inserimento");
        Out::select($this->nameForm . '_ENTDE_1_55', 1, "1", "0", "Per numero documento");

        // Predisposizione Protocolli
        Out::select($this->nameForm . '_ENTDE_3_55', 1, "", "0", "Firma il documento e crea il protocollo");
        Out::select($this->nameForm . '_ENTDE_3_55', 1, "1", "0", "Crea il protocollo e firma contestuale");

        // Stili Fattura in Visualizzazione
        Out::select($this->nameForm . '_ENTDE_5_39', 1, "", "0", "Default SDI");
        Out::select($this->nameForm . '_ENTDE_5_39', 1, "1", "0", "Riepilogo Dati Fattura");

        //
        Out::select($this->nameForm . '_ENTDE_4_57', 1, "", "0", "Semplice");
        Out::select($this->nameForm . '_ENTDE_4_57', 1, "1", "0", "Ad albero");

        // combo 
        Out::select($this->nameForm . '_ENTDE_4_59', 1, "", "0", "Asincrona");
        Out::select($this->nameForm . '_ENTDE_4_59', 1, "1", "0", "Sincrona");

        /*
         * Anagrafiche Personalizzate
         */
        Out::select($this->nameForm . '_ENTDE_6_58', 1, "", "0", "");
        Out::select($this->nameForm . '_ENTDE_6_58', 1, "proRicVerticaleErap", "0", "Locatari Erap - Insigeco");
    }

    private function OpenForm() {
        $this->Azzera();
        $sql = "SELECT * FROM ANAENT ORDER BY ROWID";
        try {   // Effettuo la FIND
            $anaent_tab = ItaDB::DBSQLSelect($this->PROT_DB, $sql);
            $open_Info = 'Oggetto: visualizza parametri';
            $this->openRecord($this->PROT_DB, 'ANAENT', $open_Info);
            $Vuoto = Array('ROWID' => '', 'ENTDE1' => '', 'ENTDE2' => '', 'ENTDE3' => '',
                'ENTDE4' => '', 'ENTDE5' => '', 'ENTDE6' => '', 'ENTKEY' => '', 'ENTVAL' => '');
            for ($i = 1; $i <= self::MAX_ANAENT_REC; $i++) {
                $anaent_check = $this->proLib->getGenericTab("SELECT * FROM ANAENT WHERE ENTKEY='$i'", false);
//                if ($anaent_tab[$i - 1] == null) {
                if (!$anaent_check) {
                    $Vuoto['ENTKEY'] = $i;
                    $this->insertRecord($this->PROT_DB, 'ANAENT', $Vuoto, '');
                }
            }
            if ($anaent_tab != null) {
                Out::valore($this->nameForm . '_numeroProt', $anaent_tab[0]['ENTDE1']);
                if ($anaent_tab[0]['ENTDE2'] == "1") {
                    Out::valore($this->nameForm . '_ENTDE_2_1', '1');
                }
                if ($anaent_tab[0]['ENTDE3'] == "1") {
                    Out::valore($this->nameForm . '_ENTDE_3_1', '1');
                }
                if ($anaent_tab[0]['ENTDE4'] == "1") {
                    Out::valore($this->nameForm . '_ENTDE_4_1', '1');
                } else if ($anaent_tab[0]['ENTDE4'] == "2") {
                    Out::valore($this->nameForm . '_ENTDE_4_1', '2');
                } else {
                    Out::valore($this->nameForm . '_ENTDE_4_1', '0');
                }
                if ($anaent_tab[0]['ENTDE5'] == "1") {
                    Out::valore($this->nameForm . '_ENTDE_5_1', '1');
                }
                if (substr(trim($anaent_tab[0]['ENTDE6']), 0, 1) == "1") {
                    Out::valore($this->nameForm . '_ENTDE_6A_1', '1');
                }
                if (substr(trim($anaent_tab[0]['ENTDE6']), 1, 1) == "1") {
                    Out::valore($this->nameForm . '_ENTDE_6B_1', '1');
                }
                Out::valore($this->nameForm . '_ENTDE_1_2', $anaent_tab[1]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_2', $anaent_tab[1]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_2', $anaent_tab[1]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_1_3', $anaent_tab[2]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_3', $anaent_tab[2]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_2_11', $anaent_tab[10]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_11', $anaent_tab[10]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_2_12', $anaent_tab[11]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_12', $anaent_tab[11]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_2_13', $anaent_tab[12]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_13', $anaent_tab[12]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_2_14', $anaent_tab[13]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_14', $anaent_tab[13]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_4_15', $anaent_tab[14]['ENTDE4']);
                Out::valore($this->nameForm . '_numeroComunicazioni', $anaent_tab[22]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_1_24', $anaent_tab[23]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_24', $anaent_tab[23]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_24', $anaent_tab[23]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_24', $anaent_tab[23]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_24', $anaent_tab[23]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_24', $anaent_tab[23]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_1_25', $anaent_tab[24]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_1_26', $anaent_tab[25]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_26', $anaent_tab[25]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_26', $anaent_tab[25]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_26', $anaent_tab[25]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_26', $anaent_tab[25]['ENTDE5']); /* Nuovo 18.02.2016 */
                Out::valore($this->nameForm . '_ENTDE_6_26', $anaent_tab[25]['ENTDE6']); /* Nuovo 04.05.2016 */
                Out::valore($this->nameForm . '_ENTVAL_26', $anaent_tab[25]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_27', $anaent_tab[26]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_27', $anaent_tab[26]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_27', $anaent_tab[26]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_27', $anaent_tab[26]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_27', $anaent_tab[26]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_27', $anaent_tab[26]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_3_28', $anaent_tab[27]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_28', $anaent_tab[27]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_28', $anaent_tab[27]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_28', $anaent_tab[27]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_1_29', $anaent_tab[28]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_29', $anaent_tab[28]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_29', $anaent_tab[28]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_29', $anaent_tab[28]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_29', $anaent_tab[28]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTVAL_29', $anaent_tab[28]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_30', $anaent_tab[29]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_30', $anaent_tab[29]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_30', $anaent_tab[29]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_1_31', $anaent_tab[30]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_31', $anaent_tab[30]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_31', $anaent_tab[30]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_31', $anaent_tab[30]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_31', $anaent_tab[30]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTVAL_31', $anaent_tab[30]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_32', $anaent_tab[31]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_32', $anaent_tab[31]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_32', $anaent_tab[31]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_32', $anaent_tab[31]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_32', $anaent_tab[31]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_32', $anaent_tab[31]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_1_33', $anaent_tab[32]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_33', $anaent_tab[32]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_33', $anaent_tab[32]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_33', $anaent_tab[32]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_33', $anaent_tab[32]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_33', $anaent_tab[32]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_VAL_33', $anaent_tab[32]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_2_34', $anaent_tab[33]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_34', $anaent_tab[33]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_34', $anaent_tab[33]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_6_34', $anaent_tab[33]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_1_35', $anaent_tab[34]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_35', $anaent_tab[34]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_35', $anaent_tab[34]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_35', $anaent_tab[34]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_35', $anaent_tab[34]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_35', $anaent_tab[34]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_VAL_35', $anaent_tab[34]['ENTVAL']);

                Out::valore($this->nameForm . '_ENTDE_1_36', $anaent_tab[35]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_36', $anaent_tab[35]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_36', $anaent_tab[35]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_36', $anaent_tab[35]['ENTDE4']);

                Out::valore($this->nameForm . '_ENTDE_VAL_37', $anaent_tab[36]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_37', $anaent_tab[36]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_37', $anaent_tab[36]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_37', $anaent_tab[36]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_37', $anaent_tab[36]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_37', $anaent_tab[36]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_37', $anaent_tab[36]['ENTDE6']);

                Out::valore($this->nameForm . '_ENTDE_1_38', $anaent_tab[37]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_38', $anaent_tab[37]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_38', $anaent_tab[37]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_38', $anaent_tab[37]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_38', $anaent_tab[37]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_38', $anaent_tab[37]['ENTDE6']);

                Out::valore($this->nameForm . '_ENTDE_1_39', $anaent_tab[38]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_39', $anaent_tab[38]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_39', $anaent_tab[38]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_39', $anaent_tab[38]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_39', $anaent_tab[38]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_VAL_39', $anaent_tab[38]['ENTVAL']);

                Out::valore($this->nameForm . '_ENTDE_VAL_40', $anaent_tab[39]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_40', $anaent_tab[39]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_40', $anaent_tab[39]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_40', $anaent_tab[39]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_40', $anaent_tab[39]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_40', $anaent_tab[39]['ENTDE5']);

                // Parametri Registro Giornaliero
                Out::valore($this->nameForm . '_ENTDE_VAL_41', $anaent_tab[40]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_41', $anaent_tab[40]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_41', $anaent_tab[40]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_41', $anaent_tab[40]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_41', $anaent_tab[40]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_41', $anaent_tab[40]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_41', $anaent_tab[40]['ENTDE6']);

                Out::valore($this->nameForm . '_ENTDE_VAL_42', $anaent_tab[41]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_42', $anaent_tab[41]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_42', $anaent_tab[41]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_42', $anaent_tab[41]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_42', $anaent_tab[41]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_42', $anaent_tab[41]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_42', $anaent_tab[41]['ENTDE6']);

                Out::valore($this->nameForm . '_ENTDE_VAL_43', $anaent_tab[42]['ENTVAL']);

                // Parametri per Codici di Registro 
                Out::valore($this->nameForm . '_ENTDE_1_44', $anaent_tab[43]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_44', $anaent_tab[43]['ENTDE2']);


                //Parametri Fattura Elettronica 2
                Out::valore($this->nameForm . '_ENTDE_VAL_45', $anaent_tab[44]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_45', $anaent_tab[44]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_45', $anaent_tab[44]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_45', $anaent_tab[44]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_45', $anaent_tab[44]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_45', $anaent_tab[44]['ENTDE5']);

                //Parametri Fattura Elettronica 3 Ws Kibernetes
                Out::valore($this->nameForm . '_ENTDE_VAL_46', $anaent_tab[45]['ENTVAL']);
                Out::valore($this->nameForm . '_ENTDE_1_46', $anaent_tab[45]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_46', $anaent_tab[45]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_46', $anaent_tab[45]['ENTDE3']);

                /* Parametri Anaent Vari */
                Out::valore($this->nameForm . '_ENTDE_1_47', $anaent_tab[46]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_47', $anaent_tab[46]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_47', $anaent_tab[46]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_47', $anaent_tab[46]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_47', $anaent_tab[46]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_47', $anaent_tab[46]['ENTDE6']);
                //Out::valore($this->nameForm . '_ENTDE_VAL_47', $anaent_tab[46]['ENTVAL']);// Serialize segnature

                /* Altri Parametri Anaent Vari */
                Out::valore($this->nameForm . '_ENTDE_1_48', $anaent_tab[47]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_48', $anaent_tab[47]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_48', $anaent_tab[47]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_48', $anaent_tab[47]['ENTDE4']); /* Nuovo Parametro Doc. Formali prog. unico */
                Out::valore($this->nameForm . '_ENTDE_5_48', $anaent_tab[47]['ENTDE5']); /* Nuovo Parametro Visibilità anche dei protocolli Trasmessi(TRX) */
                Out::valore($this->nameForm . '_ENTDE_6_48', $anaent_tab[47]['ENTDE6']); /* Nuovo parametro per duplicazione allegati al duplica protocollo */
                Out::valore($this->nameForm . '_ENTDE_VAL_48', $anaent_tab[47]['ENTVAL']); /* Nuovo parametro gestione mail trasmissioni interne */


                /* Parametri Gestione Documentaria */
                Out::valore($this->nameForm . '_ENTDE_1_49', $anaent_tab[48]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_49', $anaent_tab[48]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_49', $anaent_tab[48]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_49', $anaent_tab[48]['ENTDE4']);

                /* Parametri Segnatura Fascicolo */
                Out::valore($this->nameForm . '_ENTDE_1_50', $anaent_tab[49]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_VAL_50', $anaent_tab[49]['ENTVAL']);

                /*
                 * Parametri Copia Analoga Documenti 
                 * Gestiti su "Gestione Marcature"
                 * 
                 * Out::valore($this->nameForm . '_ENTDE_VAL_51', $anaent_tab[50]['ENTVAL']);
                 * Out::valore($this->nameForm . '_ENTDE_1_51', $anaent_tab[50]['ENTDE1']);
                 * Out::valore($this->nameForm . '_ENTDE_2_51', $anaent_tab[50]['ENTDE2']);
                 * Out::valore($this->nameForm . '_ENTDE_3_51', $anaent_tab[50]['ENTDE3']);
                 */

                /* Parametri vari */
                // ENTVAL è serializzato per inoltro mail
                Out::valore($this->nameForm . '_ENTDE_1_52', $anaent_tab[51]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_52', $anaent_tab[51]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_52', $anaent_tab[51]['ENTDE3']);
                // Conservazione
                Out::valore($this->nameForm . '_ENTDE_4_52', $anaent_tab[51]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_52', $anaent_tab[51]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_52', $anaent_tab[51]['ENTDE6']);

                /* Parametri Conservazione */
                Out::valore($this->nameForm . '_ENTDE_1_53', $anaent_tab[52]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_53', $anaent_tab[52]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_53', $anaent_tab[52]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_53', $anaent_tab[52]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_53', $anaent_tab[52]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_53', $anaent_tab[52]['ENTDE6']);
                $this->DecodificaFlagEstensioni($anaent_tab[52]['ENTDE6']);

                /*
                 * 53 ENTVAL usato per Parametri Template Mail 
                 */
                // Out::valore($this->nameForm . '_ENTDE_VAL_53', $anaent_tab[52]['ENTVAL']);


                /*
                 * ENTVAL 54
                 * Parametri vari altri e mail 
                 */
                Out::valore($this->nameForm . '_ENTDE_1_54', $anaent_tab[53]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_54', $anaent_tab[53]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_54', $anaent_tab[53]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_54', $anaent_tab[53]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_54', $anaent_tab[53]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_54', $anaent_tab[53]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_VAL_54', $anaent_tab[53]['ENTVAL']);

                /*
                 * ENTVAL 55
                 * Parametri vari altri 
                 */
                Out::valore($this->nameForm . '_ENTDE_1_55', $anaent_tab[54]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_55', $anaent_tab[54]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_55', $anaent_tab[54]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_55', $anaent_tab[54]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_VAL_55', $anaent_tab[54]['ENTVAL']);
                // _ENTDE_5_55 --- Utilizzato su Segnature.

                /*
                 * ENTVAL 56
                 * Parametri vari altri 
                 */
                // 1 e 2 Valorizzati su Marcature.
                Out::valore($this->nameForm . '_ENTDE_3_56', $anaent_tab[55]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_56', $anaent_tab[55]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_56', $anaent_tab[55]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_56', $anaent_tab[55]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTVAL_56', $anaent_tab[55]['ENTVAL']);


                /*
                 * ENTVAL 57
                 * Parametri scanner 
                 */
                Out::valore($this->nameForm . '_ENTDE_1_57', $anaent_tab[56]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_57', $anaent_tab[56]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_57', $anaent_tab[56]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_57', $anaent_tab[56]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_57', $anaent_tab[56]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_57', $anaent_tab[56]['ENTDE6']);
                Out::valore($this->nameForm . '_ENTDE_VAL_57', $anaent_tab[56]['ENTVAL']);
                /*
                 * ENTVAL 58
                 * Parametri vari 
                 */
                Out::valore($this->nameForm . '_ENTDE_1_58', $anaent_tab[57]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_58', $anaent_tab[57]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_3_58', $anaent_tab[57]['ENTDE3']);
                Out::valore($this->nameForm . '_ENTDE_4_58', $anaent_tab[57]['ENTDE4']);
                Out::valore($this->nameForm . '_ENTDE_5_58', $anaent_tab[57]['ENTDE5']);
                Out::valore($this->nameForm . '_ENTDE_6_58', $anaent_tab[57]['ENTDE6']);
                /*
                 * ENTVAL 59
                 * Parametri vari 
                 */
                Out::valore($this->nameForm . '_ENTDE_1_59', $anaent_tab[58]['ENTDE1']);
                Out::valore($this->nameForm . '_ENTDE_2_59', $anaent_tab[58]['ENTDE2']);
                Out::valore($this->nameForm . '_ENTDE_4_59', $anaent_tab[58]['ENTDE4']);
                /*
                 * ENTVAL 61
                 */
                Out::valore($this->nameForm . '_ENTDE_1_61', $anaent_tab[60]['ENTDE1']);
                

                if (!$anaent_tab[37]['ENTDE6']) {
                    Out::hide($this->nameForm . '_ENTDE_1_39_field');
                }
                $this->DecodAnauff($anaent_tab[29]['ENTDE3']);
                $this->DecodAnauff2($anaent_tab[33]['ENTDE1']);
                $this->caricaAccountGrid();
                $this->caricaMailSdi();
                $this->caricaMailGiornaliero();
                $this->caricaMailInoltro(); //Carico Mail di Inoltro Speciali.
                $this->decodFirmatarioRegistroProtocollo($anaent_tab[40]);
                $this->decodResponsabileRegistroProtocollo($anaent_tab[41]);
                $this->decodFirmatarioDefault($anaent_tab[51]);
                $this->caricaTemplateMail();
                $this->CaricaEstensioniConservazione();
                if ($anaent_tab[41]['ENTVAL']) {
                    $this->DecodAnacat($anaent_tab[41]['ENTVAL']);
                    if ($anaent_tab[40]['ENTDE5']) {
                        $this->DecodAnacla($anaent_tab[41]['ENTVAL'] . $anaent_tab[40]['ENTDE5']);
                        if ($anaent_tab[40]['ENTDE6']) {
                            $this->DecodAnafas($anaent_tab[41]['ENTVAL'] . $anaent_tab[40]['ENTDE5'] . $anaent_tab[40]['ENTDE6'], 'fasccf');
                        }
                    }
                }
                // Titolario mail
                if ($anaent_tab[53]['ENTDE1']) {
                    $this->DecodAnacatMail($anaent_tab[53]['ENTDE1']);
                    if ($anaent_tab[53]['ENTDE2']) {
                        $this->DecodAnaclaMail($anaent_tab[53]['ENTDE1'] . $anaent_tab[53]['ENTDE2']);
                        if ($anaent_tab[53]['ENTDE3']) {
                            $this->DecodAnafasMail($anaent_tab[53]['ENTDE1'] . $anaent_tab[53]['ENTDE2'] . $anaent_tab[53]['ENTDE3'], 'fasccf');
                        }
                    }
                }

                if ($anaent_tab[28]['ENTDE3'] != '') {
                    Out::valore($this->nameForm . '_StatoProtocollo', 'IN MANUTENZIONE');
                } else {
                    Out::valore($this->nameForm . '_StatoProtocollo', 'ATTIVO');
                }
            }

            Out::show($this->divGes);
            Out::show($this->nameForm . '_Aggiorna');
            Out::show($this->nameForm);
//            Out::codice('tinyActivate("' . $this->nameForm . '_ENTVAL_29");');
            Out::setFocus('', $this->nameForm . '_Aggiorna');
        } catch (Exception $e) {
            Out::msgStop("Errore di Connessione al DB.", $e->getMessage());
        }
    }

    private function DecodAnauff($codice, $tipo = 'codice') {
        $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        Out::valore($this->nameForm . '_ENTDE_3_30', $anauff_rec['UFFCOD']);
        Out::valore($this->nameForm . '_DecodUff', $anauff_rec['UFFDES']);
        return $anauff_rec;
    }

    private function DecodAnauff2($codice, $tipo = 'codice') {
        $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        Out::valore($this->nameForm . '_ENTDE_1_34', $anauff_rec['UFFCOD']);
        Out::valore($this->nameForm . '_DecodUff2', $anauff_rec['UFFDES']);
        return $anauff_rec;
    }

    private function DecodAnauffAbOggetto($codice, $tipo = 'codice') {
        $anauff_rec = $this->proLib->GetAnauff($codice, $tipo);
        Out::valore($this->nameForm . '_ENTDE_5_31', $anauff_rec['UFFCOD']);
        return $anauff_rec;
    }

    private function caricaAccountGrid() {
        $anaent_28 = $this->proLib->GetAnaent('28');
        $this->account = unserialize($anaent_28['ENTVAL']);
        if ($this->account) {
            $this->CaricaGriglia($this->gridAccountMail, $this->account);
        }
    }

    private function decodFirmatarioRegistroProtocollo($anaent_rec) {

        if ($anaent_rec['ENTDE1']) {
            $anamed_rec = $this->proLib->GetAnamed($anaent_rec['ENTDE1'], 'codice', 'si');
            Out::valore($this->nameForm . '_ENTDE_1_41', $anamed_rec["MEDCOD"]);
            Out::valore($this->nameForm . "_DESCRIZIONE_FRIMATARIO", $anamed_rec["MEDNOM"]);
        }
        if ($anaent_rec['ENTDE2']) {
            $anauff_rec = $this->proLib->GetAnauff($anaent_rec['ENTDE2'], 'codice');
            Out::valore($this->nameForm . '_ENTDE_2_41', $anauff_rec['UFFCOD']);
            Out::valore($this->nameForm . '_UFFICIO_FIRMATARIO', $anauff_rec['UFFDES']);
        }
    }

    private function decodFirmatarioDefault($anaent_rec) {
        if ($anaent_rec['ENTDE1']) {
            $anamed_rec = $this->proLib->GetAnamed($anaent_rec['ENTDE1'], 'codice', 'si');
            Out::valore($this->nameForm . '_ENTDE_1_51', $anamed_rec["MEDCOD"]);
            Out::valore($this->nameForm . "_DESC_FIRMATARIO_DEFAULT", $anamed_rec["MEDNOM"]);
        }
        if ($anaent_rec['ENTDE2']) {
            $anauff_rec = $this->proLib->GetAnauff($anaent_rec['ENTDE2'], 'codice');
            Out::valore($this->nameForm . '_ENTDE_2_52', $anauff_rec['UFFCOD']);
            Out::valore($this->nameForm . '_UFF_FIRMATARIO_DEFAULT', $anauff_rec['UFFDES']);
        }
    }

    private function decodResponsabileRegistroProtocollo($anaent_rec) {

        if ($anaent_rec['ENTDE2']) {
            $anamed_rec = $this->proLib->GetAnamed($anaent_rec['ENTDE2'], 'codice', 'si');
            Out::valore($this->nameForm . '_ENTDE_2_42', $anamed_rec["MEDCOD"]);
            Out::valore($this->nameForm . "_DESCRIZIONE_RESPONSABILE", $anamed_rec["MEDNOM"]);
        }
        if ($anaent_rec['ENTDE3']) {
            $anauff_rec = $this->proLib->GetAnauff($anaent_rec['ENTDE3'], 'codice');
            Out::valore($this->nameForm . '_ENTDE_3_42', $anauff_rec['UFFCOD']);
            Out::valore($this->nameForm . '_UFFICIO_RESPONSABILE', $anauff_rec['UFFDES']);
        }
    }

    private function caricaMailSdi() {
        $anaent_38 = $this->proLib->GetAnaent('38');
        $this->mailSdi = unserialize($anaent_38['ENTVAL']);
        if ($this->mailSdi) {
            $this->CaricaGriglia($this->gridMailSdi, $this->mailSdi);
        }
    }

    private function caricaMailGiornaliero() {
        $anaent_43 = $this->proLib->GetAnaent('43');
        $this->mailGiornaliero = unserialize($anaent_43['ENTVAL']);
        if ($this->mailGiornaliero) {
            $this->CaricaGriglia($this->gridMailGiornaliero, $this->mailGiornaliero);
        }
    }

    private function caricaMailInoltro() {
        $anaent_52 = $this->proLib->GetAnaent('52');
        $this->elencoMailInoltro = unserialize($anaent_52['ENTVAL']);
        if ($this->elencoMailInoltro) {
            $this->CaricaGriglia($this->gridMailInoltro, $this->elencoMailInoltro);
        }
    }

    private function CaricaGriglia($griglia, $appoggio) {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(1000);
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    private function embedVars($ritorno, $tipoDiz = 'segnatura') {
        $proLibVar = new proLibVariabili();
        switch ($tipoDiz) {
            case 'segnatura':
                $dictionaryLegend = $proLibVar->getLegendaSegnatura('adjacency', 'smarty');
                docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
                break;

            case 'baseprotocollo':
                $dictionaryLegend = $proLibVar->getLegendaCampiProtocollo('adjacency', 'smarty');
                $model = 'docVarsBrowser';
                $_POST = array();
                $_POST['event'] = 'openform';
                $_POST['dictionaryLegend'] = $dictionaryLegend;
                $_POST['editorId'] = $this->nameForm . '_ENTVAL_29';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
        }
        return true;
    }

    private function embedVarsFas($ritorno) {
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabiliFascicolo.class.php';
        $proLibVarFascicolo = new proLibVariabiliFascicolo();
        $dictionaryLegend = $proLibVarFascicolo->getLegenda('adjacency', 'smarty');
        docRic::ricVariabili($dictionaryLegend, $this->nameForm, $ritorno, true);
        return true;
    }

    public function GetUfficiAnamed($codice) {
        $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
        return $uffdes_tab;
    }

    public function CtrFirmatario() {
        // Controlli attivi solo se almeno 1 dei due è valorizzato:
        if ($_POST[$this->nameForm . '_ENTDE_1_41'] || $_POST[$this->nameForm . '_ENTDE_2_41']) {
            if ($_POST[$this->nameForm . '_ENTDE_1_41'] && !$_POST[$this->nameForm . '_ENTDE_2_41']) {
                Out::msgInfo("Attenzione", "Occorre indicare anche l'ufficio del firmatario, nel Registro Protocollo.");
                return false;
            }
            if (!$_POST[$this->nameForm . '_ENTDE_1_41'] && $_POST[$this->nameForm . '_ENTDE_2_41']) {
                Out::msgInfo("Attenzione", "Occorre indicare anche il firmatario, nel Registro Protocollo.");
                return false;
            }
            $uffdes_tab = $this->GetUfficiAnamed($_POST[$this->nameForm . '_ENTDE_1_41']);
            $trovato = false;
            foreach ($uffdes_tab as $ufficio) {
                if ($ufficio['UFFCOD'] == $_POST[$this->nameForm . '_ENTDE_2_41']) {
                    $trovato = true;
                    break;
                }
            }
            if (!$trovato) {
                Out::msgInfo("Attenzione", "Il firmatario non fa pare dell'ufficio indicato.");
                return false;
            }
        }

        return true;
    }

    public function CtrResponsabile() {
        // Controlli attivi solo se almeno 1 dei due è valorizzato:
        if ($_POST[$this->nameForm . '_ENTDE_2_42'] || $_POST[$this->nameForm . '_ENTDE_3_42']) {
            if ($_POST[$this->nameForm . '_ENTDE_2_42'] && !$_POST[$this->nameForm . '_ENTDE_3_42']) {
                Out::msgInfo("Attenzione", "Occorre indicare anche l'ufficio del responsabile, nel Registro Protocollo.");
                return false;
            }
            if (!$_POST[$this->nameForm . '_ENTDE_2_42'] && $_POST[$this->nameForm . '_ENTDE_3_42']) {
                Out::msgInfo("Attenzione", "Occorre indicare anche il responsabile, nel Registro Protocollo.");
                return false;
            }
            $uffdes_tab = $this->GetUfficiAnamed($_POST[$this->nameForm . '_ENTDE_2_42']);
            $trovato = false;
            foreach ($uffdes_tab as $ufficio) {
                if ($ufficio['UFFCOD'] == $_POST[$this->nameForm . '_ENTDE_3_42']) {
                    $trovato = true;
                    break;
                }
            }
            if (!$trovato) {
                Out::msgInfo("Attenzione", "Il responsabile non fa pare dell'ufficio indicato.");
                return false;
            }
        }

        return true;
    }

    private function decodTitolario($rowid, $tipoArc) {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_ENTDE_VAL_42', $cat);
        Out::valore($this->nameForm . '_ENTDE_5_41', $cla);
        Out::valore($this->nameForm . '_ENTDE_6_41', $fas);
        Out::valore($this->nameForm . '_TitolarioDecod', $des);
    }

    private function DecodAnacat($codice, $tipo = 'codice') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolario($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_ENTDE_VAL_42', '');
            Out::valore($this->nameForm . '_ENTDE_5_41', '');
            Out::valore($this->nameForm . '_ENTDE_6_41', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anacat_rec;
    }

    private function DecodAnacla($codice, $tipo = 'codice') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolario($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_ENTDE_VAL_42', '');
            Out::valore($this->nameForm . '_ENTDE_5_41', '');
            Out::valore($this->nameForm . '_ENTDE_6_41', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anacla_rec;
    }

    private function DecodAnafas($codice, $tipo = 'codice') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolario($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_ENTDE_VAL_42', '');
            Out::valore($this->nameForm . '_ENTDE_5_41', '');
            Out::valore($this->nameForm . '_ENTDE_6_41', '');
            Out::valore($this->nameForm . '_TitolarioDecod', '');
        }
        return $anafas_rec;
    }

    private function decodTitolarioMail($rowid, $tipoArc) {
        $cat = $cla = $fas = $org = $des = '';
        switch ($tipoArc) {
            case 'ANACAT':
                $anacat_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
                $cat = $anacat_rec['CATCOD'];
                $des = $anacat_rec['CATDES'];
                break;
            case 'ANACLA':
                $anacla_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
                $cat = $anacla_rec['CLACAT'];
                $cla = $anacla_rec['CLACOD'];
                $des = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                break;
            case 'ANAFAS':
                $anafas_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
                $cat = substr($anafas_rec['FASCCA'], 0, 4);
                $cla = substr($anafas_rec['FASCCA'], 4);
                $fas = $anafas_rec['FASCOD'];
                $des = $anafas_rec['FASDES'];
                break;
        }
        Out::valore($this->nameForm . '_ENTDE_1_54', $cat);
        Out::valore($this->nameForm . '_ENTDE_2_54', $cla);
        Out::valore($this->nameForm . '_ENTDE_3_54', $fas);
        Out::valore($this->nameForm . '_TitolarioDecodMail', $des);
    }

    private function DecodAnacatMail($codice, $tipo = 'codice') {
        $anacat_rec = $this->proLib->GetAnacat('', $codice, $tipo);
        if ($anacat_rec) {
            $this->decodTitolarioMail($anacat_rec['ROWID'], 'ANACAT');
        } else {
            Out::valore($this->nameForm . '_ENTDE_1_54', '');
            Out::valore($this->nameForm . '_ENTDE_2_54', '');
            Out::valore($this->nameForm . '_ENTDE_3_54', '');
            Out::valore($this->nameForm . '_TitolarioDecodMail', '');
        }
        return $anacat_rec;
    }

    private function DecodAnaclaMail($codice, $tipo = 'codice') {
        $anacla_rec = $this->proLib->GetAnacla('', $codice, $tipo);
        if ($anacla_rec) {
            $this->decodTitolarioMail($anacla_rec['ROWID'], 'ANACLA');
        } else {
            Out::valore($this->nameForm . '_ENTDE_1_54', '');
            Out::valore($this->nameForm . '_ENTDE_2_54', '');
            Out::valore($this->nameForm . '_ENTDE_3_54', '');
            Out::valore($this->nameForm . '_TitolarioDecodMail', '');
        }
        return $anacla_rec;
    }

    private function DecodAnafasMail($codice, $tipo = 'codice') {
        $anafas_rec = $this->proLib->GetAnafas('', $codice, $tipo);
        if ($anafas_rec) {
            $this->decodTitolarioMail($anafas_rec['ROWID'], 'ANAFAS');
        } else {
            Out::valore($this->nameForm . '_ENTDE_1_54', '');
            Out::valore($this->nameForm . '_ENTDE_2_54', '');
            Out::valore($this->nameForm . '_ENTDE_3_54', '');
            Out::valore($this->nameForm . '_TitolarioDecodMail', '');
        }
        return $anafas_rec;
    }

    private function caricaTemplateMail() {
        $arrTemplateMail = array();
        $arrTemplateMail[1] = array('ROWID' => 1, 'TEMPLATE' => 'Mail di Risposta per Conferma Ricezione');
        $arrTemplateMail[2] = array('ROWID' => 2, 'TEMPLATE' => 'Mail di Notifica Protocollo');
        $this->arrTemplateMail = $arrTemplateMail;
        $this->CaricaGriglia($this->gridTemplateMail, $this->arrTemplateMail);

        // Verifico ENVTAL 29 primo utilizzo nuova funzione template modelli.
        $anaent_29 = $this->proLib->GetAnaent('29');
        $anaent_53 = $this->proLib->GetAnaent('53');
        $ElencoTemplate = unserialize($anaent_53['ENTVAL']);
        if ($anaent_29['ENTVAL'] && !isset($ElencoTemplate[1])) {
            if ($anaent_29['ENTVAL'] != '1' && $anaent_29['ENTVAL'] != '0') {
                $ElencoTemplate[1]['OGGETTOMAIL'] = '';
                $ElencoTemplate[1]['BODYMAIL'] = $anaent_29['ENTVAL'];
                $anaent_53['ENTVAL'] = serialize($ElencoTemplate);
                // Aggiorno i dati mail:
                $update_Info = 'Aggiornameto Template mail: ' . $anaent_53['ENTKEY'];
                $this->updateRecord($this->PROT_DB, 'ANAENT', $anaent_53, $update_Info);
            }
        }
    }

    public function ApriAccountMail() {
        $rowid = $_POST['rowid'];
        itaLib::openDialog('proAccMail', true);
        /* @var $proAccMail proAccMail */
        $proAccMail = itaModel::getInstance('proAccMail');
        $proAccMail->setEvent('openform');
        $proAccMail->setIndiceMail($rowid);
        $proAccMail->setReturnEvent('retAggAccountMail');
        $proAccMail->setReturnModel($this->nameForm);
        $proAccMail->setMailAccount($this->account);
        $proAccMail->parseEvent();
    }

    public function CaricaEstensioniConservazione() {
        $this->estensioniConservazione = $this->proLib->GetProExtConser('', '', true);
        $this->CaricaGriglia($this->gridEstensioni, $this->estensioniConservazione);
    }

    public function InputEstensione($rowid = '') {
        $ProExtConser_rec = array();
        if ($rowid) {
            $ProExtConser_rec = $this->proLib->GetProExtConser($rowid, 'rowid');
        }


        $valori[] = array(
            'label' => array(
                'value' => "Estensione:",
                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_PROEXTCONSER[ESTENSIONE]',
            'name' => $this->nameForm . '_PROEXTCONSER[ESTENSIONE]',
            'type' => 'text',
            'style' => 'margin:2px;width:80px;',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Descrizione:",
                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_PROEXTCONSER[DESCRIZIONE]',
            'name' => $this->nameForm . '_PROEXTCONSER[DESCRIZIONE]',
            'type' => 'text',
            'style' => 'margin:2px;width:380px;',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Protocollabile:",
                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_PROEXTCONSER[EXTPROTO]',
            'name' => $this->nameForm . '_PROEXTCONSER[EXTPROTO]',
            'type' => 'checkbox',
            'style' => 'margin:2px;',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "Conservabile:",
                'style' => 'width:110px;maxlength:6;size:50;display:block;float:left;padding: 0 5px 0 0;text-align: right;'
            ),
            'id' => $this->nameForm . '_PROEXTCONSER[EXTCONSER]',
            'name' => $this->nameForm . '_PROEXTCONSER[EXTCONSER]',
            'type' => 'checkbox',
            'style' => 'margin:2px;',
            'value' => ''
        );
        $valori[] = array(
            'label' => array(
                'value' => "",
                'style' => 'display:none'
            ),
            'id' => $this->nameForm . '_PROEXTCONSER[ROW_ID]',
            'name' => $this->nameForm . '_PROEXTCONSER[ROW_ID]',
            'type' => 'text',
            'style' => 'display:none',
            'value' => null
        );
        Out::msgInput(
                'Gestione Estensione', $valori
                , array(
            'Conferma' => array('id' => $this->nameForm . '_ConfermaEstensione',
                'model' => $this->nameForm)
                ), $this->nameForm . "_workSpace"
        );

        Out::valori($ProExtConser_rec, $this->nameForm . '_PROEXTCONSER');
    }

    public function DecodificaFlagEstensioni($FlagExt = '') {
        if ($FlagExt) {
            $MsgExt = 'La conservazione sarà effettuata anche in presenza di allegati non conservabili che saranno esclusi';
        } else {
            $MsgExt = 'La conservazione sarà effettuata solo se tutti gli allegati sono di tipo conservabile';
        }
        Out::html($this->nameForm . '_spanInfoEstensioni', $MsgExt);
    }

    public function CaricaSubFormMetaData($metadati = array()) {
        $gridMetadata = array();
        foreach ($metadati as $key => $value) {
            $gridMetadata[] = array('CHIAVE' => $key, 'VALORE' => $value);
        }

        $model = 'utiGridMetaData';

        /* @var $utiGridMetaData utiGridMetaData */
        $utiGridMetaData = itaFormHelper::innerForm($model, $this->nameForm . '_divDatiNamirial');
        $utiGridMetaData->setMetaData($gridMetadata);
        $utiGridMetaData->setEvent('openform');
        $utiGridMetaData->parseEvent();
        $this->utiGridMetaDataNameForm = $utiGridMetaData->getNameForm();
    }

    public function AggiornaParamNamirial() {
        $utiGridMetaData = itaModel::getInstance('utiGridMetaData', $this->utiGridMetaDataNameForm);
        $gridMetadata = $utiGridMetaData->getMetaData();
        $gridMetadata = array_combine(array_column($gridMetadata, 'CHIAVE'), array_column($gridMetadata, 'VALORE'));
        $arr = json_encode($gridMetadata);

        $AmtAnaTipoDocDag_rec = $_POST[$this->nameForm . '_amt_tipo_documento_dag'];
        $AmtAnaTipoDocDag_rec['id_tipo_documento'] = $this->IdTipoDoc;
        if ($_POST[$this->nameForm . '_amt_tipo_documento_dag']['tipo'] == 'DECIMALE') {
            $AmtAnaTipoDocDag_rec['meta'] = json_encode(array('tipo' => array(
                    'decimali' => $_POST[$this->nameForm . '_decimali']
            )));
        }
        if ($_POST[$this->nameForm . '_amt_tipo_documento_dag']['tipo'] == 'SELECT') {
            $AmtAnaTipoDocDag_rec['meta'] = json_encode(array('tipo' => array(
                    'select' => $arr
            )));
        }
        $AmtAnaTipoDocDag_rec['utente_umod'] = App::$utente->getKey('nomeUtente');
        $AmtAnaTipoDocDag_rec['data_umod'] = date("Ymd");
//        if (!$this->insertRecord($this->bdaLib->getBDAPDB(), 'amt_tipo_documento_dag', $AmtAnaTipoDocDag_rec, 'Inserimento MetaDati', 'id')) {
//            
//        }



        $utiGridMetaData = itaModel::getInstance('utiGridMetaData', $this->utiGridMetaDataNameForm);
        $gridMetadata = $utiGridMetaData->getMetaData();
        $gridMetadata = array_combine(array_column($gridMetadata, 'CHIAVE'), array_column($gridMetadata, 'VALORE'));
        $arr = json_encode($gridMetadata);

        $AmtAnaTipoDocDag_rec = $_POST[$this->nameForm . '_amt_tipo_documento_dag'];
        $AmtAnaTipoDocDag_rec['id_tipo_documento'] = $this->IdTipoDoc;
        if ($_POST[$this->nameForm . '_amt_tipo_documento_dag']['tipo'] == 'DECIMALE') {
            $AmtAnaTipoDocDag_rec['meta'] = json_encode(array('tipo' => array(
                    'decimali' => $_POST[$this->nameForm . '_decimali']
            )));
        }
        if ($_POST[$this->nameForm . '_amt_tipo_documento_dag']['tipo'] == 'SELECT') {
            $AmtAnaTipoDocDag_rec['meta'] = json_encode(array('tipo' => array(
                    'select' => $arr
            )));
        }
        $AmtAnaTipoDocDag_rec['utente_umod'] = App::$utente->getKey('nomeUtente');
        $AmtAnaTipoDocDag_rec['data_umod'] = date("Ymd");
//        if (!$this->updateRecord($this->bdaLib->getBDAPDB(), 'amt_tipo_documento_dag', $AmtAnaTipoDocDag_rec, '', 'id')) {
//            
//        }
    }

}

?>
