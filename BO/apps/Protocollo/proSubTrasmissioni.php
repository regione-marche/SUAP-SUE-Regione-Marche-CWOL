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
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

function proSubTrasmissioni() {
    $proSubTrasmissioni = new proSubTrasmissioni();
    $proSubTrasmissioni->parseEvent();
    return;
}

class proSubTrasmissioni extends itaModel {

    public $PROT_DB;
    public $nameForm = "proSubTrasmissioni";
    public $proLib;
    public $proLibMail;
    public $proLibGiornaliero;
    public $gridDestinatari = "proSubTrasmissioni_gridDestinatari";
    public $proArriDest = array();
    public $proArriUff = array();
    public $StatoToggle = array();
    public $tipoProt;
    public $anapro_rec;
    public $consultazione;
    public $disabledRec;
    public $bloccoDaEmail;
    public $annullato;
    public $nameFormChiamante;
    public $profilo;
    public $proDestinatari = array();
    public $rowidAppoggio;
    public $varAppoggio;

    function __construct() {
        parent::__construct();
        try {
            
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    protected function postInstance() {
        $this->origForm = $this->nameFormOrig;
        $this->nameModel = substr($this->nameForm, strpos($this->nameForm, '_') + 1);
        $this->gridDestinatari = $this->nameForm . '_gridDestinatari';

        $this->proLib = new proLib();
        $this->proLibMail = new proLibMail();
        $this->PROT_DB = $this->proLib->getPROTDB();
        $this->tipoProt = App::$utente->getKey($this->nameForm . '_tipoProt');
        $this->anapro_rec = App::$utente->getKey($this->nameForm . '_anapro_rec');
        $this->consultazione = App::$utente->getKey($this->nameForm . '_consultazione');
        $this->disabledRec = App::$utente->getKey($this->nameForm . '_disabledRec');
        $this->bloccoDaEmail = App::$utente->getKey($this->nameForm . '_bloccoDaEmail');
        $this->annullato = App::$utente->getKey($this->nameForm . '_annullato');
        $this->nameFormChiamante = App::$utente->getKey($this->nameForm . '_nameFormChiamante');
        $this->proArriDest = App::$utente->getKey($this->nameForm . '_proArriDest');
        $this->proArriUff = App::$utente->getKey($this->nameForm . '_proArriUff');
        $this->StatoToggle = App::$utente->getKey($this->nameForm . '_StatoToggle');
        $this->profilo = App::$utente->getKey($this->nameForm . '_profilo');
        $this->proDestinatari = App::$utente->getKey($this->nameForm . '_proDestinatari');
        $this->rowidAppoggio = App::$utente->getKey($this->nameForm . '_rowidAppoggio');
        $this->varAppoggio = App::$utente->getKey($this->nameForm . '_varAppoggio');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_tipoProt", $this->tipoProt);
            App::$utente->setKey($this->nameForm . "_anapro_rec", $this->anapro_rec);
            App::$utente->setKey($this->nameForm . "_consultazione", $this->consultazione);
            App::$utente->setKey($this->nameForm . "_disabledRec", $this->disabledRec);
            App::$utente->setKey($this->nameForm . "_bloccoDaEmail", $this->bloccoDaEmail);
            App::$utente->setKey($this->nameForm . "_annullato", $this->annullato);
            App::$utente->setKey($this->nameForm . "_nameFormChiamante", $this->nameFormChiamante);
            App::$utente->setKey($this->nameForm . '_proArriDest', $this->proArriDest);
            App::$utente->setKey($this->nameForm . '_proArriUff', $this->proArriUff);
            App::$utente->setKey($this->nameForm . '_StatoToggle', $this->StatoToggle);
            App::$utente->setKey($this->nameForm . '_profilo', $this->profilo);
            App::$utente->setKey($this->nameForm . '_proDestinatari', $this->proDestinatari);
            App::$utente->setKey($this->nameForm . '_rowidAppoggio', $this->rowidAppoggio);
            App::$utente->setKey($this->nameForm . '_varAppoggio', $this->varAppoggio);
        }
    }

    public function setTipoProt($tipoProt) {
        $this->tipoProt = $tipoProt;
    }

    public function setAnapro_rec($anapro_rec) {
        $this->anapro_rec = $anapro_rec;
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

    public function getStatoToggle() {
        return $this->StatoToggle;
    }

    public function setStatoToggle($StatoToggle) {
        $this->StatoToggle = $StatoToggle;
    }

    public function getProArriDest() {
        return $this->proArriDest;
    }

    public function getProArriUff() {
        return $this->proArriUff;
    }

    public function setProArriDest($proArriDest) {
        $this->proArriDest = $proArriDest;
    }

    public function setProArriUff($proArriUff) {
        $this->proArriUff = $proArriUff;
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                /*
                 * Funzioni di Rimozione/Nascondi campi
                 */
                $this->profilo = proSoggetto::getProfileFromIdUtente();
                /*
                 * Pulizia Variabili:
                 */
                $this->proAltriDestinatari = array();
                $this->mittentiAggiuntivi = array();
                $this->proDestinatari = array();
                $this->proArriDest = array();
                $this->proArriUff = array();
                /*
                 * Disattivo Campo Settore
                 */
                $anaent_57 = $this->proLib->GetAnaent('57');
                if ($anaent_57['ENTDE3']) {
                    Out::removeElement($this->nameForm . '_divSettore');
                    /*
                     * Parametrizzare
                     * 
                     */
                    Out::removeElement($this->gridDestinatari . '_addGridRow');
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($this->elementId) {
                    
                }
                break;

            case 'addGridRow':
                $anaent_37 = $this->proLib->GetAnaent('37');
                if (($this->consultazione != true && $this->disabledRec != true) || ($this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) || (!$anaent_37['ENTDE5'])) {
                    switch ($this->elementId) {
                        case $this->gridDestinatari:
                            $nameForm = array(
                                'nameForm' => $this->nameForm,
                                'nameFormOrig' => $this->nameFormOrig);
                            proRic::proRicDestinatari($this->proLib, $nameForm);
                            break;
                    }
                }
                break;
            case 'delGridRow':
                switch ($this->elementId) {
                    case $this->gridDestinatari:
                        $anaent_37 = $this->proLib->GetAnaent('37');
                        if (($this->consultazione != true && $this->disabledRec != true) || ($this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) || (!$anaent_37['ENTDE5'])) {
                            if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true) {
                                if ($this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true') {
                                    if (substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                        $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                        unset($this->proArriDest[$indice]);
                                    } else if (substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'UFFPRO') {
                                        $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                        unset($this->proArriUff[$indice]);
                                    }
                                } else if (substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'UFFPRO') {
                                    foreach ($this->proArriDest as $key => $dest) {

                                        if ($dest['DESCUF'] == $this->proDestinatari[$_POST['rowid']]['UFFCOD']) {
                                            unset($this->proArriDest[$key]);
                                        }
                                    }
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    unset($this->proArriUff[$indice]);
                                }
                                $this->elaboraAlbero();
                            }
                            break;
                        }
                        break;
                }
                break;

            case 'exportTableToExcel':
                switch ($_POST['id']) {
                    
                }
                break;

            case 'cellSelect':
                switch ($this->elementId) {
                    case $this->gridDestinatari:
                        switch ($_POST['colName']) {
                            case 'GESTIONE':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true &&
                                        $this->proDestinatari[$_POST['rowid']]['VALORE'] != '') {
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    if (substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                        // @TODO Cambiare condizioni selezione cellSelect utilizzando l'array di sessione e non il valore
                                        if ($_POST['cellContent'] == '1' ||
                                                $_POST['cellContent'] == '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>' ||
                                                $this->proArriDest[$indice]['DESGES'] == '1') {

                                            $this->proArriDest[$indice]['DESGES'] = '';
                                        } else {
                                            $this->proArriDest[$indice]['DESGES'] = '1';
                                        }
                                    } else if (substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'UFFPRO') {
                                        if ($_POST['cellContent'] == '1' ||
                                                $_POST['cellContent'] == '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>' ||
                                                $this->proArriUff[$indice]['UFFFI1'] == '1') {

                                            $this->proArriUff[$indice]['UFFFI1'] = '';
                                        } else {
                                            $this->proArriUff[$indice]['UFFFI1'] = '1';
                                        }
                                    }
                                    $this->elaboraAlbero();
                                }
                                break;
                            case 'RESPONSABILE':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    if ($_POST['cellContent'] == 'true' ||
                                            $_POST['cellContent'] == '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>' ||
                                            $this->proArriDest[$indice]['DESRES'] == 'true') {

                                        $this->proArriDest[$indice]['DESRES'] = '';
                                    } else {
                                        $this->proArriDest[$indice]['DESRES'] = 'true';
                                    }
                                    $this->elaboraAlbero();
                                }
                                break;
                            case 'DESORIGINALE':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES' && ( $this->tipoProt == 'A' || $this->tipoProt == 'C')) {
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    if ($_POST['cellContent'] == 'true' ||
                                            $_POST['cellContent'] == '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>' ||
                                            $this->proArriDest[$indice]['DESORIGINALE'] == 'true') {

                                        $this->proArriDest[$indice]['DESORIGINALE'] = '';
                                    } else {
                                        foreach ($this->proArriDest as $key => $dest) {
                                            $dest = $dest;
                                            $this->proArriDest[$key]['DESORIGINALE'] = '';
                                        }

                                        $this->proArriDest[$indice]['DESORIGINALE'] = 'true';
                                    }
                                    $this->elaboraAlbero();
                                }
                                break;
                            case 'MAILDESTINT':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    if (!$this->proDestinatari[$_POST['rowid']]['DESIDMAIL']) {
                                        break;
                                    }
                                    if (!$this->proLibMail->CheckStatoMail($this->proDestinatari[$_POST['rowid']]['DESIDMAIL'])) {
                                        Out::msgInfo('Stato Mail', $this->proLibMail->getMailAvviso());
                                        break;
                                    }
                                    $model = 'emlViewer';
                                    $_POST['event'] = 'openform';
                                    $_POST['codiceMail'] = $this->proDestinatari[$_POST['rowid']]['DESIDMAIL'];
                                    $_POST['tipo'] = 'id';
                                    itaLib::openForm($model);
                                    $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                                    include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                                    $model();
                                }
                                break;
                            case 'ACCDESTINT':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    if ($this->proDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRic($this->proDestinatari[$_POST['rowid']]['DESIDMAIL']);
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
                            case 'CONSDESTINT':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    if ($this->proDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $retRic = $this->proLib->checkMailRicMulti($this->proDestinatari[$_POST['rowid']]['DESIDMAIL']);
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

                            case 'NOTIFICAPECINT':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    if ($this->proDestinatari[$_POST['rowid']]['DESIDMAIL'] == '') {
                                        break;
                                    }
                                    $IdMailPadre = $this->proDestinatari[$_POST['rowid']]['DESIDMAIL'];
                                    $retRic = $this->proLib->checkMailRicMulti($IdMailPadre);
                                    if (!$retRic['NOTIFICHE']) {
                                        break;
                                    }
                                    $nameForm = array(
                                        'nameForm' => $this->nameForm,
                                        'nameFormOrig' => $this->nameFormOrig);
                                    proRic::proRicNotificheMail($nameForm, $IdMailPadre);
                                }
                                break;

                            case 'TERMINE':
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    Out::msgInput(
                                            'Data Termine', array(
                                        'label' => 'Data<br>',
                                        'id' => $this->nameForm . '_Termine',
                                        'name' => $this->nameForm . '_Termine',
                                        'type' => 'text',
                                        'size' => '15',
                                        'class' => "ita-date",
                                        'maxchars' => '12'), array(
                                        'Conferma' => array('id' => $this->nameForm . '_ConfermaDataTermine', 'model' => $this->nameForm)
                                            ), $this->nameForm
                                    );
                                    $this->rowidAppoggio = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    Out::valore($this->nameForm . '_Termine', $this->proDestinatari[$_POST['rowid']]['TERMINE']);
                                }
                                break;

                            case 'INVIOMAIL':
                                // Controllo il destinatario e se sto selezionando un destinatario.
                                if (array_key_exists($_POST['rowid'], $this->proDestinatari) == true && $this->proDestinatari[$_POST['rowid']]['isLeaf'] == 'true' && substr($this->proDestinatari[$_POST['rowid']]['VALORE'], 0, 6) == 'ANADES') {
                                    $indice = $this->proDestinatari[$_POST['rowid']]['INDICE'];
                                    if ($_POST['cellContent'] == 'true' ||
                                            $_POST['cellContent'] == '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>' ||
                                            $this->proArriDest[$indice]['DESINV'] == '1') {

                                        $this->proArriDest[$indice]['DESINV'] = '';
                                    } else {
                                        $this->proArriDest[$indice]['DESINV'] = '1';
                                    }
                                    $this->elaboraAlbero();
                                }
                                break;
                        }
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($this->elementId) {
                    
                }
                break;
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Uff_cod_butt':
                        $returnModel = array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig);
                        itaLib::openForm('proSeleTrasmUffici');
                        /* @var $proSeleTrasmUffici proSeleTrasmUffici */
                        $proSeleTrasmUffici = itaModel::getInstance('proSeleTrasmUffici');
                        $proSeleTrasmUffici->setEvent('openform');
                        $proSeleTrasmUffici->setReturnModel($returnModel);
                        $proSeleTrasmUffici->setReturnId('');
                        $proSeleTrasmUffici->parseEvent();
                        break;

                        proRic::proRicAnauff(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig));
                        break;

                    case $this->nameForm . '_sercod_butt':
                        proRic::proRicAnaservizi(array(
                            'nameForm' => $this->nameForm,
                            'nameFormOrig' => $this->nameFormOrig));
                        break;

                    case $this->nameForm . '_ConfermaDataTermine':
                        $this->proArriDest[$this->rowidAppoggio]['TERMINE'] = $_POST[$this->nameForm . '_Termine'];
                        $this->elaboraAlbero();
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Uff_cod':
                        $codice = $_POST[$this->nameForm . '_Uff_cod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 4 - strlen(trim($codice))) . trim($codice);
                            } else {
                                $codice = str_repeat(" ", 4 - strlen(trim($codice))) . trim($codice);
                            }
                            $anauff_rec = $this->proLib->GetAnauff($codice);
                        } else {
                            break;
                        }
                        if ($anauff_rec) {
                            $this->scaricaUfficio($anauff_rec);
                            $this->elaboraAlbero();
                        }
                        Out::valore($this->nameForm . '_Uff_des', "");
                        Out::valore($this->nameForm . "_Uff_cod", "");
                        Out::setFocus('', $this->nameForm . "_Uff_cod");
                        break;
                    case $this->nameForm . '_sercod':
                        $this->scaricaServizio($_POST[$this->nameForm . '_sercod']);
                        break;

                    case $this->nameForm . '_Dest_cod':
                        $codice = $_POST[$this->nameForm . '_Dest_cod'];
                        if (trim($codice) != "") {
                            if (is_numeric($codice)) {
                                $codice = str_repeat("0", 6 - strlen(trim($codice))) . trim($codice);
                            }
                            $anamed_rec = $this->proLib->GetAnamed($codice, 'codice', 'no', false, true);
                            if (!$anamed_rec) {
                                Out::valore($this->nameForm . "_Dest_cod", "");
                                Out::setFocus('', $this->nameForm . "_Dest_cod");
                            } else {
                                Out::valore($this->nameForm . "_Dest_cod", "");
                                Out::setFocus('', $this->nameForm . "_Dest_cod");
                                $uffdes_tab = $this->proLib->getGenericTab("SELECT ANAUFF.UFFCOD, UFFFI1__1 FROM UFFDES LEFT OUTER JOIN ANAUFF ON UFFDES.UFFCOD=ANAUFF.UFFCOD WHERE UFFDES.UFFCESVAL='' AND UFFDES.UFFKEY='$codice' AND ANAUFF.UFFANN=0");
                                if (count($uffdes_tab) == 1) {
                                    $anauff_rec = $this->proLib->GetAnauff($uffdes_tab[0]['UFFCOD']);
                                    foreach ($this->proArriDest as $key => $destinatario) {
                                        if ($destinatario['DESCOD'] == $codice) {
                                            $this->proArriDest[$key]['DESCUF'] = $anauff_rec['UFFCOD'];
                                            break;
                                        }
                                    }
                                    $this->caricaDestinatarioInterno($codice, 'codice', $anauff_rec['UFFCOD'], '', $uffdes_tab[0]['UFFFI1__1']);
                                    $this->scaricaUfficio($anauff_rec, 'false');
                                    $this->elaboraAlbero();
                                } else {
                                    $this->varAppoggio = $codice;
                                    $nameForm = array(
                                        'nameForm' => $this->nameForm,
                                        'nameFormOrig' => $this->nameFormOrig);
                                    proRic::proRicUfficiPerDestinatario($nameForm, $anamed_rec['MEDCOD'], '', $codice, '', "Seleziona l'ufficio per il destinatario: " . $anamed_rec['MEDNOM']);
                                }
                            }
                        }
                        Out::valore($this->nameForm . '_Dest_nome', "");
                        break;
                }
                break;

            case 'suggest':
                if ($this->consultazione != true) {
                    switch ($_POST['id']) {
                        case $this->nameForm . '_Uff_des':
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('UFFDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANAUFF WHERE UFFANN <> 1 AND " . $where;
                            $anauff_tab = $this->proLib->getGenericTab($sql);
                            if (count($anauff_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anauff_tab as $anauff_rec) {
                                    itaSuggest::addSuggest($anauff_rec['UFFDES'], array($this->nameForm . "_Uff_cod" => $anauff_rec['UFFCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                        case $this->nameForm . '_serdes':
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('SERDES') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANASERVIZI WHERE " . $where;
                            $anaservizi_tab = $this->proLib->getGenericTab($sql);
                            if (count($anaservizi_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anaservizi_tab as $anaservizi_rec) {
                                    itaSuggest::addSuggest($anaservizi_rec['SERDES'], array($this->nameForm . "_sercod" => $anaservizi_rec['SERCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;

                        case $this->nameForm . '_Dest_nome':
                            $filtroUff = "MEDUFF" . $this->PROT_DB->isNotBlank();
                            /* new suggest */
                            $q = itaSuggest::getQuery();
                            itaSuggest::setNotFoundMessage('Nessun risultato.');
                            $parole = explode(' ', $q);
                            foreach ($parole as $k => $parola) {
                                $parole[$k] = $this->PROT_DB->strUpper('MEDNOM') . " LIKE '%" . addslashes(strtoupper($parola)) . "%'";
                            }
                            $where = implode(" AND ", $parole);
                            $sql = "SELECT * FROM ANAMED WHERE MEDANN=0 AND $filtroUff AND " . $where;
                            $anamed_tab = $this->proLib->getGenericTab($sql);
                            if (count($anamed_tab) > 100) {
                                itaSuggest::setNotFoundMessage('Troppi dati estratti. Prova ad inserire più elementi di ricerca.');
                            } else {
                                foreach ($anamed_tab as $anamed_rec) {
                                    itaSuggest::addSuggest($anamed_rec['MEDNOM'], array($this->nameForm . "_Dest_cod" => $anamed_rec['MEDCOD']));
                                }
                            }
                            itaSuggest::sendSuggest();
                            break;
                    }
                }
                break;

            case 'returnanauff':
                echo $anauff_rec['UFFDES'] . "|" . $this->nameForm . '_Uff_cod' . "|" . $anauff_rec['UFFCOD'] . "\n";
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_Uff_cod', $anauff_rec['UFFCOD']);
                Out::valore($this->nameForm . '_Uff_des', $anauff_rec['UFFDES']);
                Out::setFocus('', $this->nameForm . '_Uff_cod');
                break;

            case 'returnanaservizi':
                $this->scaricaServizio($_POST['retKey'], 'rowid');
                break;

            case 'returnUfficiPerDestinatario':
                $anauff_rec = $this->proLib->GetAnauff($_POST['retKey'], 'rowid');
                $uffdes_rec = $this->proLib->GetUffdes(array('UFFKEY' => $this->varAppoggio, 'UFFCOD' => $anauff_rec['UFFCOD']), 'ruolo');
                $this->caricaDestinatarioInterno($uffdes_rec['UFFKEY'], 'codice', $uffdes_rec['UFFCOD'], '', $uffdes_rec['UFFFI1__1']);

                $this->scaricaUfficio($anauff_rec, 'false');
                $this->elaboraAlbero();
                Out::valore($this->nameForm . "_Dest_cod", "");
                Out::setFocus('', $this->nameForm . "_Dest_cod");
                break;

            case 'returnDestinatari':
                if ($_POST['retKey']) {
                    $rowid_sel = explode(",", $_POST['retKey']);
                }
//---controllo che non ci siano nominativi doppi
                $rowid_anamed = array();
                $rowid_err = array();
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr1 = explode('-', $rowids);
                    if (array_search($rowid_arr1[2], $rowid_anamed) === false) {
                        $rowid_anamed[] = $rowid_arr1[2];
                    } else {
                        $rowid_err[] = $rowid_arr1[2];
                    }
                }
                if ($rowid_err) {
                    $nomi = "";
                    foreach ($rowid_err as $rowid) {
                        $anamed_rec = $this->proLib->GetAnamed($rowid, 'rowid');
                        $nomi .= "\n" . $anamed_rec['MEDNOM'];
                    }
                    $fl_msg = true;
                }
//----

                $primo = true;
                foreach ($rowid_sel as $rowids) {
                    $rowid_arr = explode('-', $rowids);
//$anaservizi_rec = $this->proLib->getAnaservizi($rowid_arr[0], 'rowid');
                    $anauff_rec = $this->proLib->GetAnauff($rowid_arr[1], 'rowid');
                    $anamed_rec = $this->proLib->GetAnamed($rowid_arr[2], 'rowid');
//$anaruo_rec = $this->proLib->getAnaruoli($rowid_arr[3], 'rowid');
                    if (!$anamed_rec) {
                        continue;
                    }
                    $inserisci = true;
                    if (array_search($rowid_arr[2], $rowid_err) !== false) {
                        $inserisci = false;
                    }
                    foreach ($this->proArriDest as $key => $value) {
                        if ($anamed_rec['MEDCOD'] == $value['DESCOD']) {
                            $inserisci = false;
                            break;
                        }
                    }
                    if ($inserisci == true) {
                        $salvaDest = array();
                        $salvaDest['ROWID'] = 0;
                        $salvaDest['DESPAR'] = $this->tipoProt;
                        $salvaDest['DESCOD'] = $anamed_rec['MEDCOD'];
                        $salvaDest['DESNOM'] = $anamed_rec['MEDNOM'];
                        $salvaDest['DESIND'] = $anamed_rec['MEDIND'];
                        $salvaDest['DESCAP'] = $anamed_rec['MEDCAP'];
                        $salvaDest['DESCIT'] = $anamed_rec['MEDCIT'];
                        $salvaDest['DESPRO'] = $anamed_rec['MEDPRO'];
                        $salvaDest['DESDAT'] = $this->workDate;
                        $salvaDest['DESDAA'] = $this->workDate;
                        $salvaDest['DESDUF'] = '';
                        $salvaDest['DESANN'] = '';
                        $salvaDest['DESMAIL'] = $anamed_rec['MEDEMA'];
                        $salvaDest['DESCUF'] = $anauff_rec['UFFCOD'];
                        $salvaDest['DESGES'] = 1;
                        if ($primo) {
                            $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                        } else {
                            $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">0</span>';
                        }
                        $salvaDest['TERMINE'] = '';
                        $this->proArriDest[] = $salvaDest;
                        $anauff_rec = $this->proLib->GetAnauff($salvaDest['DESCUF']);
                        $this->scaricaUfficio($anauff_rec, 'false');
                        $primo = false;
                    }
                }
                $this->elaboraAlbero();
                if ($fl_msg) {
                    Out::msgStop("Attenzione", "I seguenti nominativi risultano selezionati più volte:\n\r" . $nomi);
                }

                break;

            case 'returnFromSeleTrasmUfficio':
                $TipoSelezione = $_POST['tipoSelezione'];
                $retUffici = $_POST['retUffici'];
                if ($TipoSelezione == 'Persona') {
                    foreach ($retUffici as $anauff_rec) {
                        if ($anauff_rec) {
                            $this->scaricaUfficio($anauff_rec);
                            $this->elaboraAlbero();
                        }
                    }
                } else {
                    foreach ($retUffici as $anauff_rec) {
                        if ($anauff_rec) {
                            $this->scaricaUfficio($anauff_rec, 'true', null, true);
                            $this->elaboraAlbero();
                        }
                    }
                }
                break;
        }
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_tipoProt');
        App::$utente->removeKey($this->nameForm . '_anapro_rec');
        App::$utente->removeKey($this->nameForm . '_consultazione');
        App::$utente->removeKey($this->nameForm . '_disabledRec');
        App::$utente->removeKey($this->nameForm . '_bloccoDaEmail');
        App::$utente->removeKey($this->nameForm . '_annullato');
        App::$utente->removeKey($this->nameForm . '_nameFormChiamante');
        App::$utente->removeKey($this->nameForm . '_proArriDest');
        App::$utente->removeKey($this->nameForm . '_proArriUff');
        App::$utente->removeKey($this->nameForm . '_StatoToggle');
        App::$utente->removeKey($this->nameForm . '_profilo');
        App::$utente->removeKey($this->nameForm . '_proDestinatari');
        App::$utente->removeKey($this->nameForm . '_rowidAppoggio');
        App::$utente->removeKey($this->nameForm . '_varAppoggio');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function AzzeraVariabili() {
        $this->proArriDest = array();
        $this->proArriUff = array();
        $this->consultazione = '';
        $this->disabledRec = '';
        $this->varAppoggio = '';
        $this->proDestinatari = array();
        Out::clearFields($this->nameForm, $this->nameForm);
        TableView::clearGrid($this->gridDestinatari);
    }

    public function AbilitaCampi() {
        if ($this->tipoProt == 'C') {
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');
            Out::show($this->nameForm . '_Uff_cod_butt');
        } else if ($this->tipoProt == 'A') {
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');
            Out::show($this->nameForm . '_Uff_cod_butt');
        } else if ($this->tipoProt == 'P') {
            Out::show($this->nameForm . '_divCampiDest');
            Out::show($this->nameForm . '_divCampiUff');

            Out::show($this->nameForm . '_Uff_cod_butt');
        }
        $anaent_25 = $this->proLib->GetAnaent('25');
        if ($anaent_25['ENTDE1'] === '1') {
            Out::hide($this->nameForm . '_divCampiUff');
        }
    }

    /*
     * Funzione per controllo permessi su Archivio Mittenti/Destinatari
     */

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

    public function Nuovo() {
        $this->toggleAssegnazioniInterne('abilita');
        $this->AzzeraVariabili();
        $this->Nascondi();
        $this->AbilitaCampi();
        $this->elaboraAlbero();

        $this->consultazione = false;
        $this->proDestinatari = array();
    }

    public function Nascondi($nuovo = true) {
        if ($nuovo) {
            
        }
    }

    public function CheckProtConservato($Anapro_rec) {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        if ($ProConser_rec) {
            $this->DisabilitaDatiConservazione(); //RIATTIVARE! PER I TEST COMMENTATO.
        }
    }

    public function DisabilitaDatiConservazione() {
        Out::hide($this->nameForm . '_MittentiAggiuntivi');
        $this->toggleAssegnazioniInterne('disabilita');
    }

    public function getDatiAnapro() {
        $anapro_rec = $_POST[$this->nameForm . "_ANAPRO"];
        return $anapro_rec;
    }

    public function elaboraAlbero() {
        $destArrivo = $this->proArriDest;
        $i = 0;
        $matrice = array();
        $matrice[0]['level'] = 0;
        $matrice[0]['parent'] = '';
        $matrice[0]['isLeaf'] = 'true';
        $matrice[0]['expanded'] = 'true';
        $matrice[0]['loaded'] = 'true';
        $matrice[0]['DESCRIZIONE'] = 'ASSEGNATARI:';
        $matrice[0]['GESTIONE'] = '';
        $matrice[0]['RESPONSABILE'] = '';
        $matrice[0]['DESORIGINALE'] = '';
        $matrice[0]['CHIAVE'] = 0;
        $matrice[0]['VALORE'] = '';
        $matrice[0]['TERMINE'] = '';
        if ($this->proArriUff) {
            $matrice[0]['isLeaf'] = 'false';
        }
        if ($destArrivo) {
            $matrice[0]['isLeaf'] = 'false';
        }
        foreach ($this->proArriUff as $key => $recordUff) {
            $i = $i + 1;
            $matrice[$i] = $recordUff;
            $matrice[$i]['level'] = 1;
            $matrice[$i]['parent'] = 0;
            $matrice[$i]['DESCRIZIONE'] = $recordUff['UFFDES'];
            if (!$recordUff['UFFCOD']) {
                $matrice[$i]['DESCRIZIONE'] = ' Trasmissione a persona ';
            }
//            if ($recordUff['UFFFI1'] == '1') {
//                $matrice[$i]['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
//            } else {
            $matrice[$i]['GESTIONE'] = '';
//            }

            $matrice[$i]['RESPONSABILE'] = '';
            $matrice[$i]['DESORIGINALE'] = '';
            $matrice[$i]['CHIAVE'] = $i;
            $matrice[$i]['INDICE'] = $key;
            $matrice[$i]['TERMINE'] = '';
            $matrice[$i]['VALORE'] = 'UFFPRO:' . $recordUff['UFFCOD'];
            $risultato = $this->checkPresenzaDest($destArrivo, $recordUff['UFFCOD']);
            if ($risultato === false) {
                $matrice[$i]['isLeaf'] = 'true';
                $matrice[$i]['expanded'] = 'false';
                $matrice[$i]['loaded'] = 'false';
            } else {
                $matrice[$i]['isLeaf'] = 'false';
                $matrice[$i]['expanded'] = 'true';
                $matrice[$i]['loaded'] = 'true';
                $padre = $i;
                foreach ($destArrivo as $key => $destinatario) {
                    if ($destinatario['DESCUF'] == $recordUff['UFFCOD']) {
                        $i = $i + 1;
                        $matrice[$i] = $destinatario;
                        $matrice[$i]['level'] = 2;
                        $matrice[$i]['parent'] = $padre;
                        $matrice[$i]['isLeaf'] = 'true';
                        $matrice[$i]['expanded'] = 'false';
                        $matrice[$i]['loaded'] = 'false';
                        $anaruoli_rec = $this->proLib->getAnaruoli($destinatario['DESRUOLO']);
                        if ($anaruoli_rec) {
                            $anaruoli_rec['RUODES'] = " - " . $anaruoli_rec['RUODES'];
                        }
                        $matrice[$i]['DESCRIZIONE'] = $destinatario['DESNOM'] . $anaruoli_rec['RUODES'];
                        // Trasmissione ad Ufficio
                        if (!$destinatario['DESCOD'] && $destinatario['DESCUF']) {
                            $matrice[$i]['DESCRIZIONE'] = '<p style = " background:#B2F7DC;">TRASMISSIONE A INTERO UFFICIO</p>';
                        }
                        if ($destinatario['DESGES'] == '1') {
                            $matrice[$i]['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
                        } else {
                            $matrice[$i]['GESTIONE'] = '';
                        }
                        if ($destinatario['DESRES'] == 'true' || $destinatario['DESRES'] == 1) {
                            $matrice[$i]['RESPONSABILE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>';
                        } else {
                            $matrice[$i]['RESPONSABILE'] = '';
                        }
                        if ($destinatario['DESORIGINALE'] == 'true') {
                            $matrice[$i]['DESORIGINALE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>';
                        } else {
                            $matrice[$i]['DESORIGINALE'] = '';
                        }
                        // Invio Mail
                        if ($destinatario['DESINV'] == '1') {
                            $matrice[$i]['INVIOMAIL'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>';
                        } else {
                            $matrice[$i]['INVIOMAIL'] = '';
                        }

                        $matrice[$i]['CHIAVE'] = $i;
                        $matrice[$i]['INDICE'] = $key;
                        $matrice[$i]['TERMINE'] = $destinatario['TERMINE'];
                        $matrice[$i]['VALORE'] = 'ANADES:' . $destinatario['DESCOD'];

                        if ($destinatario['DESIDMAIL']) {
                            $matrice[$i]['MAILDESTINT'] = '<span class="ui-icon ui-icon-mail-closed"></span>';
                            $retRic = $this->proLib->checkMailRic($destinatario['DESIDMAIL']);
                            if ($retRic['ACCETTAZIONE']) {
                                $matrice[$i]['ACCDESTINT'] = '<span class="ui-icon ui-icon-check"></span>';
                                $matrice[$i]['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                            }
                            if ($retRic['CONSEGNA']) {
                                $matrice[$i]['CONSDESTINT'] = '<span class="ui-icon ui-icon-check"></span>';
                                $matrice[$i]['IDCONSEGNA'] = $retRic['CONSEGNA'];
                            }
                            if ($retRic['NOTIFICHE']) {
                                $matrice[$i]['NOTIFICAPECINT'] = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>";
                                $matrice[$i]['IDNOTIFICAPECINT'] = $retRic['NOTIFICHE'];
                            }
                        }
                        unset($destArrivo[$key]);
                    }
                }
            }
        }
        foreach ($destArrivo as $key => $recordDest) {
            $i = $i + 1;
            $matrice[$i] = $recordDest;
            $matrice[$i]['level'] = 1;
            $matrice[$i]['parent'] = 0;
            $matrice[$i]['isLeaf'] = 'true';
            $matrice[$i]['expanded'] = 'false';
            $matrice[$i]['loaded'] = 'false';
            $anaruoli_rec = $this->proLib->getAnaruoli($recordDest['DESRUOLO']);
            if ($anaruoli_rec) {
                $anaruoli_rec['RUODES'] = " - " . $anaruoli_rec['RUODES'];
            }
            $matrice[$i]['DESCRIZIONE'] = $recordDest['DESNOM'] . $anaruoli_rec['RUODES'];
            if ($recordDest['DESGES'] == '1') {
                $matrice[$i]['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
            } else {
                $matrice[$i]['GESTIONE'] = '';
            }
            if ($recordDest['DESRES'] == 'true' || $recordDest['DESRES'] == 1) {
                $matrice[$i]['RESPONSABILE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>';
            } else {
                $matrice[$i]['RESPONSABILE'] = '';
            }
            if ($recordDest['DESORIGINALE'] == 'true') {
                $matrice[$i]['DESORIGINALE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">true</span>';
            } else {
                $matrice[$i]['DESORIGINALE'] = '';
            }
            $matrice[$i]['CHIAVE'] = $i;
            $matrice[$i]['INDICE'] = $key;
            $matrice[$i]['TERMINE'] = $recordDest['TERMINE'];

            if ($recordDest['DESIDMAIL']) {
                $matrice[$i]['MAILDESTINT'] = '<span class="ui-icon ui-icon-mail-closed"></span>';
                $retRic = $this->proLib->checkMailRic($recordDest['DESIDMAIL']);
                if ($retRic['ACCETTAZIONE']) {
                    $matrice[$i]['ACCDESTINT'] = '<span class="ui-icon ui-icon-check"></span>';
                    $matrice[$i]['IDACCETTAZIONE'] = $retRic['ACCETTAZIONE'];
                }
                if ($retRic['CONSEGNA']) {
                    $matrice[$i]['CONSDESTINT'] = '<span class="ui-icon ui-icon-check"></span>';
                    $matrice[$i]['IDCONSEGNA'] = $retRic['CONSEGNA'];
                }
                if ($retRic['NOTIFICHE']) {
                    $matrice[$i]['NOTIFICAPECINT'] = "<div style =\"display:inline-block; border:0;\" class=\"ita-html ui-state-error\"><span class=\"ui-icon ui-icon-alert ita-tooltip\" title=\"Riscontrate Anomalie PEC\"></span></div>";
                    $matrice[$i]['IDNOTIFICAPECINT'] = $retRic['NOTIFICHE'];
                }
            }

            $matrice[$i]['VALORE'] = 'ANADES:' . $recordDest['DESCOD'];
        }
        $this->proDestinatari = $matrice;
        if ($matrice) {
            $this->caricaGriglia($this->gridDestinatari, $this->proDestinatari);
        }
    }

    public function scaricaUfficio($anauff_rec, $scaricaDest = 'true', $forzaGestisci = null, $AssegnaUfficio = false) {
        if ($anauff_rec['UFFANN'] == 1) {
            Out::msgInfo("Decodifica Ufficio", "ATTENZIONE.<BR>Uffico " . $anauff_rec['UFFCOD'] . "  "
                    . $anauff_rec['UFFDES'] . " non più utilizzabile. Annullato.");
        } else if ($anauff_rec) {
            $inserisci = true;
            foreach ($this->proArriUff as $value) {
                if ($anauff_rec['UFFCOD'] == $value['UFFCOD']) {
                    $inserisci = false;
                    break;
                }
            }
            if ($inserisci == true) {
                $this->proArriUff[] = array('UFFCOD' => $anauff_rec['UFFCOD'], 'UFFDES' => $anauff_rec['UFFDES'], 'UFFFI1' => 1);
                if ($scaricaDest == 'true') {
                    if ($AssegnaUfficio) {
                        $this->assegnaUfficio($anauff_rec['UFFCOD']);
                    } else {
                        $this->confermaScarico($anauff_rec['UFFCOD'], $forzaGestisci);
                    }
                }
                $anaent_32 = $this->proLib->GetAnaent('32');
                if ($anaent_32['ENTDE5'] == '1') {
                    $anamed_rec = $this->proLib->GetAnamed($anauff_rec['UFFRES'], 'codice', 'no', false, true);
                    if ($anamed_rec) {
                        //@TODO Eliminare parte commentata dopo test
                        //$this->caricaDestinatarioInterno($anamed_rec['MEDCOD'], 'codice', $anauff_rec['UFFCOD'], 'true');
                        $this->caricaDestinatarioInterno($anamed_rec['MEDCOD'], 'codice', $anauff_rec['UFFCOD']);
                    }
                }
            }
        }
    }

    public function toggleAssegnazioniInterne($modo = 'disabilita') {
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
        Out::$hideShow($this->gridDestinatari . '_addGridRow');
        Out::$hideShow($this->gridDestinatari . '_delGridRow');
        Out::$hideShow($this->gridDestinatari . '_divCamp');
        $this->StatoToggle['toggleAssegnazioniInterne'] = $modo;
    }

    public function Decod($anapro_rec, $riscontro = false) {
        if ($this->tipoProt == 'P') {
            $this->proArriUff = $this->proLib->caricaUffici($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
        }
        $this->caricaAlbero($anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $riscontro);
    }

    public function Modifica() {
        $anapro_rec = $this->anapro_rec;
        //$this->AzzeraVariabili();
        $this->toggleAssegnazioniInterne('abilita');
        $this->Nascondi(false);
        $this->AbilitaCampi();
        $this->Decod($anapro_rec);
        $anaent_37 = $this->proLib->GetAnaent('37');

        if ($this->consultazione) {
            $this->toggleAssegnazioniInterne('disabilita');
        } elseif ($this->disabledRec) {
            if (!$this->profilo['OK_ANNULLA'] && $anaent_37['ENTDE5']) {
                $this->toggleAssegnazioniInterne('disabilita');
            }
        }
    }

    public function scaricaServizio($codice, $tipo = 'codice') {
        $anaservizi_rec = $this->proLib->getAnaservizi($codice, $tipo);
        if ($anaservizi_rec) {
            $anauff_tab = $this->proLib->GetAnauff($anaservizi_rec['SERCOD'], 'uffser');
            foreach ($anauff_tab as $anauff_rec) {
                $this->scaricaUfficio($anauff_rec);
            }
            $this->elaboraAlbero();
        }
        Out::valore($this->nameForm . '_sercod', "");
        Out::valore($this->nameForm . "_serdes", "");
    }

    public function assegnaDestinatari($destinatari) {
        foreach ($destinatari as $destinatario) {
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESCOD'] = $destinatario['CodiceDestinatario'];
            $salvaDest['DESNOM'] = $destinatario['Denominazione'];
            $salvaDest['DESIND'] = $destinatario['Indirizzo'];
            $salvaDest['DESCAP'] = $destinatario['CAP'];
            $salvaDest['DESCIT'] = $destinatario['Citta'];
            $salvaDest['DESPRO'] = $destinatario['Provincia'];
            $salvaDest['DESDAT'] = $this->workDate;
            $salvaDest['DESDAA'] = $this->workDate;
            $salvaDest['DESDUF'] = '';
            $salvaDest['DESANN'] = $destinatario['Annotazioni'];
            $salvaDest['DESTIPO'] = 'T';
            $salvaDest['DESMAIL'] = '';
            $salvaDest['DESCUF'] = $destinatario['Ufficio'];
            $salvaDest['DESGES'] = 1;
            $salvaDest['TERMINE'] = '';
            $salvaDest['GESTIONE'] = '<span style="margin-left:auto; margin-right:auto;" class="ui-icon ui-icon-check">1</span>';
            $this->proArriDest[] = $salvaDest;
        }
    }

    public function caricaAlbero($codice, $tipoProt, $riscontro = false) {
        $this->proArriUff = $this->proLib->caricaUffici($codice, $tipoProt);
        $this->proArriDest = $this->proLib->caricaDestinatari($codice, $tipoProt, array(), true);
        /*
         * Se è un riscontro rimuovo se stesso dalle trasmissioni.
         */
        if ($riscontro) {
            $this->ElaboraALberoDaRiscontro();
        }
        $this->elaboraAlbero();
    }

    public function ElaboraALberoDaRiscontro() {
        $CodiceUtente = $this->profilo['COD_SOGGETTO'];
        foreach ($this->proArriDest as $key => $proArriDest) {
            if ($CodiceUtente == $proArriDest['DESCOD']) {
                unset($this->proArriDest[$key]);
                $descuff = $proArriDest['DESCUF'];
                $risultato = $this->checkPresenzaDest($this->proArriDest, $descuff);
                if ($risultato === false) {
                    foreach ($this->proArriUff as $keyUff => $proArriUff) {
                        if ($proArriUff['UFFCOD'] == $descuff) {
                            unset($this->proArriUff[$keyUff]);
                        }
                    }
                }
            }
        }
    }

    public function checkPresenzaDest($dest, $codUff = '') {
        foreach ($dest as $key => $destinatario) {
            if ($destinatario['DESCUF'] == $codUff) {
                return $key;
            }
        }
        return false;
    }

    public function checkUfficio($uffici, $codUff) {
        foreach ($uffici as $ufficio) {
            if ($ufficio['UFFCOD'] == $codUff) {
                return $ufficio;
            }
        }
        return false;
    }

    public function confermaScarico($codice, $forzaGestisci = null) {
        $uffdes_tab = $this->proLib->GetUffdes($codice, 'uffcod', true, ' ORDER BY UFFFI1__3 DESC', true);
        foreach ($uffdes_tab as $uffdes_rec) {
            if ($forzaGestisci === null) {
                $gestisci = $uffdes_rec['UFFFI1__1'];
            } else {
                $gestisci = $forzaGestisci;
            }
            $this->caricaDestinatarioInterno($uffdes_rec['UFFKEY'], 'codice', $uffdes_rec['UFFCOD'], '', $gestisci);
        }
        $this->elaboraAlbero();
    }

    public function assegnaUfficio($uffcod) {
        $presente = false;
        foreach ($this->proArriDest as $key => $value) {
            if ($value['DESCOD'] == '' && $uffcod == $value['DESCUF']) {
                $presente = true;
                break;
            }
        }
        if (!$presente) {
            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESCOD'] = '';
            $salvaDest['DESNOM'] = 'TRASMISSIONE A INTERO UFFICIO';
            $salvaDest['DESDAT'] = $this->workDate;
            $salvaDest['DESDAA'] = $this->workDate;
            $salvaDest['DESDUF'] = '';
            $salvaDest['DESANN'] = '';
            $salvaDest['DESMAIL'] = '';
            $salvaDest['DESCUF'] = $uffcod;
            $salvaDest['DESGES'] = 1;
            $salvaDest['DESRES'] = '';
            $salvaDest['TERMINE'] = '';
            $salvaDest['DESORIGINALE'] = '';
            $salvaDest['DESRUOLO'] = '';
            $this->proArriDest[] = $salvaDest;
        }
    }

    /**
     *
     * @param type $codice
     * @param type $tipo
     * @param type $uffcod
     * @param boolean $responsabile Forza l'inserimento del soggettto come responsabile<br>
     *                              anche se il soggetto non è responsabile ufficio
     * @param type $gestisci
     * @return boolean
     */
    public function caricaDestinatarioInterno($codice, $tipo = 'codice', $uffcod = '', $responsabile = '', $gestisci = '') {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no', false, true);
        if (!$anamed_rec) {
            return false;
        }
        $anauff_rec = $this->proLib->GetAnauff($uffcod);
        if ($anauff_rec['UFFRES'] == $anamed_rec['MEDCOD']) {
            $responsabile = true;
        }

        $presente = false;
        $presenteKey = null;
        $selResponsabile = null;
        foreach ($this->proArriDest as $key => $value) {
            if ($anamed_rec['MEDCOD'] == $value['DESCOD'] && $uffcod == $value['DESCUF']) {
                $presente = true;
                $presenteKey = $key;
                break;
            }
        }

        if (!$presente) {
            $uffdes_rec = $this->proLib->getGenericTab("SELECT * FROM UFFDES WHERE UFFKEY='$codice' AND UFFCOD='$uffcod'", false);
            $salvaDest = array();
            $salvaDest['ROWID'] = 0;
            $salvaDest['DESPAR'] = $this->tipoProt;
            $salvaDest['DESCOD'] = $anamed_rec['MEDCOD'];
            $salvaDest['DESNOM'] = $anamed_rec['MEDNOM'];
            $salvaDest['DESIND'] = $anamed_rec['MEDIND'];
            $salvaDest['DESCAP'] = $anamed_rec['MEDCAP'];
            $salvaDest['DESCIT'] = $anamed_rec['MEDCIT'];
            $salvaDest['DESPRO'] = $anamed_rec['MEDPRO'];
            $salvaDest['DESSER'] = $anauff_rec['UFFSER'];
            $salvaDest['DESDAT'] = $this->workDate;
            $salvaDest['DESDAA'] = $this->workDate;
            $salvaDest['DESDUF'] = '';
            $salvaDest['DESANN'] = '';
            $salvaDest['DESMAIL'] = $anamed_rec['MEDEMA'];
            $salvaDest['DESCUF'] = $uffcod;
            $salvaDest['DESGES'] = $gestisci;
            $salvaDest['DESRES'] = $responsabile;
            $salvaDest['TERMINE'] = '';
            $salvaDest['DESORIGINALE'] = '';
            $salvaDest['DESRUOLO'] = $uffdes_rec['UFFFI1__2'];
            $this->proArriDest[] = $salvaDest;
        }
        return $anamed_rec;
    }

    public function CaricaGrigliaDestinatari() {
        $this->caricaGriglia($this->gridDestinatari, $this->proDestinatari);
    }

    public function setRiscontro() {
        if ($this->tipoProt == 'A') {

            foreach ($this->proArriUff as $ufficio) {
                $this->confermaScarico($ufficio['UFFCOD']);
            }
        }
        foreach ($this->proArriDest as $key => $value) {
            $value = $value;
            $this->proArriDest[$key]['STATO'] = '';
            $this->proArriDest[$key]['DESIDMAIL'] = '';
        }
        $this->elaboraAlbero();
    }

    public function duplicaDoc() {
        foreach ($this->proArriDest as $key => $value) {
            $value = $value;
            $this->proArriDest[$key]['STATO'] = '';
            $this->proArriDest[$key]['DESIDMAIL'] = '';
        }
        $this->elaboraAlbero();
    }

}
