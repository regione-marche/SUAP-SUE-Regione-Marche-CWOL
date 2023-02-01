<?php

/* * 
 *
 * RIEPILOGO PROTOCOLLAZIONE
 *
 * PHP Version 5
 *
 * @category
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    02.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibPratica.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';

function proMsgQuestion() {
    if (!proMsgQuestion::$singleton) {
        proMsgQuestion::$singleton = new proMsgQuestion();
    }
    proMsgQuestion::$singleton->parseEvent();
    return;
}

class proMsgQuestion extends itaModel {

    public static $singleton;
    public $PROT_DB;
    public $proLib;
    public $nameForm = "proMsgQuestion";
    public $avviso = '';
    public $proNum;
    public $proTipo;
    public $datiProt;
    public $proLibPratica;
    public $proLibAllegati;
    public $proLibConservazione;

    function __construct() {
        parent::__construct();
        // Apro il DB
        try {
            $this->PROT_DB = ItaDB::DBOpen('PROT');
            $this->avviso = App::$utente->getKey($this->nameForm . '_avviso');
            $this->proNum = App::$utente->getKey($this->nameForm . '_proNum');
            $this->proTipo = App::$utente->getKey($this->nameForm . '_proTipo');
            $this->datiProt = App::$utente->getKey($this->nameForm . '_datiProt');
            $this->proLibAllegati = new proLibAllegati();

            $this->proLib = new proLib();
            $this->proLibPratica = new proLibPratica();
            $this->proLibConservazione = new proLibConservazione();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_avviso', $this->avviso);
            App::$utente->setKey($this->nameForm . '_proNum', $this->proNum);
            App::$utente->setKey($this->nameForm . '_proTipo', $this->proTipo);
            App::$utente->setKey($this->nameForm . '_datiProt', $this->datiProt);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                /* Rimosso in attesa di definizione utilizzo */
                Out::removeElement($this->nameForm . '_Fascicola');
                include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
                $proLib = new proLib();
                $anaent_32 = $proLib->GetAnaent('32');
                if ($anaent_32['ENTDE4'] == 1) {
                    Out::removeElement($this->nameForm . '_Ricevuta');
                    /* Commentata istruzione per richiesta cliente,
                     * comunque chiede di allegare se allegati obbligatori, 
                     * prima di procedere
                     */
//                    Out::removeElement($this->nameForm . '_Nuovo');
                }

                $anaent_29 = $proLib->GetAnaent('29');
                if ($anaent_29['ENTDE4'] == 1) {
                    Out::removeElement($this->nameForm . '_Ricevuta');
                    Out::removeElement($this->nameForm . '_Fascicola');
                    Out::removeElement($this->nameForm . '_FileLocale');
                    Out::removeElement($this->nameForm . '_Scanner');
                    Out::removeElement($this->nameForm . '_Notifica');
                    Out::removeElement($this->nameForm . '_Raccomandata');
                } else {
                    Out::codice("pluploadActivate('" . $this->nameForm . "_FileLocale_uploader');");
                }

                $this->datiProt = $_POST[$this->nameForm . '_DATIPROT'];
                $this->CheckProtocolloConservato();
                $this->proNum = $this->datiProt['PRONUM'];
                $this->proTipo = $this->datiProt['PROPAR'];
                $prot = substr($this->datiProt['PRONUM'], 4);
                $segnatura = $this->datiProt['PROSEG'];
                $data = substr($this->datiProt['PRODAR'], 6) . '/' . substr($this->datiProt['PRODAR'], 4, 2) . '/' . substr($this->datiProt['PRODAR'], 0, 4);
                $tipoSpedizione = $this->datiProt['PROTSP'];
                $classificazione = $this->datiProt['PROCCF'] . $this->datiProt['PROARG'];
                if (strlen($_POST[$this->nameForm . '_Classificazione']) >= 4)
                    $classificazione = substr($_POST[$this->nameForm . '_Classificazione'], 0, 4);
                if (strlen($_POST[$this->nameForm . '_Classificazione']) >= 8)
                    $classificazione.= "-" . substr($_POST[$this->nameForm . '_Classificazione'], 4, 4);
                if (strlen($_POST[$this->nameForm . '_Classificazione']) >= 12)
                    $classificazione.= "-" . substr($_POST[$this->nameForm . '_Classificazione'], 8, 4);
                if (strlen($_POST[$this->nameForm . '_Classificazione']) > 12)
                    $classificazione.= "-" . substr($_POST[$this->nameForm . '_Classificazione'], 12);
                $messaggio = '';
                if ($anaent_29['ENTDE4'] == 1) {
                    $messaggio = '<span style="font-weight:bold;color:red;font-size:2.2em;">Attenzione: Questo Protocollo è di Emergenza</span><br>';
                }
                $messaggio .= '<span style="font-weight:bold;color:red;font-size:2em;"><br>Registrazione protocollo <br>N. ' . $prot
                        . '</span><br><br><span style="font-weight:bold;color:black;font-size:1.2em;">' . '<br>Segnatura:<br>' . $segnatura . '<br><br>' . 'Data: '
                        . $data . "<br><br>Titolario: " . addslashes($classificazione) . "</span><br><br>";
                $this->avviso = '';
                $this->avviso = $_POST[$this->nameForm . '_avviso'];
                if ($this->avviso) {
                    $messaggio .= '<span style="font-weight:bold;color:red;font-size:1.8em;">' . $this->avviso . '</span><br><br>';
                    Out::hide($this->nameForm . '_Notifica');
                    Out::hide($this->nameForm . '_Raccomandata');
                    Out::hide($this->nameForm . '_Chiudi');
                    Out::hide($this->nameForm . '_NotificaMittenti');
                } else {
                    $docfirma_tab = $this->proLibAllegati->GetDocFirmaFromArcite($this->proNum, $this->proTipo, true, " AND FIRDATA=''");
                    if (!$docfirma_tab) {
                        if (trim($tipoSpedizione) == 'ROL' && substr($this->proTipo, 0, 1) == 'P') {
                            Out::show($this->nameForm . '_Raccomandata');
                            Out::hide($this->nameForm . '_Notifica');
                            Out::hide($this->nameForm . '_NotificaMittenti');
                        } else {
                            if (substr($this->proTipo, 0, 1) == 'A') {
                                Out::show($this->nameForm . '_NotificaMittenti');
                            } else {
                                Out::hide($this->nameForm . '_NotificaMittenti');
                            }
                            Out::show($this->nameForm . '_Notifica');
                            Out::hide($this->nameForm . '_Raccomandata');
                        }
                    } else {
                        Out::hide($this->nameForm . '_Notifica');
                        Out::hide($this->nameForm . '_NotificaMittenti');
                        Out::hide($this->nameForm . '_Raccomandata');
                    }
                }
                Out::html($this->nameForm . '_boxMsg', $messaggio);
                Out::setFocus('', $this->nameForm . '_Nuovo');
                if ($_POST[$this->nameForm . '_etichette'] == 0) {
                    Out::hide($this->nameForm . '_Etichetta');
                }

                if ($this->datiProt['PROFASKEY']) {
                    Out::hide($this->nameForm . '_Fascicola');
                }

                break;
            case 'onClick':
                $focus = 'proArri_Oggetto';
                if ($_POST['id'] == $this->nameForm . '_Nuovo') {
                    $profilo = proSoggetto::getProfileFromIdUtente();
                    if ($profilo['PROT_ABILITATI'] == '' || $profilo['PROT_ABILITATI'] == 1) {
                        $proLib = new proLib();
                        $oggutenti_check = $proLib->GetOggUtenti($profilo['UTELOG']);
                        if ($oggutenti_check) {
                            $focus = "proArri_ANAPRO[PROUOF]";
                        }
                    }
                }
                switch ($_POST['id']) {
                    case $this->nameForm . '_Torna':
                        if ($this->avviso !== '') {
                            Out::msgQuestion("ATTENZIONE!", "Protocollo senza Documenti Allegati! Sei sicuro?", array(
                                'F8 - No. Voglio allegare' => array('id' => $this->nameForm . '_AnnullaChiusura',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5 - Si. Continua' => array('id' => $this->nameForm . '_ConfermaTorna',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                        $this->returnToParent($focus);
                        break;
                    case $this->nameForm . '_Nuovo':
                        if ($this->avviso !== '') {
                            Out::msgQuestion("ATTENZIONE!", "Protocollo senza Documenti Allegati! Sei sicuro?", array(
                                'F8 - No. Voglio allegare' => array('id' => $this->nameForm . '_AnnullaChiusura',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5 - Si. Continua' => array('id' => $this->nameForm . '_ConfermaNuovo',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ConfermaNuovo':
                        $this->returnToParent($focus);
                    case $this->nameForm . '_Ricevuta':
                    case $this->nameForm . '_Etichetta':
                    case $this->nameForm . '_Notifica':
                    case $this->nameForm . '_NotificaMittenti':
                    case $this->nameForm . '_Raccomandata':
                        $returnModel = 'proArri';
                        $retField = $_POST['id'];
                        $postAppoggio = $_POST;
                        $_POST = array();
                        $_POST['event'] = 'returntoform';
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $retField;
                        $_POST['postAppoggio'] = $postAppoggio;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
                        $returnModel();
                        break;
//                  case $this->nameForm . '_Raccomandata':                                            
//                        itaLib::openForm(ptiROLAppend);
//                        /* @var $ptiROLAppendObj ptiROLAppend */
//                        $ptiROLAppendObj = itaModel::getInstance('ptiROLAppend');
//                        $ptiROLAppendObj->setEvent('openform');
//                        $ptiROLAppendObj->setReturnModel($this->nameForm);
//                        $ptiROLAppendObj->setReturnEvent('returnFromPtiROLAppend');
//                        $ptiROLAppendObj->setReturnId('');
//                        $documentoPrincipale = $this->getDocumentoPrincipale($this->proNum, $this->proTipo);
//                        if ($documentoPrincipale) {
//                            $ptiROLAppendObj->setDocumentName($documentoPrincipale['NAME']);
//                            $ptiROLAppendObj->setDocumentPath($documentoPrincipale['PATH']);
//                        } else {
//                            
//                        }
//                        $ptiROLAppendObj->setRecipients($this->caricaDestinatari($this->proNum, $this->proTipo));
//                        $ptiROLAppendObj->parseEvent();
                        break;
                    case $this->nameForm . '_Chiudi':
                        if ($this->avviso !== '') {
                            Out::msgQuestion("ATTENZIONE!", "Protocollo senza Documenti Allegati! Sei sicuro?", array(
                                'F8 - No. Voglio allegare' => array('id' => $this->nameForm . '_AnnullaChiusura',
                                    'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5 - Si. Continua' => array('id' => $this->nameForm . '_ConfermaChiudi',
                                    'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }
                    case $this->nameForm . '_ConfermaChiudi':
                        $this->returnToParent($focus);
                        $returnModel = 'proArri';
                        $retField = $_POST['id'];
                        $_POST = array();
                        $_POST['event'] = 'returntoform';
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $retField;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
                        $returnModel();
                        break;
                    case $this->nameForm . '_Fascicola':
//                        Out::msgQuestion("Fascicolazione", "Vuoi creare un nuovo fascicolo o selezionarne uno esistente?", array(
//                            'F8 - Esistente' => array('id' => $this->nameForm . '_FascicoloEsistente',
//                                'model' => $this->nameForm, 'shortCut' => "f8"),
//                            'F5 - Nuovo' => array('id' => $this->nameForm . '_FascicoloNuovo',
//                                'model' => $this->nameForm, 'shortCut' => "f5")
//                                )
//                        );
//                        break;
                    case $this->nameForm . '_FascicoloNuovo':
                        $model = 'proArri';
                        $_POST[$model . '_Clacod'] = substr($this->datiProt['PROCCF'], 4, 8);
                        $_POST[$model . '_Fascod'] = substr($this->datiProt['PROCCF'], 8, 12);
                        $_POST[$model . '_TitolarioDecod'] = $this->datiProt['TitolarioDecod'];
                        $_POST[$model . '_ANAPRO'] = $this->datiProt;
                        $formObj = itaModel::getInstance($model);
                        $formObj->setEvent('onClick');
                        $formObj->setElementId($model . '_Fascicola');
                        $formObj->parseEvent();
                        $this->returnToParent($focus);
                        break;
                    case $this->nameForm . '_FascicoloEsistente':
                        $model = 'proArri';
                        $_POST[$model . '_Clacod'] = substr($this->datiProt['PROCCF'], 4, 8);
                        $_POST[$model . '_Fascod'] = substr($this->datiProt['PROCCF'], 8, 12);
                        $_POST[$model . '_TitolarioDecod'] = $this->datiProt['TitolarioDecod'];
                        $_POST[$model . '_ANAPRO'] = $this->datiProt;
                        $formObj = itaModel::getInstance($model);
                        $formObj->setEvent('onClick');
                        $formObj->setElementId($model . '_ANAPRO[PROARG]_butt');
                        $formObj->parseEvent();
                        $this->returnToParent($focus);
                        break;
                    case $this->nameForm . '_ConfermaTorna':
                    case 'close-portlet':
                        $this->returnToParent($focus);
                        break;
                    case $this->nameForm . '_FileLocale':
                        $this->avviso = '';
                        $returnModel = 'proArri';
                        $retField = $_POST['id'];
                        $postAppoggio = $_POST;
                        $this->returnToParent($focus);
                        $_POST = array();
                        $_POST['event'] = 'returntoform';
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $retField;
                        $_POST['postAppoggio'] = $postAppoggio;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
                        App::$utente->setKey($this->nameForm . '_avviso', $this->avviso);
                        $returnModel();
                        break;
                    case $this->nameForm . '_DuplicaProt':
                        $this->avviso = '';
                        $returnModel = 'proArri';
                        $retField = $_POST['id'];
                        $postAppoggio = $_POST;
                        $this->returnToParent($focus);
                        $_POST = array();
                        $_POST['event'] = 'returntoform';
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $retField;
                        $_POST['postAppoggio'] = $postAppoggio;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
                        App::$utente->setKey($this->nameForm . '_avviso', $this->avviso);
                        $returnModel();
                        break;
                    case $this->nameForm . '_Scanner':
                        $returnModel = 'proArri';
                        $retField = $_POST['id'];
                        $postAppoggio = $_POST;
                        $_POST = array();
                        $_POST['event'] = 'returntoform';
                        $_POST['model'] = $returnModel;
                        $_POST['retField'] = $retField;
                        $_POST['postAppoggio'] = $postAppoggio;
                        $phpURL = App::getConf('modelBackEnd.php');
                        $appRouteProg = App::getPath('appRoute.' . substr($returnModel, 0, 3));
                        include_once $phpURL . '/' . $appRouteProg . '/' . $returnModel . '.php';
                        $returnModel();
                        //
                        //  rimessa la chiusura per messaggio di richiesta Marcatura, da firmare e firma documento
                        //
                        $this->returnToParent($focus);
                        break;
                }
                break;
        }
    }

    public function close($focus) {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_avviso');
        App::$utente->removeKey($this->nameForm . '_proNum');
        App::$utente->removeKey($this->nameForm . '_proTipo');
        App::$utente->removeKey($this->nameForm . '_datiProt');
        Out::closeDialog($this->nameForm);
        Out::setFocus('', $focus);
    }

    public function returnToParent($focus = '') {
        $this->close($focus);
    }

    function caricaDestinatari($proNum, $proTipo) {
        $DestinatariROL = array();
        $AltriDestinatari_tab = $this->proLib->GetAnades($proNum, 'codice', true, $proTipo, 'D', " AND DESCUF=''");
        foreach ($AltriDestinatari_tab as $AltriDestinatari_rec) {
            $DestinatariROL_rec = array();
            $DestinatariROL_rec['ROL_DESTINATARIO'] = $AltriDestinatari_rec['DESNOM'];
            $DestinatariROL_rec['ROL_INDIRIZZO'] = $AltriDestinatari_rec['DESIND'];
            $DestinatariROL_rec['ROL_VERIFICATO'] = 0;
            $DestinatariROL[] = $DestinatariROL_rec;
        }
        return $DestinatariROL;
    }

    public function getDocumentoPrincipale($proNum, $proTipo) {
        $documentoPrincipale = array();
        $anadoc_rec = $this->proLib->GetAnadoc($proNum, 'codice', false, $proTipo, " AND DOCSERVIZIO=0 AND DOCTIPO=''");
        if ($anadoc_rec) {
            $protPath = $this->proLib->SetDirectory($proNum, substr($proTipo, 0, 1));
            $documentoPrincipale['PATH'] = $protPath . "/" . $anadoc_rec['DOCFIL'];
            $documentoPrincipale['NAME'] = $anadoc_rec['DOCNAME'];
            return $documentoPrincipale;
        } else {
            return false;
        }
    }

    public function CheckProtocolloConservato() {
        $proLibConservazione = new proLibConservazione();
        $ProConser_rec = $proLibConservazione->CheckProtocolloVersato($this->datiProt['PRONUM'], $this->datiProt['PROPAR']);
        if ($ProConser_rec) {
            Out::removeElement($this->nameForm . '_Fascicola');
            Out::removeElement($this->nameForm . '_FileLocale');
            Out::removeElement($this->nameForm . '_Scanner');
            Out::removeElement($this->nameForm . '_Notifica');
            Out::removeElement($this->nameForm . '_NotificaMittenti');
        }
    }

}

?>
