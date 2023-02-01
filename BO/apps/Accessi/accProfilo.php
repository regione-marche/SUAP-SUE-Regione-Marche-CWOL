<?php
/* * 
 *
 * Dati profilo gestibili da utente
 *
 * PHP Version 5
 *
 * @category
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    11.09.2015
 * @link
 * @see
 * @since
 * @deprecated
 *  * */


include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLib.class.php';
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibNotifiche.class.php';
include_once ITA_BASE_PATH . '/apps/RemoteSign/rsnSigner.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiSecDiag.class.php';

function accProfilo() {
    $accProfilo = new accProfilo();
    $accProfilo->parseEvent();
    return;
}

class accProfilo extends itaModel {

    public $ITW_DB;
    public $ITALWEB_DB;
    public $nameForm = "accProfilo";
    public $divGes = "accProfilo_divGestione";
    public $accLib;
    public $praLib;
    public $envLib;
    public $currUtecod;

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->praLib = new praLib();
        $this->envLib = new envLib();
        $this->ITW_DB = $this->accLib->getITW();
        $this->ITALWEB_DB = $this->accLib->getITALWEB();
        $this->currUtecod = App::$utente->getKey($this->nameForm . '_currUtecod');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currUtecod', $this->currUtecod);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->CreaCombo();
                $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
                $this->Dettaglio($Utenti_rec['ROWID']);
                Out::setFocus('', $this->nameForm . '_RICHUT[RICCOG]');
                Out::hide($this->nameForm . '_labelSlider');
                Out::hide($this->nameForm . '_sliderNotice');
                $risultato = $this->envLib->getAttivazioneMail();
                if ($risultato == '1') {
                    Out::show($this->nameForm . '_Notifiche[NotMail]_lbl');
                    Out::show($this->nameForm . '_Notifiche[NotMail]');
                } else {
                    Out::hide($this->nameForm . '_Notifiche[NotMail]_lbl');
                    Out::hide($this->nameForm . '_Notifiche[NotMail]');
                }

                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Aggiorna':
                        $richut_rec = $_POST[$this->nameForm . '_RICHUT'];
                        if ($richut_rec['ROWID']) {
                            try {
                                $nRows = ItaDB::DBUpdate($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Aggiornamento dati Utente", $e->getMessage());
                            }
                        } else {
                            try {
                                $richut_rec['RICCOD'] = $this->currUtecod;
                                $nRows = ItaDB::DBInsert($this->ITW_DB, 'RICHUT', 'ROWID', $richut_rec);
                            } catch (Exception $e) {
                                Out::msgStop("Errore in Inserimento dati Utente", $e->getMessage());
                            }
                        }

                        //
                        //aggiunta per Firma Remota
                        //
                        $datiFirma_rec = array();
                        $datiFirma_rec['Utente'] = $_POST[$this->nameForm . '_FirmaRemota']['Utente'];
                        $datiFirma_rec['Password'] = $_POST[$this->nameForm . '_FirmaRemota']['Password'];
                        $datiFirma_rec['OtpAuth'] = $_POST[$this->nameForm . '_FirmaRemota']['OtpAuth'];
                        $firma_rec = array();
                        $firma_rec['ROWID'] = $_POST[$this->nameForm . '_FirmaRemota']['ROWID'];
                        $firma_rec['METAKEY'] = "ParmFirmaRemota";
                        $firma_rec['METAVALUE'] = serialize(array(
                            'FirmaRemota' => $datiFirma_rec
                        ));
                        if ($firma_rec['ROWID'] == '') {
                            $firma_rec['UTECOD'] = $this->currUtecod;
                            $nRows = ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $firma_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $firma_rec);
                        }
                        
                        //
                        //aggiunta max numero notifiche 
                        //
                        $datiNotifiche_rec = array();
                        $datiNotifiche_rec['MaxNumNotifiche'] = $_POST[$this->nameForm . '_Notifiche']['MaxNumNotifiche'];
                        $datiNotifiche_rec['NotMail'] = $_POST[$this->nameForm . '_Notifiche']['NotMail'];
                        $notifiche_rec = array();
                        $notifiche_rec['ROWID'] = $_POST[$this->nameForm . '_Notifiche']['ROWID'];
                        $notifiche_rec['METAKEY'] = "ParmNotifiche";
                        $notifiche_rec['METAVALUE'] = serialize(array(
                            'Notifiche' => $datiNotifiche_rec
                        ));
                        if ($notifiche_rec['ROWID'] == '') {
                            $notifiche_rec['UTECOD'] = $this->currUtecod;
                            $nRows = ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $notifiche_rec);
                        } else {
                            $nRows = ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_UTEMETA', 'ROWID', $notifiche_rec);
                        }
                        Out::msgInfo("Utente " . App::$utente->getKey('nomeUtente'), "Dati profilo Aggiornati");
                        $this->Dettaglio($_POST[$this->nameForm . '_UTENTI']['ROWID']);
                        break;
                    case $this->nameForm . '_Password':
                        utiSecDiag::GetMsgInputPassword($this->nameForm, 'Cambia Password');
                        break;
                    case $this->nameForm . '_returnPassword':
                        $ditta = App::$utente->getKey('ditta');
                        $utente = App::$utente->getKey('nomeUtente');
                        $password = $_POST[$this->nameForm . '_password'];
                        $ret_verpass = ita_verpass($ditta, $utente, $password);
                        if ($ret_verpass['status'] != 0 && $ret_verpass['status'] != '-99') {
                            Out::msgStop("Errore di validazione", "Inserire la Password Corretta!");
                            break;
                        }

                        $sql = "SELECT * FROM UTENTI WHERE UTECOD='$this->currUtecod'";
                        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                        if ($utenti_rec) {
                            $this->cambiaPassword($utenti_rec['UTECOD'], $utenti_rec['UTELOG'], 'modifica');
                        }
                        break;

                    case $this->nameForm . '_DelegheProtocollo':
                        $model = 'proDelegheIter';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        $this->close();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_currUtecod');
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show('menuapp');
    }

    public function CreaCombo() {
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "", "1", "");
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "tls", "0", "tls");
        Out::select($this->nameForm . '_RICHUT[RICSMT]', 1, "ssl", "0", "ssl");

        Out::select($this->nameForm . '_Notifiche[MaxNumNotifiche]', 1, "", "1", "Seleziona");
        for ($index = 0; $index <= envLibNotifiche::NOTIFCHE_VIEW_MAX_DEFAULT; $index++) {
            Out::select($this->nameForm . '_Notifiche[MaxNumNotifiche]', 1, "$index", "0", "$index");
        }
        
        Out::select($this->nameForm . '_FirmaRemota[OtpAuth]', 1, "", "1", "Seleziona");
        foreach (rsnSigner::$TYPES_OPT_AUTH as $type) {
            Out::select($this->nameForm . '_FirmaRemota[OtpAuth]', 1, $type, "0", $type);
        }
    }

    public function Dettaglio($indice) {
        $utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE ROWID='$indice'", false);
        $this->currUtecod = $utenti_rec['UTECOD'];
        Out::clearFields($this->nameForm, $this->divGes);
        Out::valori($utenti_rec, $this->nameForm . '_UTENTI');
        if ($utenti_rec['UTEPAS'] != '') {
            Out::valore($this->nameForm . '_Utepas', 'Password Presente');
            if ($utenti_rec['UTESPA'] <= date("Ymd")) {
                Out::valore($this->nameForm . '_Utepas', 'Password Scaduta');
            }
        } else {
            Out::valore($this->nameForm . '_Utepas', 'Password NON Presente');
        }

        $firmaRec_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmFirmaRemota');
        if ($firmaRec_rec) {
            $meta = unserialize($firmaRec_rec['METAVALUE']);
            Out::valore($this->nameForm . '_FirmaRemota[ROWID]', $firmaRec_rec['ROWID']);
            Out::valori($meta['FirmaRemota'], $this->nameForm . '_FirmaRemota');
        }
        // Notifiche
        $paramNotifiche_rec = $this->accLib->GetEnv_Utemeta($utenti_rec['UTECOD'], 'codice', 'ParmNotifiche');
        if ($paramNotifiche_rec) {
            $meta = unserialize($paramNotifiche_rec['METAVALUE']);
            Out::valore($this->nameForm . '_Notifiche[ROWID]', $paramNotifiche_rec['ROWID']);
            Out::valori($meta['Notifiche'], $this->nameForm . '_Notifiche');
        }

        $Ananom_rec = $this->praLib->GetAnanom($utenti_rec['UTEANA__3'], 'codice');
        Out::valore($this->nameForm . '_DecodDipendente', $Ananom_rec['NOMCOG'] . " " . $Ananom_rec['NOMNOM']);
        //CERCO SU RICHUT
        $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
        if ($richut_rec) {
            Out::valori($richut_rec, $this->nameForm . '_RICHUT');
        }
        // Se utente protocollo abilito deleghe iter:
        Out::hide($this->nameForm . '_DelegheProtocollo');
        if ($utenti_rec['UTEANA__1']) {
            Out::show($this->nameForm . '_DelegheProtocollo');
        }

        if ($utenti_rec['UTEDPA'] == '9999') {
            Out::valore($this->nameForm . '_UTENTI[UTEDPA]', '');
        }
    }

    function cambiaPassword($codiceUtente, $nomeUtente, $modo) {
        $model = 'accPassword';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['modo'] = $modo;
        $_POST['UTECOD'] = $codiceUtente;
        $_POST['UTELOG'] = $nomeUtente;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

//    public static function leggiParametri_mail() {
//        include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
//        $result = array();
//        $devLib = new devLib();
//        $env_config_rec = $devLib->getEnv_config('ENVNOTIFICHE', 'codice', 'ATTIVA_MAIL', false);
//        $attivamail = $env_config_rec['CONFIG'];
//        return $attivamail;
//    }
}
?>
