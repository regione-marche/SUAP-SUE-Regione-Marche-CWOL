<?php

/* * 
 *
 * GESTIONE PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    27.03.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Menu/menLib.class.php';

function proSubMittDest() {
    $proSubMittDest = new proSubMittDest();
    $proSubMittDest->parseEvent();
    return;
}

class proSubMittDest extends itaModel {

    public $PROT_DB;
    public $nameForm = "proSubMittDest";
    public $proLib;
    public $proLibGiornaliero;
    public $gridAltriDestinatari = "proSubMittDest_gridAltriDestinatari";
    public $proAltriDestinatari = array();
    public $tipoProt;
    public $anapro_rec;
    public $elaboraAltriDest;
    public $medcodOLD;
    public $consultazione;
    public $altriDestPerms;
    public $mittentiAggiuntivi;
    public $disabledRec;
    public $bloccoDaEmail;
    public $annullato;
    public $nameFormChiamante;

    function __construct() {
        parent::__construct();
        try {
            
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function postInstance() {
        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);

        $this->proLib = new proLib();
        $this->proLibGiornaliero = new proLibGiornaliero();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->gridAltriDestinatari = $this->nameForm . '_gridAltriDestinatari';
        $this->proAltriDestinatari = App::$utente->getKey($this->nameForm . '_proAltriDestinatari');
        $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
        $this->anapro_rec = App::$utente->getKey($this->nameForm . '_anapro_rec');
        $this->elaboraAltriDest = App::$utente->getKey($this->nameForm . '_elaboraAltriDest');
        $this->medcodOLD = App::$utente->getKey($this->nameForm . '_medcodOLD');
        $this->consultazione = App::$utente->getKey($this->nameForm . '_consultazione');
        $this->altriDestPerms = App::$utente->getKey($this->nameForm . '_altriDestPerms');
        $this->mittentiAggiuntivi = App::$utente->getKey($this->nameForm . '_mittentiAggiuntivi');
        $this->disabledRec = App::$utente->getKey($this->nameForm . '_disabledRec');
        $this->bloccoDaEmail = App::$utente->getKey($this->nameForm . '_bloccoDaEmail');
        $this->annullato = App::$utente->getKey($this->nameForm . '_annullato');
        $this->nameFormChiamante = App::$utente->getKey($this->nameForm . '_nameFormChiamante');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_proAltriDestinatari", $this->proAltriDestinatari);
            App::$utente->setKey($this->nameForm . "_tipoProt", $this->tipoProt);
            App::$utente->setKey($this->nameForm . "_anapro_rec", $this->anapro_rec);
            App::$utente->setKey($this->nameForm . "_elaboraAltriDest", $this->elaboraAltriDest);
            App::$utente->setKey($this->nameForm . "_medcodOLD", $this->medcodOLD);
            App::$utente->setKey($this->nameForm . "_consultazione", $this->consultazione);
            App::$utente->setKey($this->nameForm . "_altriDestPerms", $this->altriDestPerms);
            App::$utente->setKey($this->nameForm . "_mittentiAggiuntivi", $this->mittentiAggiuntivi);
            App::$utente->setKey($this->nameForm . "_disabledRec", $this->disabledRec);
            App::$utente->setKey($this->nameForm . "_bloccoDaEmail", $this->bloccoDaEmail);
            App::$utente->setKey($this->nameForm . "_annullato", $this->annullato);
            App::$utente->setKey($this->nameForm . "_nameFormChiamante", $this->nameFormChiamante);
        }
    }

    public function setTipoProt($tipoProt) {
        $this->tipoProt = $tipoProt;
    }

    public function setAnapro_rec($anapro_rec) {
        $this->anapro_rec = $anapro_rec;
    }

    public function setElaboraAltriDest($elaboraAltriDest) {
        $this->elaboraAltriDest = $elaboraAltriDest;
    }

    public function setConsultazione($consultazione) {
        $this->consultazione = $consultazione;
    }

    public function setDisabledRec($disabledRec) {
        $this->disabledRec = $disabledRec;
    }

    public function setBloccoDaEmail($bloccoDaEmail) {
        $this->bloccoDaEmail = $bloccoDaEmail;
    }

    public function setAnnullato($annullato) {
        $this->annullato = $annullato;
    }

    public function setNameFormChiamante($nameFormChiamante) {
        $this->nameFormChiamante = $nameFormChiamante;
    }

    public function getProAltriDestinatari() {
        return $this->proAltriDestinatari;
    }

    public function getMittentiAggiuntivi() {
        return $this->mittentiAggiuntivi;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                /*
                 * Funzioni di Rimozione/Nascondi campi
                 */
                Out::attributo($this->gridAltriDestinatari . '_exportTableToExcel', 'title', 0, 'Importa CSV');
                Out::delClass($this->nameForm . '_workSpace', 'ui-widget-content');
                Out::hide($this->nameForm . '_altriDest_field');
                $anaent_40 = $this->proLib->GetAnaent('40');
                if (!$anaent_40['ENTDE3']) {
                    Out::removeElement($this->nameForm . "_DestSimple");
                }
                /*
                 * Pulizia Variabili:
                 */
                $this->proAltriDestinatari = array();
                $this->mittentiAggiuntivi = array();
                /*
                 * Imposto uppercase se parametrizzato
                 */
                $anaent_37 = $this->proLib->GetAnaent('37');
                if ($anaent_37['ENTDE3'] == 1) {
                    Out::addClass($this->nameForm . '_Oggetto', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_ANAPRO[PROIND]', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-edit-uppercase");
                    Out::addClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-edit-uppercase");
                }
                Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
                $this->CaricaDivMittentiAggiuntivi();
                // Nascondo per futura gestione.
                Out::removeElement($this->nameForm . "_divCodFiscale");
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    case $this->gridAltriDestinatari:
                        if ($this->consultazione) {
                            $permessi = 'consultazione';
                        } else if ($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                            $permessi = 'consultazione';
                        } else {
                            $permessi = $this->altriDestPerms;
                        }
                        $model = 'proDettDestinatari';
                        $rowapp = $_POST['rowid'];
                        $_POST = array();
                        $_POST['destRowid'] = $rowapp;
                        $_POST[$model . '_proDettCampi'] = $this->proAltriDestinatari[$rowapp];
                        $_POST[$model . '_returnField'] = $this->nameForm . '_CaricaGridPart';
                        $_POST[$model . '_permessi'] = $permessi;
                        Out::closeDialog($model);
                        $_POST[$model . '_returnModel'] = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        );
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;

            case 'addGridRow':
                switch ($this->elementId) {
                    case $this->gridAltriDestinatari:
                        if ($this->consultazione != true) {
                            $_POST = array();
                            $model = 'proDettDestinatari';
                            $_POST[$model . '_proDettCampi'] = '';
                            $_POST[$model . '_returnField'] = $this->nameForm . '_CaricaGridPart';
                            $_POST['tipoProt'] = $this->tipoProt;
                            Out::closeDialog($model);
                            $_POST[$model . '_returnModel'] = array(
                                'nameForm' => $this->nameForm,
                                'nameFormOrig' => $this->nameFormOrig
                            );
                            $_POST['event'] = 'openform';
                            itaLib::openForm($model);
                            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                            $model();
                        }
                        break;
                }
                break;
            case 'delGridRow':
                switch ($this->elementId) {
                    case $this->gridAltriDestinatari:
                        if ($this->consultazione != true && $this->disabledRec != true) {
                            if ($this->bloccoDaEmail != true) {
                                if (array_key_exists($_POST['rowid'], $this->proAltriDestinatari) == true) {
                                    unset($this->proAltriDestinatari[$_POST['rowid']]);
                                    $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                                }
                            }
                        }
                        break;
                }

                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    case $this->gridAltriDestinatari:
                        $model = "utiUploadDiag";
                        itaLib::openDialog($model);
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura fallita");
                            break;
                        }

                        $formObj->setReturnModelOrig($this->nameFormOrig);
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('returnUploadCSV');
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;
                }
                break;

            case 'cellSelect':
                switch ($this->elementId) {
                    case $this->gridAltriDestinatari:
                        switch ($_POST['colName']) {
                            case 'MAILDEST':
                                if (array_key_exists($_POST['rowid'], $this->proAltriDestinatari) == true) {
                                    if (!$this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'SBLOCCA':
                                if (array_key_exists($_POST['rowid'], $this->proAltriDestinatari) == true) {
                                    if (!$this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                                        break;
                                    }
                                    $retRic = array();
                                    if ($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                                        $retRic = $this->proLib->checkMailRic($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']);
                                    }
                                    $messaggio = "<br><br>Procedura NON reversibile.<br>Sei sicuro di voler sbloccare i riferimenti dell'email per il destinatario?";
                                    if ($retRic['ACCETTAZIONE']) {
                                        if ($retRic['CONSEGNA']) {
                                            $messaggio .= "<br>Fare Attenzione perché la PEC è stata sia Accettata dal sistema che Consegnata al Destinatario!";
                                        } else {
                                            $messaggio .= "<br>Fate Attenzione perché la PEC è stata Accettata dal Sistema.<br>Se l'indirizzo di destinazione è una PEC per sicurezza è consigliabile scaricare la posta per verificare se è avvenuta la Consegna.";
                                        }
                                    }

                                    Out::msgQuestion("Attenzione.", $messaggio, array(
                                        'F9-Annulla' => array('id' => $this->nameForm . '_AnnullaSblocco',
                                            'model' => $this->nameForm, 'shortCut' => "f9"),
                                        'F5-Sblocca' => array('id' => $this->nameForm . '_SbloccaMailConfDaGriglia',
                                            'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                }
                                break;
                            case 'ACCETTAZIONE':
                                if (array_key_exists($_POST['rowid'], $this->proAltriDestinatari) == true) {
                                    if ($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRic($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']);
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
                            case 'CONSEGNA':
                                if (array_key_exists($_POST['rowid'], $this->proAltriDestinatari) == true) {
                                    if ($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRicMulti($this->proAltriDestinatari[$_POST['rowid']]['DESIDMAIL']);
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
                            CASE 'CONOSCENZA':
                                if (!isset($this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'])) {
                                    $this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'] = 0;
                                }
                                if ($this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'] == 0) {
                                    $this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'] = 1;
                                } else {
                                    $this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'] = 0;
                                }
                                if ($this->proAltriDestinatari[$_POST['rowid']]['DESCONOSCENZA'] == 1) {
                                    $this->proAltriDestinatari[$_POST['rowid']]['CONOSCENZA'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                                } else {
                                    $this->proAltriDestinatari[$_POST['rowid']]['CONOSCENZA'] = "";
                                }
                                $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                                break;

                            case 'NOTIFICAPEC':
                                $IdMailPadre = $this->proAltriDestinatari[$_POST['rowid']]['IDNOTIFICAPEC'];
                                $retRic = $this->proLib->checkMailRicMulti($IdMailPadre);
                                if (!$retRic['NOTIFICHE']) {
                                    break;
                                }
                                proRic::proRicNotificheMail(array(
                                    'nameForm' => $this->nameForm,
                                    'nameFormOrig' => $this->nameFormOrig
                                        ), $IdMailPadre);
                                break;
                        }
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($this->elementId) {
                    case $this->gridAltriDestinatari:

                        break;
                }
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_DestSimple':
                        if ($this->tipoProt != "P") {
                            break;
                        }
                        $arrParam = array();
                        $arrParam['PRINCIPALE'] = array(
                            'PRONOM' => $this->formData[$this->nameForm . '_ANAPRO']['PRONOM'],
                            'PROIND' => $this->formData[$this->nameForm . '_ANAPRO']['PROIND'],
                            'PROCIT' => $this->formData[$this->nameForm . '_ANAPRO']['PROCIT'],
                            'PROCAP' => $this->formData[$this->nameForm . '_ANAPRO']['PROCAP'],
                            'PROPRO' => $this->formData[$this->nameForm . '_ANAPRO']['PROPRO'],
                            'PROMAIL' => $this->formData[$this->nameForm . '_ANAPRO']['PROMAIL']
                        );
                        $arrParam['ALTRIDEST'] = $this->proAltriDestinatari;
                        /* @var $objModel proDestSimple */
                        $objModel = itaModel::getInstance('proDestSimple');
                        $objModel->setNumProt($this->anapro_rec['PRONUM']);
                        $objModel->setTipoProt($this->tipoProt);
                        $objModel->setDestinatari($arrParam);
                        $objModel->setEvent('openDestinatari');
                        $ReturnModel = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        );
                        $objModel->setReturnModel($ReturnModel);
                        $objModel->setReturnEvent('returnFromDestSimple');
                        $objModel->parseEvent();
                        break;

                    case $this->nameForm . '_ANAPRO[PRONOM]_butt':
                        if ($this->tipoProt == 'C') {
                            $filtroUff = " WHERE MEDUFF" . $this->PROT_DB->isNotBlank();
                        } else {
                            $filtroUff = '';
                        }
                        $mednom = $_POST[$this->nameForm . '_ANAPRO']['PRONOM'];
                        proRic::proRicAnamed(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                                ), $filtroUff, '');
                        Out::valore('gs_MEDNOM', $mednom);
                        Out::setFocus('', "gs_MEDNOM");
                        break;

                    case $this->nameForm . '_CercaAnagrafe':
                        $model = 'utiVediAnel';
                        itaLib::openDialog($model);
                        $modelObj = itaModel::getInstance($model);
                        $ReturnModel = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        );
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_ANAGPROARRI_PRONOM';
                        $modelObj->setReturnModel($ReturnModel);
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;

                    case $this->nameForm . '_CercaIPA':
                        $model = 'proRicIPA';
                        itaLib::openForm($model);
                        /* @var $modelObj itaModel */
                        $modelObj = itaModel::getInstance($model);
                        $ReturnModel = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        );
                        $modelObj->setReturnModel($ReturnModel);
                        $modelObj->setReturnEvent('returnRicIPA');
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;

                    case $this->nameForm . '_CercaAnagrafeAltri':
                        $model = 'utiVediAnel';
                        itaLib::openDialog($model);
                        $modelObj = itaModel::getInstance($model);
                        $ReturnModel = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        );
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['Ricerca'] = 1;
                        $_POST['returnBroadcast'] = 'PRENDI_ANAGPROARRI_ALTRI';
                        $modelObj->setReturnModel($ReturnModel);
                        $modelObj->setEvent('openform');
                        $modelObj->parseEvent();
                        break;

                    case $this->nameForm . '_AggiungiMittente':
                        $email = $_POST[$this->nameForm . '_ANAPRO']['PROMAIL'];
                        //Controllo se la mail è tra quelle sdi:
                        $anaent_38 = $this->proLib->GetAnaent('38');
                        if ($anaent_38) {
                            $ElencoMail = unserialize($anaent_38['ENTVAL']);
                            $presente = false;
                            foreach ($ElencoMail as $Mail) {
                                if ($Mail['EMAIL'] == $email) {
                                    $presente = true;
                                    break;
                                }
                            }
                            if ($presente == true) {
                                // Blocca l'inserimento o chiede una conferma?
                                $Msg = 'La mail <b>' . $email . '</b> è presente tra i Mittenti dello SDI';
                                $Msg .= ', non è possibile aggiungerla all\'archivio dei Mittenti/Destinatari.';
                                Out::msgStop('Attenzione', $Msg);
                                break;
                            }
                        }
                        $this->AggiungiMittente();
                        break;

                    case $this->nameForm . '_ConsultaMittentiAggiuntivi':
                        $consulta = true;
                    case $this->nameForm . '_MittentiAggiuntivi':
                        $anaent_3 = $this->proLib->GetAnaent('3');
                        if ($anaent_3['ENTDE1'] == 1) {
                            /*
                             * Nuova Chiamata piu app title:
                             */
                            $model = "proMittentiAggiuntivi";
                            itaLib::openForm($model);
                            $formObj = itaModel::getInstance($model);
                            if (!$formObj) {
                                Out::msgStop("Errore", "Apertura fallita");
                                break;
                            }
                            $ReturnModel = array(
                                'nameForm' => $this->nameForm,
                                'nameFormOrig' => $this->nameFormOrig
                            );
                            $_POST = array();
                            $_POST['event'] = 'openform';
                            $_POST['returnEvent'] = 'returnMittentiAggiuntivi';
                            $_POST['tipoProt'] = $this->tipoProt;
                            $_POST['mittentiAggiuntivi'] = $this->mittentiAggiuntivi;
                            $_POST[$model . '_returnModel'] = $ReturnModel;
                            if ($consulta === true) {
                                $_POST['consultazione'] = true;
                            }
                            $formObj->setReturnModel($ReturnModel);
                            $formObj->setReturnEvent('returnMittentiAggiuntivi');
                            $formObj->setEvent('openform');
                            if ($this->tipoProt == "P" || $this->tipoProt == "C") {
                                $formObj->setTitoloForm('Firmatari Aggiuntivi');
                            }
                            $formObj->parseEvent();
                        } else {
                            Out::msgStop("Attenzione!", "Funzione non abilitata da parametri.");
                        }
                        break;
                    case $this->nameForm . '_MailNotifica':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $anapro_rec['PROIDMAILDEST'];
                        $_POST['tipo'] = 'id';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                    case $this->nameForm . '_SbloccaMail':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $retRic = array();
                        if ($anapro_rec['PROIDMAILDEST']) {
                            $retRic = $this->proLib->checkMailRic($anapro_rec['PROIDMAILDEST']);
                        }
                        $messaggio = "<br><br>Procedura NON reversibile.<br>Sei sicuro di voler sbloccare i riferimenti dell'email per il destinatario?";
                        if ($retRic['ACCETTAZIONE']) {
                            if ($retRic['CONSEGNA']) {
                                $messaggio .= "<br>Fare Attenzione perché la PEC è stata sia Accettata dal sistema che Consegnata al Destinatario!";
                            } else {
                                $messaggio .= "<br>Fate Attenzione perché la PEC è stata Accettata dal Sistema.<br>Se l'indirizzo di destinazione è una PEC per sicurezza è consigliabile scaricare la posta per verificare se è avvenuta la Consegna.";
                            }
                        }
                        Out::msgQuestion("Attenzione.", $messaggio, array(
                            'F9-Annulla' => array('id' => $this->nameForm . '_AnnullaSblocco',
                                'model' => $this->nameForm, 'shortCut' => "f9"),
                            'F5-Sblocca' => array('id' => $this->nameForm . '_SbloccaMailConf',
                                'model' => $this->nameForm, 'shortCut' => "f5")
                                )
                        );
                        break;
                    case $this->nameForm . '_SbloccaMailConf':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $update_Info = 'Oggetto: SBLOCCA MAIL INVIO DESTINATARIO PRINCIPALE - elimina PROIDMAILDEST ' . $anapro_rec['PRONUM'] . "/" . $anapro_rec['PROPAR'] . " - " . $anapro_rec['PROIDMAILDEST'];
                        $anapro_rec['PROIDMAILDEST'] = '';
                        $this->anapro_rec['PROIDMAILDEST'] = '';
                        $this->updateRecord($this->PROT_DB, 'ANAPRO', $anapro_rec, $update_Info);
                        $proAppChiamante = itaModel::getInstance($this->nameFormChiamante);
                        $proAppChiamante->Dettaglio($anapro_rec['ROWID']);
                        break;

                    case $this->nameForm . '_SbloccaMailConfDaGriglia':
                        $selrow = $_POST[$this->gridAltriDestinatari]['gridParam']['selrow'];
                        $anades_rec = $this->proLib->GetAnades($this->proAltriDestinatari[$selrow]['ROWID'], 'rowid');
                        $update_Info = 'Oggetto: SBLOCCA MAIL INVIO ALTRO DESTINATARIO - elimina DESIDMAIL ' . $anades_rec['PRONUM'] . "/" . $anades_rec['PROPAR'] . " - " . $anades_rec['DESIDMAIL'];
                        $anades_rec['DESIDMAIL'] = '';
                        $this->proAltriDestinatari[$selrow]['DESIDMAIL'] = '';
                        $this->updateRecord($this->PROT_DB, 'ANADES', $anades_rec, $update_Info);
                        $proAppChiamante = itaModel::getInstance($this->nameFormChiamante);
                        $proAppChiamante->Dettaglio($this->anapro_rec['ROWID']);
                        break;

                    case $this->nameForm . '_AccettazioneNotifica':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $retRic = $this->proLib->checkMailRic($anapro_rec['PROIDMAILDEST']);
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
                        break;

                    case $this->nameForm . '_ConsegnaNotifica':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $retRic = $this->proLib->checkMailRicMulti($anapro_rec['PROIDMAILDEST']);
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
                        break;

                    case $this->nameForm . '_NotifichePEC':
                        $anapro_rec = $this->proLib->GetAnapro($this->anapro_rec['ROWID'], 'rowid');
                        $retRic = $this->proLib->checkMailRicMulti($anapro_rec['PROIDMAILDEST']);
                        if (!$retRic['NOTIFICHE']) {
                            break;
                        }
                        proRic::proRicNotificheMail(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                                ), $anapro_rec['PROIDMAILDEST']);
                        break;

                    case $this->nameForm . '_CercaAnaSoggetti':
                        include_once ITA_BASE_PATH . '/apps/CityFinancing/cwfLibDB_FTA.class.php';
                        proRic::apriRicercaAnagrafeSoggettiUnici('', '', 'retAnaSoggettiUnici', $this->nameForm, $this->nameFormOrig);
                        break;

                    case $this->nameForm . '_ANAPRO[PROTSP]_butt':
                        proRic::proRicAnatsp(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig
                        ));
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
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_ANAPRO[PROCON]':
                        $medcodOLD = $this->medcodOLD;
                        if ($_POST[$this->nameForm . '_ANAPRO']['PROCON'] != $medcodOLD) {
                            $codice = $_POST[$this->nameForm . '_ANAPRO']['PROCON'];
                            if (trim($codice) != "") {
                                if (is_numeric($codice)) {
                                    $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                                }
                                if ($this->tipoProt == 'C') {
                                    $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no');
                                    if (!$anamed_rec) {
                                        Out::valore($this->nameForm . '_ANAPRO[PROCON]', '');
                                        Out::valore($this->nameForm . '_ANAPRO[PRONOM]', '');
                                        Out::setFocus('', $this->nameForm . "_ANAPRO[PROCON]");
                                        break;
                                    }
                                }
                                /* Se è un arrivo ripristino la sua mail senza sovrascriverla: */
                                $MailPrec = $this->formData[$this->nameForm . '_ANAPRO']['PROMAIL'];
                                $anamed_rec = $this->DecodAnamed($codice);
                                if ($this->tipoProt == 'A' && $MailPrec) {
                                    /*
                                     * Se il parametro permette la sovrascrittura della mail in Arrivo
                                     * Non lo ripristino e lascio sovrascritto.
                                     */
                                    $anaent_52 = $this->proLib->GetAnaent('52');
                                    if (!$anaent_52['ENTDE3']) {
                                        Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $MailPrec);
                                    }
                                }
                            }
                        }
                        Out::setFocus('', $this->nameForm . "_ANAPRO[PRONOM]");
                        break;
                    case $this->nameForm . '_ANAPRO[PROCIT]':
                        $comuni_rec = $this->proLib->getGenericTab("SELECT * FROM COMUNI WHERE " . $this->PROT_DB->strUpper('COMUNE') . " ='"
                                . addslashes(strtoupper($_POST[$this->nameForm . '_ANAPRO']['PROCIT'])) . "'", false, 'COMUNI');
                        if ($comuni_rec) {
                            Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $comuni_rec['PROVIN']);
                            Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $comuni_rec['COAVPO']);
                        }
                        break;
                    case $this->nameForm . '_ANAPRO[PROTSP]':
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
                        break;
                }
                break;

            case 'suggest':
                if ($this->consultazione != true) {
                    switch ($_POST['id']) {
                        case $this->nameForm . '_ANAPRO[PRONOM]':
                            $this->suggestAnamed();
                            break;
                        case $this->nameForm . '_ANAPRO[PROCIT]':
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
                }
                break;

            case 'returnanamed':
                $this->DecodAnamed($_POST['retKey'], 'rowid');
                break;

            case 'returnanamedMittPartenza':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . '_DESCOD', $anamed_rec["MEDCOD"]);
                Out::valore($this->nameForm . "_DESNOM", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_DESCUF", '');
                Out::valore($this->nameForm . "_UFFNOM", '');
                Out::setFocus('', $this->nameForm . "_DESCOD");
                break;

            case 'returnRicIPA':
                Out::valore($this->nameForm . '_ANAPRO[PROCON]', '');
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $_POST['PROIND']);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_ANAPRO[PROFIS]', $_POST['PROFIS']);
                break;
            
            case 'returnAnagPerson':
                Out::valore($this->nameForm . '_ANAPRO[PROCON]', '');
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $_POST['PRONOM']);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $_POST['PROIND']);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $_POST['PROCIT']);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $_POST['PROPRO']);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $_POST['PROCAP']);
                Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $_POST['PROMAIL']);
                Out::valore($this->nameForm . '_ANAPRO[PROFIS]', $_POST['PROFIS']);
                break;
            case 'returnMittentiAggiuntivi':
                $this->mittentiAggiuntivi = $_POST['mittentiAggiuntivi'];
                $this->setStatoMittenti($this->mittentiAggiuntivi);
                break;

            case 'returntoform':
                switch ($_POST['retField']) {
                    case $this->nameForm . '_CaricaGridPart':
                        $proDettCampi = $_POST['proDettCampi'];
                        $salvaDest = array();
                        $salvaDest['PROPAR'] = $salvaDest['DESPAR'] = $this->tipoProt;
                        $salvaDest['DESCOD'] = $proDettCampi['destCodice'];
                        $salvaDest['PRONOM'] = $salvaDest['DESNOM'] = $proDettCampi['destNome'];
                        $salvaDest['PROIND'] = $salvaDest['DESIND'] = $proDettCampi['destInd'];
                        $salvaDest['PROCAP'] = $salvaDest['DESCAP'] = $proDettCampi['destCap'];
                        $salvaDest['PROCIT'] = $salvaDest['DESCIT'] = $proDettCampi['destCitta'];
                        $salvaDest['PROPRO'] = $salvaDest['DESPRO'] = $proDettCampi['destProv'];
                        $salvaDest['PRODAR'] = $salvaDest['DESDAT'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESDAA'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESDUF'] = $proDettCampi['destDataReg'];
                        $salvaDest['DESANN'] = $proDettCampi['destAnn'];
                        $salvaDest['DESMAIL'] = $proDettCampi['email'];
                        $salvaDest['DESTSP'] = $proDettCampi['destProtsp'];
                        $salvaDest['DESFIS'] = $proDettCampi['destFis'];
                        $salvaDest['DESNRAC'] = $proDettCampi['destNumRacc'];
                        $salvaDest['DESCUF'] = '';
                        $salvaDest['DESGES'] = 1;
                        $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                        if ($proDettCampi['destRowid'] != '') {
                            $this->proAltriDestinatari[$proDettCampi['destRowid']] = $salvaDest;
                        } else {
                            $this->proAltriDestinatari[] = $salvaDest;
                        }
                        $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                        if (!$this->consultazione) {
                            
                        }
                        break;
                }
                break;

            case 'returnUploadCSV':
                $altriDest = $this->proLib->elaboraCSVDestinatari($_POST['uploadedFile'], $this->tipoProt);

                if (!$altriDest) {
                    Out::msgStop("Errore", $this->proLib->getErrMessage());
                    break;
                }
                if ($this->proAltriDestinatari) {
                    $this->proAltriDestinatari = array_merge($this->proAltriDestinatari, $altriDest);
                } else {
                    $this->proAltriDestinatari = $altriDest;
                }

                $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                break;

            case 'returnNotifichePec':
                include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
                $emlLib = new emlLib();
                $mailArchivio_rec = $emlLib->getMailArchivio($_POST['retKey'], 'rowid');

                $model = 'emlViewer';
                $_POST['event'] = 'openform';
                $_POST['codiceMail'] = $mailArchivio_rec['IDMAIL'];
                $_POST['tipo'] = 'id';
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;

            case "returnFromDestSimple";
                //Out::msgInfo("", print_r($_POST['destSimpleDestinatari'], true));
                $retDest = $_POST['destSimpleDestinatari'];
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $retDest['PRINCIPALE']['PRONOM']);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $retDest['PRINCIPALE']['PROIND']);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $retDest['PRINCIPALE']['PROCIT']);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $retDest['PRINCIPALE']['PROCAP']);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $retDest['PRINCIPALE']['PROPRO']);
                Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $retDest['PRINCIPALE']['PROMAIL']);
                Out::valore($this->nameForm . '_ANAPRO[PROFIS]', $retDest['PRINCIPALE']['PROFIS']);

                $this->proAltriDestinatari = $retDest['ALTRIDEST'];
                $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);

                break;

            case 'broadcastMsg':
                switch ($_POST['message']) {
                    case 'PRENDI_ANAGPROARRI_PRONOM':
                        $soggetto = $_POST['msgData'];
                        $this->DecodAnagrafe($soggetto, 'PRONOM');
                        break;
                    case 'PRENDI_ANAGPROARRI_ALTRI':
                        $soggetto = $_POST['msgData'];
                        $this->DecodAnagrafe($soggetto, 'AltriDestCod');
                        break;
                }
                break;

            case 'retAnaSoggettiUnici':
                $DatiSogg = $this->formData['returnData'];
                $DatiResSogg = $this->proLib->GetDatiResidenzaSoggettoUnico($DatiSogg['PROGSOGG']);
                if ($DatiSogg['RAGSOC']) {
                    Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $DatiSogg['RAGSOC']);
                } else {
                    Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $DatiSogg['COGNOME'] . ' ' . $DatiSogg['NOME']);
                }
                Out::valore($this->nameForm . '_ANAPRO[PROFIS]', $DatiSogg['CODFISCALE']);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $DatiResSogg['DESVIA'] . ' ' . $DatiResSogg['NUMCIV']);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $DatiResSogg['DESLOCAL']);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $DatiResSogg['PROVINCIA']);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $DatiResSogg['CAP']);
                Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $DatiResSogg['E_MAIL']);
                break;

            case 'returnanatsp':
                $this->DecodAnatsp($_POST['retKey'], 'rowid');
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_proAltriDestinatari');
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_anapro_rec');
        App::$utente->removeKey($this->nameForm . '_elaboraAltriDest');
        App::$utente->removeKey($this->nameForm . '_elaboraAltriDest');
        App::$utente->removeKey($this->nameForm . '_medcodOLD');
        App::$utente->removeKey($this->nameForm . '_consultazione');
        App::$utente->removeKey($this->nameForm . '_altriDestPerms');
        App::$utente->removeKey($this->nameForm . '_mittentiAggiuntivi');
        App::$utente->removeKey($this->nameForm . '_disabledRec');
        App::$utente->removeKey($this->nameForm . '_bloccoDaEmail');
        App::$utente->removeKey($this->nameForm . '_annullato');
        App::$utente->removeKey($this->nameForm . '_nameFormChiamante');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function AzzeraVariabili() {
        $this->mittentiAggiuntivi = array();
        $this->proAltriDestinatari = array();
        $this->consultazione = '';
        $this->disabledRec = '';
        $this->bloccoDaEmail = false;
        Out::clearFields($this->nameForm, $this->nameForm);
        $this->medcodOLD = '';

        TableView::clearGrid($this->gridAltriDestinatari);
    }

    public function AbilitaCampi() {
        Out::show($this->nameForm . '_divMittDestPrincipale');
        Out::show($this->nameForm . '_ANAPRO[PROCIT]_field');
        Out::show($this->nameForm . '_ANAPRO[PROPRO]_field');
        Out::show($this->nameForm . '_ANAPRO[PROIND]_field');
        Out::show($this->nameForm . '_ANAPRO[PROCAP]_field');
        Out::show($this->nameForm . '_ANAPRO[PROMAIL]_field');
        Out::show($this->nameForm . '_ANAPRO[PROFIS]_field');
        Out::show($this->nameForm . '_ANAPRO[PRONAZ]_field');
        if ($this->checkPermsAnamed()) {
            Out::show($this->nameForm . '_AggiungiMittente');
        } else {
            Out::hide($this->nameForm . '_AggiungiMittente');
        }
        $anaent_31 = $this->proLib->GetAnaent('31');
        if ($anaent_31['ENTDE4'] == '1') {
            Out::show($this->nameForm . '_CercaAnagrafe');
            Out::show($this->nameForm . '_CercaAnagrafeAltri');
        } else {
            Out::hide($this->nameForm . '_CercaAnagrafe');
            Out::hide($this->nameForm . '_CercaAnagrafeAltri');
        }
        if ($this->proLib->CheckAbilitaAnaSoggettiUnici()) {
            Out::show($this->nameForm . '_CercaAnaSoggetti');
        } else {
            Out::hide($this->nameForm . '_CercaAnaSoggetti');
        }

        Out::show($this->nameForm . '_CercaIPA');
        Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
        if ($this->tipoProt == 'C') {
            Out::hide($this->nameForm . '_divCampiAltriDest');
            Out::hide($this->nameForm . '_divMittDestPrincipale');
            Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
            Out::hide($this->nameForm . '_divGrigliaDestPart');
            Out::hide($this->nameForm . '_divSpese');
        } else if ($this->tipoProt == 'A') {
            Out::show($this->nameForm . '_divSpese');
            Out::hide($this->nameForm . '_divCampiAltriDest');
            Out::hide($this->nameForm . '_divGrigliaDestPart');
            Out::html($this->nameForm . '_ANAPRO[PROCON]_lbl', 'Mittente');
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
            $anaent_rec = $this->proLib->GetAnaent('15');
            if ($anaent_rec['ENTDE4'] == '1') {
                Out::show($this->nameForm . '_divSpese');
            }
        } else if ($this->tipoProt == 'P') {
            Out::show($this->nameForm . '_divSpese');
            Out::hide($this->nameForm . '_divCampiAltriDest');
            Out::show($this->nameForm . '_divGrigliaDestPart');
            Out::show($this->nameForm . '_DestSimple');
            Out::html($this->nameForm . '_ANAPRO[PROCON]_lbl', 'Destinatario');
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "required");
            $anaent_rec = $this->proLib->GetAnaent('15');
            if ($anaent_rec['ENTDE4'] == '1') {
                Out::show($this->nameForm . '_divSpese');
            }
        }
        $anaent_3 = $this->proLib->GetAnaent('3');
        if ($anaent_3['ENTDE1'] == 1) {
            Out::show($this->nameForm . '_divMittentiAggiuntivi');
        }
        $this->CaricaDivMittentiAggiuntivi();

        /*
         * Campo Spedizione Required:
         */
        $anaent_57 = $this->proLib->GetAnaent('57');
        if ($anaent_57['ENTDE2'] == 1 && $this->tipoProt != 'C') {
            Out::addClass($this->nameForm . '_ANAPRO[PROTSP]', "required");
        } else {
            Out::delClass($this->nameForm . '_ANAPRO[PROTSP]', "required");
        }
        $anaent_58 = $this->proLib->GetAnaent('58');
        if ($anaent_58['ENTDE6']) {
            Out::show($this->nameForm . '_CercaAnagPerson');
        } else {
            Out::hide($this->nameForm . '_CercaAnagPerson');
        }
    }

    /*
     * Funzione per controllo permessi su Archivio Mittenti/Destinatari
     */

    private function checkPermsAnamed() {
        $menLib = new menLib();
        $gruppi = $menLib->getGruppi(App::$utente->getKey('idUtente'));
        $fl1 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGVIS", $menLib->defaultVis);
        $fl2 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGACC", $menLib->defaultAcc);
        $fl3 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGEDT", $menLib->defaultMod);
        $fl4 = $menLib->privilegiModel('proAnamed', $gruppi, "PER_FLAGINS", $menLib->defaultIns);
        if ($fl1 && $fl2 && $fl3 && $fl4) {
            return true;
        } else {
            return false;
        }
    }

    public function CaricaGrigliaAltriDestinatari() {
        if ($this->anapro_rec) {
            $this->proAltriDestinatari = $this->proLib->caricaAltriDestinatari($this->anapro_rec['PRONUM'], $this->anapro_rec['PROPAR'], $this->elaboraAltriDest);
            if (!$da_modifica) {
                foreach ($this->proAltriDestinatari as $key => $value) {
                    $value = $value;
                    $this->proAltriDestinatari[$key]['DESIDMAIL'] = '';
                }
            }
            $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
        }
    }

    public function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '1000') {
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $appoggio,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }

    public function Modifica() {
        /*
         * Leggo parametri di utilizzo proArriSubTest
         */
        $anapro_rec = $this->anapro_rec;
        $anaent_3 = $this->proLib->GetAnaent('3');

//        $this->AzzeraVariabili();
        $this->toggleMittentiDestinatari('abilita');
        $this->Nascondi(false);
        $this->AbilitaCampi();
        $this->Decod($anapro_rec, true);

        if ($anapro_rec['PROIDMAILDEST']) {
            Out::show($this->nameForm . '_MailNotifica');
            if ($this->tipoProt == 'P') {
                Out::show($this->nameForm . '_SbloccaMail');
            }
            $retRic = $this->proLib->checkMailRic($anapro_rec['PROIDMAILDEST']);
            if ($retRic['ACCETTAZIONE']) {
                Out::show($this->nameForm . '_AccettazioneNotifica');
            }
            if ($retRic['CONSEGNA']) {
                Out::show($this->nameForm . '_ConsegnaNotifica');
            }
            // ALTRE NOTIFICHE
            if ($retRic['NOTIFICHE']) {
                Out::show($this->nameForm . '_NotifichePEC');
                Out::show($this->nameForm . '_divInfoErrorProto');
//                Out::css($this->nameForm . '_divTesta', 'background-image', ' linear-gradient(to right, #de3b43 0%, #FFFFFF 100%)');
//                Out::html($this->nameForm . '_divInfoErrorProto', 'Riscontrate anomalie nelle notifiche inviate ai destinatari del protocollo.');
            }
        }
        /*
         * Carico Mittenti Aggiuntivi
         */
        if ($anaent_3['ENTDE1'] == 1) {
            $this->mittentiAggiuntivi = $this->proLib->getPromitagg($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR']);
            $this->setStatoMittenti($this->mittentiAggiuntivi);
        }
        if ($this->consultazione == true) {
            $this->Nascondi(false);
        }

        /*
         * Fase di blocco campi e funzioni
         */
        if ($this->bloccareSeInviato($anapro_rec) === true) {
            $this->bloccoDaEmail = true;
        } else {
            $this->bloccoDaEmail = false;
            $this->toggleMailMittentiDestinatari('abilita');
        }
        if ($this->consultazione) {
            $this->toggleMittentiDestinatari('disabilita');
        } elseif ($this->disabledRec) {
            $this->toggleMittentiDestinatari('disabilita', false);
            if ($this->tipoProt == "P") {
                // 
                if ($this->bloccoDaEmail == true) {
                    if ($anapro_rec['PROIDMAILDEST'] == '') {
                        $this->toggleMailMittentiDestinatari('abilita');
                    }
                } else {
                    $this->toggleMailMittentiDestinatari('abilita');
                }
            }
        } elseif (!$this->disabledRec) {
            if ($this->tipoProt == "P") {
                if ($this->bloccoDaEmail == true) {
                    $this->toggleMittentiDestinatari('disabilita', false);
                    if ($anapro_rec['PROIDMAILDEST'] == '') {
                        $this->toggleMailMittentiDestinatari('abilita');
                    }
                } else {
                    $this->toggleMailMittentiDestinatari('abilita');
                }
            }
        }

        if ($this->proLibGiornaliero->isRegistroGiornaliero($anapro_rec)) {
            $this->toggleMittentiDestinatari('disabilita', false);
        }
        /*
         * Verifico se protocollo conservato:
         * - Per Mitt/Dest nessuna azione particolare.
         */
        $this->CheckProtConservato($anapro_rec);
        /*
         * Verifico protocollo annullato:
         */
        if ($this->annullato) {
            $this->toggleMittentiDestinatari('disabilita', false);
        }
        return $anapro_rec;
    }

    public function Decod($anapro_rec, $da_modifica = false) {
        Out::valori($anapro_rec, $this->nameForm . '_ANAPRO');

        if ($this->tipoProt == 'P') {
            $this->proAltriDestinatari = $this->proLib->caricaAltriDestinatari($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $da_modifica);
            if (!$da_modifica) {
                foreach ($this->proAltriDestinatari as $key => $value) {
                    $value = $value;
                    $this->proAltriDestinatari[$key]['DESIDMAIL'] = '';
                }
            }
            $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
        }
        $this->DecodAnatsp($anapro_rec['PROTSP']);
        if ($this->tipoProt == 'C') {
            Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "required");
        }
    }

    public function Nuovo() {
        $this->toggleMittentiDestinatari('abilita');
        $this->AzzeraVariabili();
        $this->Nascondi();

        $this->AbilitaCampi();
        $anaent_3 = $this->proLib->GetAnaent('3');

        $this->setStatoMittenti($this->mittentiAggiuntivi);

        $this->consultazione = false;
    }

    public function Nascondi($nuovo = true) {
        $this->sbloccaSeDaInviare();
        if ($nuovo) {
            Out::hide($this->nameForm . '_divProtMit');
            Out::hide($this->nameForm . '_divMittPartenza');
        }
        Out::hide($this->nameForm . '_divCampiDest');
        Out::hide($this->nameForm . '_divMittentiAggiuntivi');
        Out::hide($this->nameForm . '_MailNotifica');
        Out::hide($this->nameForm . '_AccettazioneNotifica');
        Out::hide($this->nameForm . '_ConsegnaNotifica');
        Out::hide($this->nameForm . '_NotifichePEC');
        Out::hide($this->nameForm . '_divInfoErrorProto');
        Out::hide($this->nameForm . '_SbloccaMail');
        Out::hide($this->nameForm . '_ModificaNota');
        Out::hide($this->nameForm . '_AggiungiMittente');
        Out::hide($this->nameForm . '_divSpese');

        Out::hide($this->nameForm . '_ANAPRO[PRONUR]_field');
        Out::hide($this->nameForm . '_ANAPRO[PRODRA]_field');
        $this->abilitaProt($nuovo);
    }

    public function DecodAnamed($codice, $tipoRic = 'codice', $tutti = 'si') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipoRic, $tutti);
        if ($anamed_rec) {
            Out::valore($this->nameForm . '_ANAPRO[PROCON]', $anamed_rec['MEDCOD']);
            Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $anamed_rec['MEDNOM']);
            Out::valore($this->nameForm . '_ANAPRO[PROIND]', $anamed_rec['MEDIND']);
            Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $anamed_rec['MEDCIT']);
            Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $anamed_rec['MEDPRO']);
            Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $anamed_rec['MEDCAP']);
            Out::valore($this->nameForm . '_ANAPRO[PROMAIL]', $anamed_rec['MEDEMA']);
            Out::valore($this->nameForm . '_ANAPRO[PROFIS]', $anamed_rec['MEDFIS']);
            $this->medcodOLD = $anamed_rec['MEDCOD'];
        }
        return $anamed_rec;
    }

    public function suggestAnamed($altri = '') {
        if ($this->tipoProt == 'C') {
            $filtroUff = " AND MEDANN<>1 AND MEDUFF" . $this->PROT_DB->isNotBlank();
        }
        $q = itaSuggest::getQuery();
        itaSuggest::setNotFoundMessage('Nessun risultato.');

        $parole = explode(' ', $q);
        foreach ($parole as $k => $parola) {
            $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . "  LIKE '%" . strtoupper(addslashes($parola)) . "%'";
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

                $indirizzo = $indirizzo . " - " . $anamed_rec['MEDEMA'];


                if ($altri == '') {
                    itaSuggest::addSuggest($anamed_rec['MEDNOM'] . $indirizzo, array($this->nameForm . "_ANAPRO[PROCON]" => $anamed_rec['MEDCOD']));
                } else {
                    itaSuggest::addSuggest($anamed_rec['MEDNOM'] . $indirizzo, array($this->nameForm . "_AltriDestCod" => $anamed_rec['MEDCOD']));
                }
            }
        }
        itaSuggest::sendSuggest();
    }

    private function bloccaDopoInvio() {
        if (!$this->anapro_rec['PROIDMAILDEST']) {
            Out::delClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
            Out::delClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
            Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '1');
            Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '1');
        } else {
            Out::addClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
            Out::addClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
            Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '0');
            Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '0');
        }
        Out::hide($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::hide($this->nameForm . '_CercaAnagrafe');
        Out::hide($this->nameForm . '_CercaIPA');
        Out::hide($this->nameForm . '_AggiungiMittente');
        Out::hide($this->nameForm . '_CercaAnaSoggetti');
        Out::hide($this->nameForm . '_CercaAnagPerson');
    }

    private function sbloccaSeDaInviare() {
        Out::delClass($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
        Out::delClass($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");

        Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", '1');
        Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", '1');
        Out::show($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::show($this->nameForm . '_CercaAnagrafe');
        Out::show($this->nameForm . '_CercaIPA');
        Out::show($this->nameForm . '_CercaAnaSoggetti');
        Out::show($this->nameForm . '_CercaAnagPerson');

        if ($this->checkPermsAnamed()) {
            Out::show($this->nameForm . '_AggiungiMittente');
        } else {
            Out::hide($this->nameForm . '_AggiungiMittente');
        }
    }

    public function toggleMittentiDestinatari($modo = 'disabilita', $toggleAdd = true) {
        switch ($modo) {
            case 'disabilita':
                $this->altriDestPerms = 'nessuno';
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $this->altriDestPerms = 'tutti';
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }
        Out::$classMethod($this->nameForm . '_ANAPRO[PROCON]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCON]', "readonly", $attrCmd);
        Out::$classMethod($this->nameForm . '_ANAPRO[PRONOM]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONOM]', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_ANAPRO[PRONOM]_butt');
        Out::$hideShow($this->nameForm . '_CercaAnagrafe');
        Out::$hideShow($this->nameForm . '_CercaIPA');
        Out::$hideShow($this->nameForm . '_CercaAnaSoggetti');
        Out::$hideShow($this->nameForm . '_CercaAnagPerson');

        Out::$classMethod($this->nameForm . '_ANAPRO[PROIND]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROIND]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROCIT]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCIT]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROPRO]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROPRO]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROCAP]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROCAP]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROFIS]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROFIS]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONAZ]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONAZ]', "readonly", $attrCmd);

        Out::$hideShow($this->nameForm . '_MittentiAggiuntivi');
        if ($toggleAdd) {
            Out::$hideShow($this->gridAltriDestinatari . '_addGridRow');
        }
        Out::$hideShow($this->gridAltriDestinatari . '_delGridRow');

        Out::$classMethod($this->nameForm . '_ANAPRO[PRONAL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PRONAL]', "readonly", $attrCmd);

        Out::$classMethod($this->nameForm . '_ANAPRO[PROTSP]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROTSP]', "readonly", $attrCmd);
        Out::$hideShow($this->nameForm . '_ANAPRO[PROTSP]_butt');
    }

    public function toggleMailMittentiDestinatari($modo = 'disabilita') {
        switch ($modo) {
            case 'disabilita':
                $this->altriDestPerms = 'nessuno';
                $classMethod = "addClass";
                $attrCmd = '0';
                $hideShow = 'hide';
                break;
            case 'abilita':
                $this->altriDestPerms = 'indirizzo';
                $classMethod = "delClass";
                $attrCmd = '1';
                $hideShow = 'show';
                break;
        }

        Out::$classMethod($this->nameForm . '_ANAPRO[PROMAIL]', "ita-readonly");
        Out::attributo($this->nameForm . '_ANAPRO[PROMAIL]', "readonly", $attrCmd);
        $this->StatoToggle['toggleMailMittentiDestinatari'] = $modo;
    }

    public function DecodAnagrafe($soggetto, $tipoRet) {
        $nome = $soggetto['NOME'];
        $cognome = $soggetto['COGNOME'];
        switch ($tipoRet) {
            case 'PRONOM':
                Out::valore($this->nameForm . '_ANAPRO[PRONOM]', $cognome . " " . $nome);
                Out::valore($this->nameForm . '_ANAPRO[PROCAP]', $soggetto['CAP']);
                Out::valore($this->nameForm . '_ANAPRO[PROPRO]', $soggetto['PROVINCIA']);
                Out::valore($this->nameForm . '_ANAPRO[PROCIT]', $soggetto['RESIDENZA']);
                Out::valore($this->nameForm . '_ANAPRO[PROIND]', $soggetto['INDIRIZZO'] . ' ' . $soggetto['CIVICO']);
                break;
            case 'AltriDestCod':
                $salvaDest = array();
                $salvaDest['PROPAR'] = $salvaDest['DESPAR'] = $this->tipoProt;
                $salvaDest['DESCOD'] = '';
                $salvaDest['PRONOM'] = $salvaDest['DESNOM'] = $cognome . " " . $nome;
                $salvaDest['PROIND'] = $salvaDest['DESIND'] = $soggetto['INDIRIZZO'] . ' ' . $soggetto['CIVICO'];
                $salvaDest['PROCAP'] = $salvaDest['DESCAP'] = $soggetto['CAP'];
                $salvaDest['PROCIT'] = $salvaDest['DESCIT'] = $soggetto['RESIDENZA'];
                $salvaDest['PROPRO'] = $salvaDest['DESPRO'] = $soggetto['PROVINCIA'];
                $salvaDest['PRODAR'] = $salvaDest['DESDAT'] = date('Ymd');
                $salvaDest['DESDAA'] = date('Ymd');
                $salvaDest['DESDUF'] = date('Ymd');
                $salvaDest['DESANN'] = date('Y');
                $salvaDest['DESMAIL'] = '';
                $salvaDest['DESCUF'] = '';
                $salvaDest['DESGES'] = 1;
                $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                $this->proAltriDestinatari[] = $salvaDest;
                $this->CaricaGriglia($this->gridAltriDestinatari, $this->proAltriDestinatari);
                break;
        }
    }

    public function setStatoMittenti($mittentiAgg) {
        $statoMitt = 'Non sono presenti mittenti aggiuntivi.';
        $descMittFir = 'mittente';
        $descMittFirMulti = 'mittenti';
        Out::html($this->nameForm . '_MittentiAggiuntivi_lbl', 'Mittenti Aggiuntivi');
        if ($this->tipoProt == "P" || $this->tipoProt == "C") {
            $statoMitt = 'Non sono presenti firmatari aggiuntivi.';
            $descMittFir = 'firmatario';
            $descMittFirMulti = 'firmatarii';
            Out::html($this->nameForm . '_MittentiAggiuntivi_lbl', 'Firmatari Aggiuntivi');
        }
        $numMitt = count($mittentiAgg);
        $tooltipMitt = '';
        if ($numMitt) {
            if ($numMitt == 1) {
                $statoMitt = "E' presente un $descMittFir aggiuntivo.";
            } else {
                $statoMitt = "Sono presenti $numMitt $descMittFirMulti aggiuntivi.";
            }
            $tooltipMitt .= 'Elenco ' . $descMittFirMulti . ' Aggiuntivi:<br><br>';
            $cc = 1;
            foreach ($mittentiAgg as $mittente) {
                $tooltipMitt .= htmlspecialchars($cc . ') ' . $mittente['PRONOM'] . '<br>');
                if ($mittente['PROMAIL']) {
                    $tooltipMitt .= htmlspecialchars('<span style="margin-left:15px;text-transform: lowercase;">' . $mittente['PROMAIL'] . '</span>');
                }
                $tooltipMitt .= '<br>';
                $cc++;
            }
            $iniLink = '<a href="#" id="' . $this->nameForm . '_ConsultaMittentiAggiuntivi" class="ita-hyperlink" >';
            $finLink = '</a>';
            $statoMitt = "<div class=\"ita-html\">$iniLink<span style=\"display:inline-block;\" title=\"$tooltipMitt\" class=\"ita-tooltip\">$statoMitt</span>$finLink</div>";
        }
        Out::html($this->nameForm . "_statoMittenti", $statoMitt);
    }

    public function AggiungiMittente() {
        $mednom = $_POST[$this->nameForm . '_ANAPRO']['PRONOM'];
        $medcit = $_POST[$this->nameForm . '_ANAPRO']['PROCIT'];
        $medind = $_POST[$this->nameForm . '_ANAPRO']['PROIND'];
        $medcap = $_POST[$this->nameForm . '_ANAPRO']['PROCAP'];
        $medpro = $_POST[$this->nameForm . '_ANAPRO']['PROPRO'];
        $email = $_POST[$this->nameForm . '_ANAPRO']['PROMAIL'];
        $fisc = $_POST[$this->nameForm . '_ANAPRO']['PROFIS'];
        if ($mednom) {
            $risultato = $this->proLib->registraAnamed($mednom, $medcit, $medind, $medcap, $medpro, $email, $fisc);
            if ($risultato['MEDCOD']) {
                $this->DecodAnamed($risultato['MEDCOD']);
            }
            Out::msgInfo($risultato['titolo'], $risultato['messaggio']);
        }
        Out::setFocus('', $this->nameForm . "_ANAPRO[PRONOM]");
    }

    public function abilitaProt($nuovo = true) {
        $abilitaProt_tab = $this->proLib->GetAbilitaProt('', 'tutti');
        foreach ($abilitaProt_tab as $abilitaProt_rec) {
            if ($nuovo == true) {
                Out::unBlock($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
            } else {
                switch ($abilitaProt_rec['CODICE']) {
                    case '':
                        break;
                    default:
                        if ($abilitaProt_rec['MODIFICA'] == '1') {
                            Out::unBlock($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
                        } else {
                            Out::block($this->nameForm . '_' . $abilitaProt_rec['CODICE']);
                        }
                        break;
                }
            }
        }
    }

    public function CheckProtConservato($Anapro_rec) {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
    }

    public function CaricaDivMittentiAggiuntivi() {
        Out::html($this->nameForm . '_divDatiPre', '');
        Out::html($this->nameForm . '_divDatiPost', '');
        $generator = new itaGenerator();
        if ($this->tipoProt == "A") {
            $retHtml = $generator->getModelHTML('proDivMittAggiuntivi', false, $this->nameForm, true);
            Out::html($this->nameForm . '_divDatiPost', $retHtml);
        } else {
            $retHtml = $generator->getModelHTML('proDivMittAggiuntivi', false, $this->nameForm, true);
            Out::html($this->nameForm . '_divDatiPre', $retHtml);
        }
    }

    public function DisabilitaDatiConservazione() {
        Out::hide($this->nameForm . '_MittentiAggiuntivi');
    }

    public function getDatiAnapro() {
        $anapro_rec = $this->formData[$this->nameForm . "_ANAPRO"];
        return $anapro_rec;
    }

    private function bloccareSeInviato($anapro_rec) {
        if ($this->tipoProt == 'P') {
            if ($anapro_rec['PROIDMAILDEST'] != '') {
                return true;
            }
            foreach ($this->proAltriDestinatari as $destinatario) {
                if ($destinatario['DESIDMAIL']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function RegistraAltriDestinatari() {
        if ($this->proAltriDestinatari) {
            foreach ($this->proAltriDestinatari as $key => $record) {
                $anades_rec = array();
                $anades_rec['DESNUM'] = $this->anapro_rec['PRONUM'];
                $anades_rec['DESPAR'] = $record['DESPAR'];
                $anades_rec['DESCOD'] = $record['DESCOD'];
                $anades_rec['DESNOM'] = $record['DESNOM'];
                $anades_rec['DESIND'] = $record['DESIND'];
                $anades_rec['DESCAP'] = $record['DESCAP'];
                $anades_rec['DESCIT'] = $record['DESCIT'];
                $anades_rec['DESPRO'] = $record['DESPRO'];
                $anades_rec['DESDAT'] = $record['DESDAT'];
                $anades_rec['DESDAA'] = $record['DESDAA'];
                $anades_rec['DESDUF'] = $record['DESDUF'];
                $anades_rec['DESANN'] = $record['DESANN'];
                $anades_rec['DESMAIL'] = $record['DESMAIL'];
                $anades_rec['DESSER'] = $record['DESSER'];
                $anades_rec['DESCUF'] = $record['DESCUF'];
                $anades_rec['DESGES'] = $record['DESGES'];
                $anades_rec['DESRES'] = $record['DESRES'];
                $anades_rec['DESTSP'] = $record['DESTSP'];
                $anades_rec['DESIDMAIL'] = $record['DESIDMAIL'];
                $anades_rec['DESRUOLO'] = $record['DESRUOLO'];
                $anades_rec['DESCONOSCENZA'] = $record['DESCONOSCENZA'];
                $anades_rec['DESTIPO'] = "D";
                $anades_rec['DESORIGINALE'] = $record['DESORIGINALE'];
                $anades_rec['DESFIS'] = $record['DESFIS'];
                $insert_Info = 'Inserimento: ' . $anades_rec['DESNUM'] . ' ' . $anades_rec['DESNOM'];
                if (!$this->insertRecord($this->PROT_DB, 'ANADES', $anades_rec, $insert_Info)) {
                    return false;
                }
            }
        }
    }

    public function DecodAnatsp($codTsp, $tipo = 'codice') {
        $anatsp_rec = $this->proLib->GetAnatsp($codTsp, $tipo);
        if ($anatsp_rec['TSPGRACC'] == 1) {
            Out::show($this->nameForm . '_ANAPRO[PRODRA]_field');
            Out::show($this->nameForm . '_ANAPRO[PRONUR]_field');
        } else {
            Out::hide($this->nameForm . '_ANAPRO[PRODRA]_field');
            Out::hide($this->nameForm . '_ANAPRO[PRONUR]_field');
        }
        Out::valore($this->nameForm . '_ANAPRO[PROTSP]', $anatsp_rec['TSPCOD']);
        Out::valore($this->nameForm . '_Tspdes', $anatsp_rec['TSPDES']);
        // Stampa analogica e attivi parametro spedizione obbligatoria:
        if ($anatsp_rec['TSPTIPO'] == "") {
            $anaent_57 = $this->proLib->GetAnaent('57');
            if ($anaent_57['ENTDE2'] == 1 && $this->tipoProt != 'C') {
                Out::addClass($this->nameForm . '_ANAPRO[PROCAP]', "required");
            }
        } else {
            Out::delClass($this->nameForm . '_ANAPRO[PROCAP]', "required");
        }
        return $anatsp_rec;
    }

}
