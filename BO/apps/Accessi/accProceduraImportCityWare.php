<?php

/**
 *
 * Procedura di importazione utenti CityWare
 *
 * PHP Version 5
 *
 * @category   itaModel
 * @package    Accessi
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu> 
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    25.01.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Accessi/accLibCityWare.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR_UTENTI.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

function accProceduraImportCityWare() {
    $accProceduraImportCityWare = new accProceduraImportCityWare();
    $accProceduraImportCityWare->parseEvent();
    return;
}

class accProceduraImportCityWare extends itaModel {

    public $nameForm = "accProceduraImportCityWare";
    private $accLibCityWare;
    private $accLib;
    private $libBorUtenti;
    private $specialUsernames = array('admin', 'italsoft');

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->accLibCityWare = new accLibCityWare();
        $this->libBorUtenti = new cwbLibDB_BOR_UTENTI();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
                $this->htmlLog();

                $resultControllo = $this->effettuaControlloDuplicati();

                if (!$this->accLib->isSSOCitywareEnabled()) {
                    Out::hide($this->nameForm . '_Sincronizza');
//                    Out::msgStop("Errore", "Il parametro 'security.sso' non è configurato per avviare la procedura.");
//                    $this->close();
//                    break;
                }

                Out::valore($this->nameForm . '_SecurePassword', App::getConf('security.secure-password') ?: 'none');
                Out::valore($this->nameForm . '_UTENTI[UTEDPA]', '180');
                Out::valore($this->nameForm . '_UTENTI[UTEFIL__1]', '3');
                Out::valore($this->nameForm . '_UTENTI[UTEFIL__2]', '30');

                Out::hide($this->nameForm . '_divValiditaPassword');
                Out::required($this->nameForm . '_UTENTI[UTEDPA]', false, false);

                if ($resultControllo) {
                    $this->setInsertUserCase();
                }
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_UTENTI[UTEGRU]':
                        $this->decodGruppo();
                        break;
                }
                break;

            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RESETPASS':
                        if (!$_POST[$_POST['id']]) {
                            Out::hide($this->nameForm . '_divValiditaPassword');
                            Out::required($this->nameForm . '_UTENTI[UTEDPA]', false, false);
                        } else {
                            Out::show($this->nameForm . '_divValiditaPassword');
                            Out::required($this->nameForm . '_UTENTI[UTEDPA]');
                        }

                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Controlla':
                        $this->htmlLog();

                        if (!$this->effettuaControlloDuplicati()) {
                            break;
                        }

                        if (!$this->libBorUtenti->getCitywareDB()) {
                            $this->htmlLog('Nessuna connessione al DB CityWare disponibile.', true);
                            break;
                        }

                        $this->getUtentiDiff();
                        break;

                    case $this->nameForm . '_Sincronizza':
                        if (!$this->effettuaControlloDuplicati()) {
                            break;
                        }

                        $this->htmlLog();

                        if (!$this->decodGruppo()) {
                            $this->htmlLog('Gruppo non valido.', true);
                            break;
                        }

                        if (!$this->libBorUtenti->getCitywareDB()) {
                            $this->htmlLog('Nessuna connessione al DB CityWare disponibile.', true);
                            break;
                        }

                        $utenti_rec = $_POST[$this->nameForm . '_UTENTI'];
                        $resetPassword = $_POST[$this->nameForm . '_PWDRESET'];
                        $resetPasswordDate = $_POST[$this->nameForm . '_RESETPASS'];

                        $resultUtentiDiff = $this->getUtentiDiff();
                        $UtentiNEW = $resultUtentiDiff['UtentiNEW'];
                        $UtentiUPD = $resultUtentiDiff['UtentiUPD'];

                        /*
                         * Inserimento
                         */

                        $Utenti_rec_max = ItaDB::DBSQLSelect($this->accLibCityWare->getITW(), "SELECT MAX(UTECOD) AS ULTIMO FROM UTENTI", false);
                        $UTECOD = $Utenti_rec_max ? intval($Utenti_rec_max['ULTIMO']) + 1 : 1;

                        foreach ($UtentiNEW as $CodiceUtente) {
                            if (!$this->inserisciUtenteCW($CodiceUtente, $UTECOD, $utenti_rec, $resetPasswordDate)) {
                                return false;
                            }

                            $UTECOD++;
                        }

                        /*
                         * Aggiornamento
                         * 
                         * L'AGGIORNAMENTO NON VIENE EFFETTUATO IN QUANTO TUTTE LE MODIFICHE EFFETTUATE SULL'UTENTE
                         * ITAENGINE A SEGUITO DELL'IMPORTAZIONE INIZIALE VENGONO GIA' RIFLESSE SULL'UTENTE CW.
                         */

                        $utentiCWPasswordUpdate = array();

                        foreach ($UtentiUPD as $CodiceUtente) {
                            if (!$this->aggiornaUtenteCW($CodiceUtente, $utenti_rec, $resetPassword, $resetPasswordDate, $utentiCWPasswordUpdate)) {
                                return false;
                            }
                        }

                        if (count($utentiCWPasswordUpdate)) {
                            $passwordSource = $resetPassword ? 'Cityware' : 'Cityware Online';
                            Out::msgInfo('Aggiornamento password', "Il CSV scaricato contiene la lista degli utenti a cui è stata modificata<br>la password con quella impostata su $passwordSource.");

                            $csvUtenti = '';
                            foreach ($utentiCWPasswordUpdate as $UtenteIE_rec) {
                                $csvUtenti .= "\"{$UtenteIE_rec['UTENTI']['UTELOG']}\";\"{$UtenteIE_rec['RICHUT']['RICNOM']}\";\"{$UtenteIE_rec['RICHUT']['RICCOG']}\"\n";
                            }

                            $csvTempPath = itaLib::createAppsTempPath() . '/' . uniqid() . '.csv';
                            file_put_contents($csvTempPath, $csvUtenti);
                            Out::openDocument(utiDownload::getUrl('Utenti CWOL.csv', $csvTempPath));
                        }

                        $this->htmlLog('Sincronizzazione completata.');
                        break;

                    case $this->nameForm . '_ReimpostaPassword':
                        $UtentiIE_tab = ItaDB::DBSQLSelect($this->accLibCityWare->getITW(), "SELECT * FROM UTENTI WHERE UTESPA < " . date('Ymd') . " ORDER BY UTELOG");

                        $this->htmlLog('Inizio reset della password per ' . count($UtentiIE_tab) . ' utenti.');

                        foreach ($UtentiIE_tab as $UtentiIE_rec) {
                            $CodiceUtente = strtoupper($UtentiIE_rec['UTELOG']);
                            $UtenteCW_rec = $this->libBorUtenti->leggiUtenti($CodiceUtente);
                            $UtenteCW_rec = reset($UtenteCW_rec);

                            $encryptedPassword = $this->accLib->getEncryptedPassword($UtenteCW_rec['BOR_UTENTI']['PWDUTE']);
                            if ($encryptedPassword === false) {
                                $this->htmlLog('Errore getEncryptedPassword: ' . $this->accLib->getErrMessage(), true);
                                break 2;
                            }

                            $UtentiIE_rec['UTEPAS'] = $encryptedPassword;
                            $UtentiIE_rec['UTEDPA'] = '9999';
                            $UtentiIE_rec['UTEUPA'] = date('Ymd');

                            $dataScadenza = new DateTime();
                            $dataScadenza->add(new DateInterval('P' . $UtentiIE_rec['UTEDPA'] . 'D'));

                            $UtentiIE_rec['UTESPA'] = $dataScadenza->format('Ymd');

                            try {
                                ItaDB::DBUpdate($this->accLibCityWare->getITW(), 'UTENTI', 'ROWID', $UtentiIE_rec);
                            } catch (Exception $e) {
                                $this->htmlLog("Errore durante l'aggiornamento dell'utente '{$UtenteIE_rec['UTELOG']}' su UTENTI. " . $e->getMessage(), true);
                                break 2;
                            }

                            $this->htmlLog("Reset della password per utente '{$UtenteIE_rec['UTELOG']}' effettuato.");
                        }
                        break;

                    case $this->nameForm . '_UTENTI[UTEGRU]_butt':
                        accRic::accRicGru($this->nameForm);
                        break;
                }
                break;


            case 'returngru':
                try {
                    $Anagru_tab = ItaDB::DBSQLSelect($this->accLibCityWare->getITW(), "SELECT GRUCOD, GRUDES FROM GRUPPI WHERE ROWID = '" . $_POST['retKey'] . "'");

                    if (count($Anagru_tab) == 1) {
                        Out::valore($this->nameForm . '_UTENTI[UTEGRU]', $Anagru_tab[0]['GRUCOD']);
                        Out::valore($this->nameForm . '_DESGRU1', $Anagru_tab[0]['GRUDES']);
                    }
                } catch (Exception $e) {
                    Out::msgStop("Errore di Connessione al DB", $e->getMessage());
                }
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function htmlLog($message = false, $error = false) {
        if ($message === false) {
            Out::html($this->nameForm . '_divLog', '');
            return false;
        }

        Out::html($this->nameForm . '_divLog', '[' . date('H:i:s') . "] $message<br />", 'append');

        if ($error) {
            Out::msgStop("Errore", $message);
        }
    }

    public function decodGruppo() {
        $codice = $_POST[$this->nameForm . '_UTENTI']['UTEGRU'];
        if ($codice != '') {
            $Anagru_tab = ItaDB::DBSQLSelect($this->accLibCityWare->getITW(), "SELECT GRUCOD, GRUDES FROM GRUPPI WHERE GRUCOD = '$codice'");
            if (count($Anagru_tab) == 1) {
                Out::valore($this->nameForm . '_DESGRU1', $Anagru_tab[0]['GRUDES']);
                return true;
            } else {
                Out::valore($_POST['id'], '');
                Out::valore($this->nameForm . '_DESGRU1', 'Gruppo non valido');
                Out::setFocus('', $_POST['id']);
            }
        } else {
            Out::valore($this->nameForm . '_DESGRU1', '');
        }

        return false;
    }

    /**
     * Ritorna un array con gli utenti duplicati.
     * 
     * @return array Ritorna l'array con gli utenti duplicati (array vuoto
     * se non ci sono duplicati), dove la chiave è l'UTELOG in maiuscolo ed
     * il valore il numero di utenti con lo stesso UTELOG. Es.
     * array(
     *     'TIZIO' => 2, // es. TIZIO, tizio
     *     'CAIO' => 3 // es. CAIO, CaIo, caio
     * )
     */
    public function getArrayDuplicati() {
        $utentiDuplicati = array();

        $sql = "SELECT " . $this->accLib->getITW()->strUpper("UTELOG") . " AS UPPERLOG, COUNT(*) AS COUNT FROM UTENTI GROUP BY UPPERLOG";
        $utenti_tab = ItaDB::DBSQLSelect($this->accLib->getITW(), $sql);

        foreach ($utenti_tab as $utenti_rec) {
            if ($utenti_rec['COUNT'] > 1) {
                $utentiDuplicati[$utenti_rec['UPPERLOG']] = $utenti_rec['COUNT'];
            }
        }

        return $utentiDuplicati;
    }

    /**
     * Controlla la presenza di utenti duplicati in itaEngine.
     * 
     * @return boolean True se non ci sono duplicati, false viceversa.
     */
    public function effettuaControlloDuplicati() {
        $utentiDuplicati = $this->getArrayDuplicati();

        if (count($utentiDuplicati) > 0) {
            Out::hide($this->nameForm . '_Controlla');
            Out::hide($this->nameForm . '_Sincronizza');
            Out::block($this->nameForm . '_divDatiDefault');

            $this->htmlLog(sprintf('<b>ATTENZIONE</b>: sono stati trovati %d utenti con più record (duplicati).', count($utentiDuplicati)));

            foreach ($utentiDuplicati as $utenteDuplicato => $countDuplicato) {
                $this->htmlLog(sprintf('Ci sono <i>%d</i> utenti con logname <b>%s</b>.', $countDuplicato, $utenteDuplicato));
            }

            $this->htmlLog('<span style="color: red;"><b><i>IMPOSSIBILE PROSEGUIRE CON LA SINCRONIZZAZIONE.</i></b></span>');

            return false;
        }

        return true;
    }

    /**
     * Imposta il case da utilizzare in fase di inserimento a seconda
     * del parametro da config.ini e della spunta presente nell'interfaccia.
     * 
     * @return null
     */
    public function setInsertUserCase() {
        $configCase = strtoupper(App::getConf('security.insert-user-case'));

        switch ($configCase) {
            case 'UPPER':
                $importMessage = 'gli utenti saranno importanti in <b>MAIUSCOLO</b> come da <i>config.ini</i> (security.insert-user-case).';

                Out::valore($this->nameForm . '_LOWERCASE', '0');
                Out::disableField($this->nameForm . '_LOWERCASE');
                Out::setLabel($this->nameForm . '_LOWERCASE', 'importa gli utenti in minuscolo --- ' . $importMessage);

                $this->htmlLog(ucfirst($importMessage));
                return $configCase;

            case 'LOWER':
                $importMessage = 'gli utenti saranno importanti in <b>minuscolo</b> come da <i>config.ini</i> (security.insert-user-case).';

                Out::valore($this->nameForm . '_LOWERCASE', '1');
                Out::disableField($this->nameForm . '_LOWERCASE');
                Out::setLabel($this->nameForm . '_LOWERCASE', 'importa gli utenti in minuscolo --- ' . $importMessage);

                $this->htmlLog(ucfirst($importMessage));
                return $configCase;

            default:
                Out::enableField($this->nameForm . '_LOWERCASE');
                return $_POST[$this->nameForm . '_LOWERCASE'] == 1 ? 'LOWER' : 'UPPER';
        }
    }

    public function getUtentiDiff() {
        $importingCase = $this->setInsertUserCase();

        $UtentiCW_tab = $this->libBorUtenti->leggiIndiceUtenti();
        $UtentiCW = array_map('trim', array_column($UtentiCW_tab, 'CODUTE'));

        $this->htmlLog('Letti ' . count($UtentiCW) . ' utenti CityWare...');

        $UtentiIE_tab = ItaDB::DBSQLSelect($this->accLibCityWare->getITW(), "SELECT UTECOD, UTELOG FROM UTENTI ORDER BY UTELOG");
        $UtentiIE = array_map('trim', array_column($UtentiIE_tab, 'UTELOG'));

        switch (strtolower($importingCase)) {
            case 'lower':
                $UtentiCW = array_map('strtolower', $UtentiCW);
                $UtentiIE = array_map('strtolower', $UtentiIE);
                break;

            case 'upper':
                $UtentiCW = array_map('strtoupper', $UtentiCW);
                $UtentiIE = array_map('strtoupper', $UtentiIE);
                break;
        }

        /*
         * Record da elaborare
         */

        $UtentiNEW = array_diff($UtentiCW, $UtentiIE);
        $UtentiUPD = array_intersect($UtentiCW, $UtentiIE);

        $this->htmlLog('Trovati ' . count($UtentiNEW) . ' utenti da inserire...');
        $this->htmlLog(implode(', ', $UtentiNEW));

        $this->htmlLog('Trovati ' . count($UtentiUPD) . ' utenti da aggiornare...');
        $this->htmlLog(implode(', ', $UtentiUPD));

        foreach ($UtentiNEW as $CodiceUtente) {
            if (in_array(strtolower($CodiceUtente), $this->specialUsernames)) {
                $this->htmlLog('<b>ATTENZIONE</b>: si sta sincronizzando l\'utente <b>' . $CodiceUtente . '</b>.');
            }
        }

        foreach ($UtentiUPD as $CodiceUtente) {
            if (in_array(strtolower($CodiceUtente), $this->specialUsernames)) {
                $this->htmlLog('<b>ATTENZIONE</b>: si sta sincronizzando l\'utente <b>' . $CodiceUtente . '</b>.');
            }
        }

        return array(
            'UtentiNEW' => $UtentiNEW,
            'UtentiUPD' => $UtentiUPD
        );
    }

    public function inserisciUtenteCW($CodiceUtente, $UTECOD, $utenti_rec, $resetPasswordDate) {
        $this->htmlLog("Inserimento utente $CodiceUtente...");
        $UtenteCW_rec = $this->libBorUtenti->leggiUtenti($CodiceUtente);
        $UtenteCW_rec = reset($UtenteCW_rec);

        $importingCase = strtolower($this->setInsertUserCase());
        $UtenteIE_rec = $this->accLibCityWare->convertUserCW2IE($UtenteCW_rec, $UTECOD, $importingCase);

        $UtenteIE_rec['UTENTI']['UTEFIL__1'] = $utenti_rec['UTEFIL__1'];
        $UtenteIE_rec['UTENTI']['UTEFIL__2'] = $utenti_rec['UTEFIL__2'];
        $UtenteIE_rec['UTENTI']['UTEGRU'] = $utenti_rec['UTEGRU'];

        /*
         * Calcolo data scadenza password.
         */

        if ($resetPasswordDate) {
            if (!$UtenteIE_rec['UTENTI']['UTEDPA']) {
                $UtenteIE_rec['UTENTI']['UTEDPA'] = $utenti_rec['UTEDPA'];
            }

            $dataScadenza = new DateTime();
            $dataScadenza->add(new DateInterval('P' . $UtenteIE_rec['UTENTI']['UTEDPA'] . 'D'));

            $UtenteIE_rec['UTENTI']['UTESPA'] = $dataScadenza->format('Ymd');
        } else {
            if (!$UtenteIE_rec['UTENTI']['UTEDPA']) {
                $UtenteIE_rec['UTENTI']['UTEDPA'] = '9999';
            }

            /*
             * Caso in cui non sia mai stato effettuato l'accesso in CW
             */
            if (!$UtenteIE_rec['UTENTI']['UTEUPA']) {
                $UtenteIE_rec['UTENTI']['UTEUPA'] = date('Ymd');
            }

            $dataScadenza = new DateTime(substr($UtenteIE_rec['UTENTI']['UTEUPA'], 6, 2) . '-' . substr($UtenteIE_rec['UTENTI']['UTEUPA'], 4, 2) . '-' . substr($UtenteIE_rec['UTENTI']['UTEUPA'], 0, 4));
            $dataScadenza->add(new DateInterval('P' . $UtenteIE_rec['UTENTI']['UTEDPA'] . 'D'));

            $UtenteIE_rec['UTENTI']['UTESPA'] = $dataScadenza->format('Ymd');
        }

        try {
            if (!ItaDB::DBInsert($this->accLibCityWare->getITW(), 'UTENTI', 'ROWID', $UtenteIE_rec['UTENTI'])) {
                $this->htmlLog("Errore durante l'inserimento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su UTENTI.", true);
                return false;
            }

            if (!ItaDB::DBInsert($this->accLibCityWare->getITW(), 'RICHUT', 'ROWID', $UtenteIE_rec['RICHUT'])) {
                $this->htmlLog("Errore durante l'inserimento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su RICHUT.", true);
                return false;
            }

            foreach ($UtenteIE_rec['ENV_UTEMETA'] as $Env_utemeta_rec) {
                if (!ItaDB::DBInsert($this->accLibCityWare->getITALWEB(), 'ENV_UTEMETA', 'ROWID', $Env_utemeta_rec)) {
                    $this->htmlLog("Errore durante l'inserimento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su ENV_UTEMETA.", true);
                    return false;
                }
            }
            $this->htmlLog("Inserito utente: {$UtenteIE_rec['UTENTI']['UTELOG']}", false);
        } catch (ItaSecuredException $e) {
            $this->htmlLog($e->getSecuredMessage(), true);
            return false;
        } catch (Exception $e) {
            $this->htmlLog($e->getMessage(), true);
            return false;
        }

        return true;
    }

    public function aggiornaUtenteCW($CodiceUtente, $utenti_rec, $resetPassword, $resetPasswordDate, &$utentiCWPasswordUpdate) {
        $this->htmlLog("Aggiornamento utente $CodiceUtente...");

        $UtenteCW_rec = $this->libBorUtenti->leggiUtenti($CodiceUtente);
        $UtenteCW_rec = reset($UtenteCW_rec);

        /*
         * Riprendo l'UTECOD cercando l'utente UTENTI in modo case-insensitive.
         */

        $sql = "SELECT UTECOD FROM UTENTI WHERE " . $this->accLib->getITW()->strUpper("UTELOG") . " = " . $this->accLib->getITW()->strUpper("'" . addslashes($CodiceUtente) . "'");
        $utecod_rec = ItaDB::DBSQLSelect($this->accLib->getITW(), $sql, false);
        if (!$utecod_rec) {
            $this->htmlLog(sprintf('Non è stato trovato nessun record UTENTI con UTELOG %s.', $CodiceUtente));
        }

        $UTECOD = $utecod_rec['UTECOD'];

        $importingCase = strtolower($this->setInsertUserCase());
        $UtenteIE_import_rec = $this->accLibCityWare->convertUserCW2IE($UtenteCW_rec, false, $importingCase);

        try {
            $UtenteIE_rec = array();
            $UtenteIE_rec['UTENTI'] = $this->accLib->GetUtenti($UTECOD);
            $UtenteIE_rec['RICHUT'] = $this->accLib->GetRichut($UTECOD);

            if (!$UtenteIE_rec['UTENTI']) {
                $this->htmlLog("Errore durante la lettura dell'utente da aggiornare: {$UtenteIE_rec['UTENTI']['UTELOG']} su UTENTI.", true);
                return false;
            }
            $UtenteIE_rec['UTENTI']['UTEDATAULUSO'] = (!$UtenteIE_rec['UTENTI']['UTEDATAULUSO']) ? $UtenteIE_import_rec['UTENTI']['UTEDATAULUSO'] : $UtenteIE_rec['UTENTI']['UTEDATAULUSO'];
            $UtenteIE_rec['UTENTI']['DATAINIZ'] = (!$UtenteIE_rec['UTENTI']['DATAINIZ']) ? $UtenteIE_import_rec['UTENTI']['DATAINIZ'] : $UtenteIE_rec['UTENTI']['DATAINIZ'];
            $UtenteIE_rec['UTENTI']['DATAFINE'] = (!$UtenteIE_rec['UTENTI']['DATAFINE']) ? $UtenteIE_import_rec['UTENTI']['DATAFINE'] : $UtenteIE_rec['UTENTI']['DATAFINE'];
            $UtenteIE_rec['UTENTI']['UTEFLADMIN'] = (!$UtenteIE_rec['UTENTI']['UTEFLADMIN']) ? $UtenteIE_import_rec['UTENTI']['UTEFLADMIN'] : $UtenteIE_rec['UTENTI']['UTEFLADMIN'];
            $UtenteIE_rec['UTENTI']['UTELDAP'] = (!$UtenteIE_rec['UTENTI']['UTELDAP']) ? $UtenteIE_import_rec['UTENTI']['UTELDAP'] : $UtenteIE_rec['UTENTI']['UTELDAP'];

            if (
                ($UtenteIE_import_rec['UTENTI']['UTEDPA'] && $UtenteIE_rec['UTENTI']['UTEDPA'] != $UtenteIE_import_rec['UTENTI']['UTEDPA']) ||
                (!$UtenteIE_import_rec['UTENTI']['UTEDPA'] && $UtenteIE_rec['UTENTI']['UTEDPA'] != '9999') ||
                ($UtenteIE_rec['UTENTI']['UTEUPA'] != $UtenteIE_import_rec['UTENTI']['UTEUPA'])
            ) {
                /*
                 * Reimposto la data scadenza password utilizzando i valori presenti in Cityware.
                 */

                $UtenteIE_rec['UTENTI']['UTEDPA'] = $UtenteIE_import_rec['UTENTI']['UTEDPA'];
                $UtenteIE_rec['UTENTI']['UTEUPA'] = $UtenteIE_import_rec['UTENTI']['UTEUPA'];

                if (!$UtenteIE_rec['UTENTI']['UTEDPA']) {
                    $UtenteIE_rec['UTENTI']['UTEDPA'] = $resetPasswordDate ? $utenti_rec['UTEDPA'] : '9999';
                }

                if ($resetPasswordDate || !$UtenteIE_rec['UTENTI']['UTEUPA']) {
                    $UtenteIE_rec['UTENTI']['UTEUPA'] = date('Ymd');
                }

                $dataScadenza = new DateTime(substr($UtenteIE_rec['UTENTI']['UTEUPA'], 6, 2) . '-' . substr($UtenteIE_rec['UTENTI']['UTEUPA'], 4, 2) . '-' . substr($UtenteIE_rec['UTENTI']['UTEUPA'], 0, 4));
                $dataScadenza->add(new DateInterval('P' . $UtenteIE_rec['UTENTI']['UTEDPA'] . 'D'));
                $UtenteIE_rec['UTENTI']['UTESPA'] = $dataScadenza->format('Ymd');
            }

            if (!$UtenteIE_rec['UTENTI']['UTEFIS']) {
                /*
                 * Se non è presente il codice fiscale, prendo quello CW se presente
                 */
                if ($UtenteIE_import_rec['UTENTI']['UTEFIS']) {
                    $this->htmlLog('Nuovo codice fiscale (' . $UtenteIE_import_rec['UTENTI']['UTEFIS'] . ')');
                    $UtenteIE_rec['UTENTI']['UTEFIS'] = $UtenteIE_import_rec['UTENTI']['UTEFIS'];
                }
            } else {
                /*
                 * Se presente e non è lo stesso, segnalo l'anomalia
                 */
                if ($UtenteIE_import_rec['UTENTI']['UTEFIS'] != $UtenteIE_rec['UTENTI']['UTEFIS']) {
                    $this->htmlLog('ATTENZIONE! Il codice fiscale non coincide:<br>' .
                        'Utente itaEngine: ' . $UtenteIE_rec['UTENTI']['UTEFIS'] . '<br>' .
                        'Utente Cityware: ' . $UtenteIE_import_rec['UTENTI']['UTEFIS']);
                }
            }

            if ($UtenteIE_rec['UTENTI']['UTEPAS'] != $UtenteIE_import_rec['UTENTI']['UTEPAS']) {
                if ($resetPassword) {
                    $encryptedPassword = $this->accLib->getEncryptedPassword($UtenteCW_rec['BOR_UTENTI']['PWDUTE']);
                    if ($encryptedPassword === false) {
                        $this->htmlLog('Errore getEncryptedPassword: ' . $this->accLib->getErrMessage(), true);
                        return false;
                    }

                    $UtenteIE_rec['UTENTI']['UTEPAS'] = $encryptedPassword;

                    $this->htmlLog('Allineata password CWOL con quella presente su CW.');

                    $this->eqAudit->logEqEvent($this, array(
                        'Operazione' => eqAudit::OP_UPD_RECORD,
                        'Estremi' => 'Allineamento password utente CW => CWOL',
                        'Key' => $UtenteIE_rec['UTENTI']['UTELOG']
                    ));
                } else {
                    /*
                     * Effettuo l'update della password presente in CWOL su CW
                     * solo se non è impostato un metodo di cifratura della password
                     */

                    if (
                        !App::getConf('security.secure-password') ||
                        strtolower(App::getConf('security.secure-password')) == 'none'
                    ) {
                        $UtenteCW_rec['BOR_UTENTI']['PWDUTE'] = $UtenteIE_rec['UTENTI']['UTEPAS'];

                        $libBorUtenti_rec_update = array(
                            $UtenteIE_rec['UTENTI']['UTELOG'] => $UtenteCW_rec
                        );

                        $errorMessages = false;
                        $this->libBorUtenti->aggiornaUtente($UtenteIE_rec['UTENTI']['UTELOG'], $libBorUtenti_rec_update, $errorMessages);
                        if ($errorMessages) {
                            $this->htmlLog("Errore aggiornamento password su CW per utente {$UtenteIE_rec['UTENTI']['UTELOG']}.\n" . $errorMessages, true);
                            return false;
                        }

                        $this->htmlLog('Allineata password CW con quella presente su CWOL.');

                        $this->eqAudit->logEqEvent($this, array(
                            'Operazione' => eqAudit::OP_UPD_RECORD,
                            'Estremi' => 'Allineamento password utente CWOL => CW',
                            'Key' => $UtenteIE_rec['UTENTI']['UTELOG']
                        ));

                        $utentiCWPasswordUpdate[] = $UtenteIE_rec;
                    }
                }
            }

            try {
                ItaDB::DBUpdate($this->accLibCityWare->getITW(), 'UTENTI', 'UTECOD', $UtenteIE_rec['UTENTI']);
            } catch (ItaSecuredException $e) {
                $this->htmlLog("Errore durante l'aggiornamento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su UTENTI. " . $e->getSecuredMessage(), true);
                return false;
            } catch (Exception $e) {
                $this->htmlLog("Errore durante l'aggiornamento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su UTENTI. " . $e->getMessage(), true);
                return false;
            }

            if (!$UtenteIE_rec['RICHUT']) {
                $UtenteIE_import_rec['RICHUT']['RICCOD'] = $UTECOD;

                try {
                    ItaDB::DBInsert($this->accLibCityWare->getITW(), 'RICHUT', 'ROWID', $UtenteIE_import_rec['RICHUT']);
                } catch (ItaSecuredException $e) {
                    $this->htmlLog("Errore durante l'inserimento dati aggiuntivi dell'utente {$UtenteIE_import_rec['UTENTI']['UTELOG']} su RICHUT. " . $e->getSecuredMessage(), true);
                    return false;
                } catch (Exception $e) {
                    $this->htmlLog("Errore durante l'inserimento dati aggiuntivi dell'utente {$UtenteIE_import_rec['UTENTI']['UTELOG']} su RICHUT. " . $e->getMessage(), true);
                    return false;
                }
            } else {
                $UtenteIE_rec['RICHUT']['RICDEN'] = (!$UtenteIE_rec['RICHUT']['RICDEN']) ? $UtenteIE_import_rec['RICHUT']['RICDEN'] : $UtenteIE_rec['RICHUT']['RICDEN'];
                $UtenteIE_rec['RICHUT']['RICCOG'] = (!$UtenteIE_rec['RICHUT']['RICCOG']) ? $UtenteIE_import_rec['RICHUT']['RICCOG'] : $UtenteIE_rec['RICHUT']['RICCOG'];
                $UtenteIE_rec['RICHUT']['RICNOM'] = (!$UtenteIE_rec['RICHUT']['RICNOM']) ? $UtenteIE_import_rec['RICHUT']['RICNOM'] : $UtenteIE_rec['RICHUT']['RICNOM'];
                $UtenteIE_rec['RICHUT']['RICMAI'] = (!$UtenteIE_rec['RICHUT']['RICMAI']) ? $UtenteIE_import_rec['RICHUT']['RICMAI'] : $UtenteIE_rec['RICHUT']['RICMAI'];
                $UtenteIE_rec['RICHUT']['RICUSM'] = (!$UtenteIE_rec['RICHUT']['RICUSM']) ? $UtenteIE_import_rec['RICHUT']['RICUSM'] : $UtenteIE_rec['RICHUT']['RICUSM'];
                $UtenteIE_rec['RICHUT']['RICPWM'] = (!$UtenteIE_rec['RICHUT']['RICPWM']) ? $UtenteIE_import_rec['RICHUT']['RICPWM'] : $UtenteIE_rec['RICHUT']['RICPWM'];

                try {
                    ItaDB::DBUpdate($this->accLibCityWare->getITW(), 'RICHUT', 'ROWID', $UtenteIE_rec['RICHUT']);
                } catch (ItaSecuredException $e) {
                    $this->htmlLog("Errore durante l'aggiornamento dati aggiuntivi dell'utente {$UtenteIE_import_rec['UTENTI']['UTELOG']} su RICHUT. " . $e->getSecuredMessage(), true);
                    return false;
                } catch (Exception $e) {
                    $this->htmlLog("Errore durante l'aggiornamento dati aggiuntivi dell'utente {$UtenteIE_import_rec['UTENTI']['UTELOG']} su RICHUT. " . $e->getMessage(), true);
                    return false;
                }
            }

            foreach ($UtenteIE_import_rec['ENV_UTEMETA'] as $Env_utemeta_import_rec) {
                $Env_utemeta_rec = $this->accLib->GetEnv_Utemeta($UTECOD, 'codice', $Env_utemeta_import_rec['METAKEY']);

                if (!$Env_utemeta_rec) {
                    $Env_utemeta_import_rec['UTECOD'] = $UTECOD;
                    try {
                        ItaDB::DBInsert($this->accLibCityWare->getITALWEB(), 'ENV_UTEMETA', 'ROWID', $Env_utemeta_import_rec);
                    } catch (ItaSecuredException $e) {
                        $this->htmlLog("Errore durante l'inserimento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su ENV_UTEMETA. " . $e->getSecuredMessage(), true);
                        return false;
                    } catch (Exception $e) {
                        $this->htmlLog("Errore durante l'inserimento dell'utente {$UtenteIE_rec['UTENTI']['UTELOG']} su ENV_UTEMETA. " . $e->getMessage(), true);
                        return false;
                    }
                }
            }

            $this->htmlLog("Aggiornato utente: {$UtenteIE_rec['UTENTI']['UTELOG']}", false);
        } catch (Exception $e) {
            $this->htmlLog("get Utenti:" . $e->getMessage(), true);
            return false;
        }

        return true;
    }

}
