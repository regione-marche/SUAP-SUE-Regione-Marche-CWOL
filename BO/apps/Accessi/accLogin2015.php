<?php

/**
 *
 * FORM LOGIN ADVANCED
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Accessi
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    19.01.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibCohesion.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibMaggioliSPID.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibFedera.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLogin.php';

function accLogin2015() {
    $accLogin2015 = new accLogin2015();
    $accLogin2015->parseEvent();
    return;
}

class accLogin2015 extends accLogin {

    public $nameForm = "accLogin2015";
    private $accLib;
    private $accLibCohesion;
    private $accLibMaggioliSPID;
    private $accLibFedera;

    function __construct() {
        parent::__construct();
        try {
            $this->private = false;
            $this->accLib = new accLib();
            $this->accLibCohesion = new accLibCohesion();
            $this->accLibMaggioliSPID = new accLibMaggioliSPID();
            $this->accLibFedera = new accLibFedera();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $titleContent = '';
//                $titleContent = '<h1 style="margin-bottom: 35px;">itaEngine !</h1>';
                if (file_exists($this->accLib->getResourcePath('divTitle.html'))) {
                    $newTitleContent = trim(file_get_contents($this->accLib->getResourcePath('divTitle.html')));
                    if ($newTitleContent) {
                        $titleContent = $newTitleContent;
                    }
                }

                $registerFuncts = App::getConf('security.registerLink');
                $lostPwdFuncts = App::getConf('security.pwdLostLink');
                if (App::getConf('security.ldap')) {
                    Out::html($this->nameForm . '_utente_lbl', '<span style="display: inline-block; padding: .2em .5em; border-radius: 3px; background-color: #337ab7; color: #fff; font-weight: bold; margin: 0 .2em .3em 0;">LDAP</span> Nome Utente');
                    $registerFuncts = false;
                    $lostPwdFuncts = false;
                }

                Out::html($this->nameForm . '_divTitle', $titleContent);
                Out::html($this->nameForm . '_registerText', 'Non sei registrato ?');
                Out::html($this->nameForm . '_registerLink', 'Registrati ora!');
                Out::html($this->nameForm . '_passwordLost', 'Hai dimenticato la password?');                
                
                if (!$registerFuncts ) {
                    Out::hide($this->nameForm . '_registerText');
                    Out::hide($this->nameForm . '_registerLink');
                }
                if (!$lostPwdFuncts ) {
                    Out::hide($this->nameForm . '_passwordLost'); 
                }

                Out::setFocus('', $this->nameForm . "_utente");

                /*
                 * Accesso da service provider
                 */

                if (App::getConf('security.sso-cohesion')) {
                    Out::html($this->nameForm . '_IDP', $this->accLibCohesion->getButtonHtml($this->nameForm), 'append');
                }

                if (App::getConf('security.sso-spid-maggioli')) {
                    if ($this->accLibMaggioliSPID->getSessionToken()) {
                        Out::codice("itaGo( 'ItaCall', '', { asyncCall: true, bloccaui: false, event: 'serviceProvider', model: '{$this->nameForm}', idp: 'spidm', ditta: '{$_POST['ditta']}' } );");
                    }

                    Out::html($this->nameForm . '_IDP', $this->accLibMaggioliSPID->getButtonHtml($this->nameForm), 'append');
                }

                if (App::getConf('security.sso-federa')) {
                    Out::html($this->nameForm . '_IDP', $this->accLibFedera->getButtonHtml($this->nameForm), 'append');
                }
                break;

            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_registerLink':
                        $cod_ente = $_POST[$this->nameForm . '_ditta'];

                        if (!$cod_ente) {
                            Out::msgInfo("Registrazione", "Inserire l'ente per proseguire", "auto", "auto", "");
                            break;
                        }
                        $model = "accRegistra";
                        itaLib::openDialog($model, '', true, '');
                        $formObj = itaModel::getInstance($model);
                        if (!$formObj) {
                            Out::msgStop("Errore", "Apertura fallita");
                            break;
                        }
                        $formObj->setReturnModel($this->nameForm);
                        $formObj->setReturnEvent('return' . ucfirst($model));
                        $formObj->setDitta($cod_ente);
                        $formObj->setEvent('openform');
                        $formObj->parseEvent();
                        break;

                    case $this->nameForm . '_passwordLost':
                        $utente = $_POST[$this->nameForm . '_utente'];
                        $cod_ente = $_POST[$this->nameForm . '_ditta'];

                        if (!$utente || !$cod_ente) {
                            Out::msgStop("Recupero Password", "Inserire il nome utente ed l'ente per proseguire", "auto", "auto", "");
                            break;
                        }

//                        try {
                        $ITW_DB = ItaDB::DBOpen('ITW', $cod_ente);
                        $utenti_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTELOG='" . addslashes($utente) . "'", false);
//                        } catch (Exception $e) {
//                            Out::msgStop("Errore", $e->getMessage());
//                            break;
//                        }
                        if (!$utenti_rec) {
                            Out::msgStop("Errore", "Utente non valido");
                            break;
                        }
                        foreach (App::getEnti() as $ente => $value)
                            if ($value['codice'] == $cod_ente)
                                break;
                        Out::msgQuestion("Recupero Password", "<br>Si sta tentando di recuperare la password dell'utente <b>$utente</b> per l'ente <b>$ente</b>.<br>Proseguendo con il recupero password verrà contattato l'amministratore dell'ente selezionato che provvederà a reimpostare la password per l'utente selezionato.", array(
                            'F8-Annulla' => array('id' => $this->nameForm . '_annullaRec', 'model' => $this->nameForm, 'shortCut' => "f8"),
                            'F5-Prosegui' => array('id' => $this->nameForm . '_proseguiRec', 'model' => $this->nameForm, 'shortCut' => "f5")
                        ));
                        break;

                    case $this->nameForm . '_proseguiRec':
                        $utente = $_POST[$this->nameForm . '_utente'];
                        $cod_ente = $_POST[$this->nameForm . '_ditta'];

                        if (!$utente || !$cod_ente) {
                            Out::msgStop("Recupero Password", "Inserire il nome utente e l'ente per proseguire", "auto", "auto", "");
                            break;
                        }

                        try {
                            $ITW_DB = ItaDB::DBOpen('ITW', $cod_ente);
                            $richut_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT RICHUT.* FROM UTENTI LEFT OUTER JOIN RICHUT ON UTENTI.UTECOD = RICHUT.RICCOD WHERE UTELOG='" . addslashes($utente) . "'", false);
                        } catch (Exception $e) {
                            Out::msgStop("Errore", $e->getMessage());
                            break;
                        }

                        if (!$richut_rec) {
                            Out::msgStop("Errore", "Utente non valido");
                            break;
                        }

                        if (in_array($richut_rec['RICSTA'], array(accLib::RICHIESTA_NUOVA, accLib::RICHIESTA_RESET_PASSWORD))) {
                            Out::msgStop("Errore", "\nImpossibile reimpostare la password per l'utente selezionato.\nUna richiesta è già in corso.");
                            break;
                        }

                        $richut_rec['RICSTA'] = accLib::RICHIESTA_RESET_PASSWORD;
                        $richut_rec['RICDAT'] = date('Ymd');
                        $richut_rec['RICTIM'] = date('H:i:s');

                        if (!ItaDB::DBUpdate($ITW_DB, 'RICHUT', 'ROWID', $richut_rec)) {
                            Out::msgStop("Errore", "Errore nell'impostare la richiesta");
                            break;
                        }

                        $this->accLib->inviaMailRichiestaUtente($richut_rec, $cod_ente);

                        Out::msgInfo("", "La segnalazione è stata inoltrata e verrà presa in carico dall'amministratore.", "auto", "auto", "");
                        break;

                    case $this->nameForm . '_Cohesion':
                        $codiceEnte = $_POST[$this->nameForm . '_ditta'];

                        if (!$codiceEnte) {
                            Out::msgStop('Accesso', 'Inserire l\'ente per proseguire', 'auto', 'auto', '');
                            break;
                        }

                        Out::codice(sprintf('window.location.href = "%s";', $this->accLibCohesion->getLoginURI($codiceEnte)));

//                        $this->accLibCohesion->login($codiceEnte);
                        break;

                    case $this->nameForm . '_SPID':
                        $codiceEnte = $_POST[$this->nameForm . '_ditta'];

                        if (!$codiceEnte) {
                            Out::msgStop('Accesso', 'Inserire l\'ente per proseguire', 'auto', 'auto', '');
                            break;
                        }

                        Out::codice(sprintf('window.location.href = "%s";', $this->accLibMaggioliSPID->getLoginURI($_POST['idp'], $codiceEnte)));
                        break;

                    case $this->nameForm . '_Federa':
                        $codiceEnte = $_POST[$this->nameForm . '_ditta'];

                        if (!$codiceEnte) {
                            Out::msgStop('Accesso', 'Inserire l\'ente per proseguire', 'auto', 'auto', '');
                            break;
                        }

                        Out::codice(sprintf('window.location.href = "%s";', $this->accLibFedera->getLoginURI($codiceEnte)));
                        break;
                }

            case 'serviceProvider':
                switch ($_POST['idp']) {
                    case 'spidm':
                        if (!$this->accLibMaggioliSPID->login($_POST['ditta'])) {
                            Out::msgStop('Errore', 'Errore durante l\'accesso tramite SPID: ' . $this->accLibMaggioliSPID->getErrMessage());
                            break;
                        }
                        break;
                }
                break;
        }
    }

}
