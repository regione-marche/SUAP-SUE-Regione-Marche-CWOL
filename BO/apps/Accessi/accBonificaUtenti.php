<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Accessi
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */

include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLibCityWare.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR_UTENTI.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbDBRequest.class.php';

function accBonificaUtenti() {
    $accBonificaUtenti = new accBonificaUtenti();
    $accBonificaUtenti->parseEvent();
    return;
}

class accBonificaUtenti extends itaModel {

    public $nameForm = 'accBonificaUtenti';
    public $gridUtenti = 'accBonificaUtenti_gridUtenti';
    private $accLib;
    private $accLibCityWare;
    private $libDB;
    private $libBorUtenti;
    private $errMessage;
    private $relUteCWOL = array(
        'GEPR' => array(
            'AUTORI' => 'AUTDIR',
            'AUTORI' => 'AUTDIR__2',
            'AUTORI' => 'AUTDIR__3',
            'AUTORI' => 'AUTDIR__4',
            'AUTORI' => 'AUTDIR__5',
            'AUTORI' => 'AUTDIR__6',
            'AUTORI' => 'AUTDIR__7',
            'AUTORI' => 'AUTDIR__8',
            'AUTORI' => 'AUTDIR__9',
            'AUTORI' => 'AUTDIR__10',
            'AUTORI' => 'DIRGES__1',
            'AUTORI' => 'DIRGES__2',
            'AUTORI' => 'DIRGES__3',
            'AUTORI' => 'DIRGES__4',
            'AUTORI' => 'DIRGES__5',
            'AUTORI' => 'DIRGES__6',
            'AUTORI' => 'DIRGES__7',
            'AUTORI' => 'DIRGES__8',
            'AUTORI' => 'DIRGES__9',
            'AUTORI' => 'DIRGES__10',
            'AUTORI' => 'AUTFIA__1',
            'DIPANA' => 'DIPLOG',
            'DIPANA' => 'DIPLOG__2',
            'DIPANA' => 'DIPLOG__3',
            'DIPANA' => 'DIPLOG__4',
            'DIPANA' => 'DIPLOG__5',
            'DIPANA' => 'DIPLOG__6',
            'DIPANA' => 'DIPLOG__7',
            'DIPANA' => 'DIPLOG__8',
            'DIPANA' => 'DIPLOG__9',
            'DIPANA' => 'DIPLOG__10',
            'RESPON' => 'RESLEG',
            'RESPON' => 'RESLOG'
        ),
        'PROT' => array(
            'ANAPRO' => 'PROUTE',
            'ANAPROSAVE' => 'PROUTE'
        )
    );
    private $relUteCW = array(
        'BOR_UTEORG' => 'CODUTE',
        'BOR_RESPO' => 'CODUTE_RESP',
        'BOR_AUTUTE' => 'CODUTE',
        'ATD_FUTIA' => 'CODUTEOPE',
        'ATD_FUTIAD' => 'CODUTEOPE',
        'ATD_ITERS' => 'CODUTEOPE',
        'BTA_CIG' => 'CODUTE_RUP',
        'BTA_CUP' => 'CODUTE_RUP',
        'FTA_AUTUTE' => 'CODUTE_OP',
        'FAC_DETDOC' => 'CODUTE_EF',
        'FAC_DETVIS' => 'CODUTE_VIS',
        'FAC_TESDOC' => 'CODUTE_VIS',
        'FAC_TESDOC' => 'CODUTE_ASS',
        'FBA_RICDET' => 'CODUTE_VER',
        'FBA_VARBTA' => 'CODUTE_INVIO',
        'FCA_CCOSTO' => 'CODUTERES',
        'FCA_TABCOM' => 'CODUTERES',
        'FEC_MOVCA' => 'MCE_CODUT',
        'FES_DOCASS' => 'CODUTE_ASS',
        'FES_DOCTES' => 'CODUTE_CO',
        'FES_LIQATT' => 'CODUTE_DL',
        'FES_LIQTES' => 'CODUTE',
        'FES_MIFDM' => 'ESITOBT_ORD_CODUTE',
        'FES_MIFLOG' => 'CODUTERICH',
        'FES_MIFORF' => 'ESITOBT_ORD_CODUTE',
        'FES_MIFORF' => 'ESITOBT_ORD_CODUTE2',
        'FES_MIFT' => 'CODUTEFIRM',
        'FES_MIFT' => 'ESITOP_FLU_CODUTE',
        'FES_MIFT' => 'ESITOBT_FLU_CODUTE',
        'FES_MIFT' => 'VISTO_CODUTE',
        'FFE_UTEAUT' => 'CODUTEAUT',
        'FFE_UTEAUT' => 'CODUTEAUT',
        'FIN_SDATE' => 'CODUTE_USO',
        'FTA_FUNZUT' => 'CODUTE_FIR',
        'FTM_TIPDSE' => 'CODUTEAUT',
        'FEC_AGECON' => 'CODUTERES',
        'FBA_UTEVER' => 'CODUTE_VER',
        'BOR_UTEFIR' => 'CODUTE',
        'BOR_UTELIV' => 'CODUTENTE',
        'BOR_FRMUTE' => 'CODUTE',
        'BOR_GULDAP' => 'KCODUTE',
        'BOR_ORGUTE' => 'KCODUTE',
        'BOR_UTEPRO' => 'CODUTE',
        'BOR_UTERUO' => 'CODUTEA',
        'BOR_UTLDAP' => 'CODUTE',
        'BOR_UTFOTO' => 'IDUTE',
        'BOR_UTRUOR' => 'KCODUTE',
        'BOR_UTTOOL' => 'CODUT_TOOL',
        'DTA_OPECI' => 'OPERATORE',
        'DTA_OPEDEL' => 'OPERATORE',
        'DTA_OPETES' => 'OPERATORE',
        'DTA_OPETES2' => 'OPERATORE',
        'DTA_OPEDOC' => 'OPERATORE',
        'DTA_OPETCI' => 'OPERATORE',
        'DTA_OPETDO' => 'OPERATORE'
    );

    const C1_1 = 1;
    const Cn_1 = 2;
    const C1_0 = 3;
    const C0_1 = 4;
    const C1_n = 5;
    const Cn_n = 6;
    const Cn_0 = 7;
    const C0_n = 8;

    public function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->accLibCityWare = new accLibCityWare();
        $this->libDB = new cwbLibDB_GENERIC();
        $this->libBorUtenti = new cwbLibDB_BOR_UTENTI();
    }

    public function __destruct() {
        parent::__destruct();

        if ($this->close != true) {
            
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                if (!$this->libBorUtenti->getCitywareDB()) {
                    Out::msgStop('Attenzione', 'Nessuna connessione al DB CityWare disponibile.');
                }

                $this->openRisultato();
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridUtenti:
                        $this->caricaGrigliaUtenti();
                        break;
                }
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_confermaBonifica':
                        $username = $_POST[$this->gridUtenti]['gridParam']['selrow'];

                        switch ($this->getOperazionePerUtente($username)) {
                            case self::C1_1:
                                $result = $this->operazioneCWAggiornamentoRiferimentiUtente($username);
                                break;

                            case self::C1_0:
                                $result = $this->operazioneCWInserisciUtente($username);
                                break;

                            case self::C0_n:
                            case self::C1_n:
                                $result = $this->operazioneCWCancellaUtenteMinuscolo($username);
                                break;

                            case self::Cn_0:
                            case self::Cn_1:
                                $result = $this->operazioneCWOLCancellaUtenteDoppio($username);
                                break;

                            default:
                                Out::msgStop('Attenzione', 'Operazione non gestita.');
                                break 2;
                        }

                        if (!$result) {
                            Out::msgStop('Errore', $this->errMessage);
                            break;
                        }

                        $this->caricaGrigliaUtenti();
                        Out::msgBlock($this->nameForm, 1500, false, 'Operazione effettuata con successo.');
                        break;

                    case $this->nameForm . '_unisciUtenteEsistente':
                        accRic::accRicUtenti($this->nameForm);
                        break;

                    case $this->nameForm . '_cambiaNome':
                        Out::msgInputText($this->nameForm, 'Inserisci nuovo nome utente', 'Inserisci il nuovo nome utente');
                        break;

                    case $this->nameForm . '_modal_conferma':
                        $username = $_POST[$this->gridUtenti]['gridParam']['selrow'];
                        $destinazione = $_POST[$this->nameForm . '_modal_inputText'];

                        $utenti_rec = $this->accLib->GetUtenti($destinazione, 'utelog');
                        if ($utenti_rec) {
                            Out::msgStop('Attenzione', 'Nome utente già esistente.');
                            break;
                        }

                        $this->operazioneCambiaNome($username, $destinazione);
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridUtenti:
                        $username = $_POST['rowid'];
                        $messaggio = 'Proseguire con l\'operazione di bonifica?<br>';
                        $messaggio .= 'Questo comporterà ';

                        $bottoni = array(
                            'Annulla' => array('id' => $this->nameForm . '_annullaBonifica', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_confermaBonifica', 'model' => $this->nameForm)
                        );

                        switch ($this->getOperazionePerUtente($username)) {
                            case self::C0_1:
                                Out::msgInfo('Bonifica utente', 'Utilizzare la procedura di sincronizzazione.');
                                break 2;

                            case self::C1_1:
                                $messaggio .= 'l\'aggiornamento dei riferimenti all\'utente nelle tabelle correlate in Cityware.';
                                break;

                            case self::C1_0:
                                $messaggio .= 'l\'inserimento dell\'utente in Cityware.';
                                break;

                            case self::C0_n:
                            case self::C1_n:
                                $messaggio .= 'la cancellazione dell\'utente in Cityware con nominativo in minuscolo.';
                                break;

                            case self::Cn_0:
                            case self::Cn_1:
                                $messaggio .= 'la cancellazione dell\'utente in CitywareOnline duplicato erroneamente.';
                                break;

                            case self::Cn_n:
                                $messaggio = 'Scegliere l\'opzione desiderata.';

                                $bottoni = array(
                                    'Unisci ad utente esistente' => array('id' => $this->nameForm . '_unisciUtenteEsistente', 'model' => $this->nameForm),
                                    'Cambia nome' => array('id' => $this->nameForm . '_cambiaNome', 'model' => $this->nameForm)
                                );
                                break;
                        }

                        Out::msgQuestion('Conferma Bonifica', $messaggio, $bottoni);
                        break;
                }
                break;

            case 'exportTableToExcel':
                $arrayGridUtenti = $this->getArrayGridUtenti();
                foreach ($arrayGridUtenti as &$recordUtente) {
                    $recordUtente = array_map('strip_tags', $recordUtente);
                }

                $tableView = new TableView($this->gridUtenti, array(
                    'arrayTable' => $arrayGridUtenti,
                    'rowIndex' => 'idx'
                ));

                $tableView->exportXLS('', 'BonificaUtenti.xls');
                break;

            case 'returnutenti':
                $username = $_POST[$this->gridUtenti]['gridParam']['selrow'];
                $utenti_rec = $this->accLib->GetUtenti($_POST['retKey'], 'rowid');
                $this->operazioneUnioneUtenteEsistente($username, $utenti_rec['UTELOG']);
                break;
        }
    }

    public function openRisultato() {
        TableView::enableEvents($this->gridUtenti);
        TableView::reload($this->gridUtenti);
        Out::setFocus($this->nameForm, $this->gridUtenti);
    }

    private function getArrayGridUtenti() {
        $indiceUtentiCityware = $this->libBorUtenti->leggiIndiceUtenti();
        $indiceUtentiCitywareOnline = ItaDB::DBSQLSelect($this->accLib->getITW(), "SELECT UTELOG FROM UTENTI");

        $utenti_cw = array_column($indiceUtentiCityware, 'CODUTE');
        $utenti_cwol = array_column($indiceUtentiCitywareOnline, 'UTELOG');

        $arrayGridUtenti = $this->elaboraRecordUtenti(array(), $utenti_cw, 'UTECW');
        $arrayGridUtenti = $this->elaboraRecordUtenti($arrayGridUtenti, $utenti_cwol, 'UTECWOL');
        $arrayGridUtenti = $this->analizzaRecordUtenti($arrayGridUtenti);

        return $arrayGridUtenti;
    }

    private function caricaGrigliaUtenti() {
        return $this->caricaGriglia($this->gridUtenti, array(
                'arrayTable' => $this->getArrayGridUtenti(),
                'rowIndex' => 'idx'
        ));
    }

    private function elaboraRecordUtenti($arrayGridUtenti, $utenti, $campoUtente) {
        foreach ($utenti as $USERNAME) {
            $lowercaseUSERNAME = strtolower($USERNAME);

            if (!isset($arrayGridUtenti[$lowercaseUSERNAME])) {
                $arrayGridUtenti[$lowercaseUSERNAME] = array(
                    'UTECWOL' => array(),
                    'UTECW' => array(),
                    'BONIFICA' => ''
                );
            }

            $arrayGridUtenti[$lowercaseUSERNAME][$campoUtente][] = $USERNAME;
        }

        return $arrayGridUtenti;
    }

    private function analizzaRecordUtenti($arrayGridUtenti) {
        foreach ($arrayGridUtenti as $lowercaseUSERNAME => $recordGridUtenti) {
            /*
             * Caso normale. Un utente per CW ed uno per CWOL.
             */

            $arrayGridUtenti[$lowercaseUSERNAME]['STATO'] = '<i class="ui-icon ui-icon-cancel" style="color: red;"></i>';

            switch ($this->getOperazionePerArrayUtenti($recordGridUtenti['UTECWOL'], $recordGridUtenti['UTECW'])) {
                case self::C1_1:
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-vcs-compare"></i> Aggiorna riferimenti Cityware';
                    $arrayGridUtenti[$lowercaseUSERNAME]['STATO'] = '<i class="ui-icon ui-icon-check" style="color: green;"></i>';
                    break;

                case self::C0_1:
                    $arrayGridUtenti[$lowercaseUSERNAME]['UTECWOL'][] = '<i style="color: red;">Utente mancante</i>';
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-transfer-e-w"></i> Effettuare la sincronizzazione';
                    $arrayGridUtenti[$lowercaseUSERNAME]['STATO'] = '<i class="ui-icon ui-icon-minus"></i>';
                    break;

                case self::C1_0:
                    $arrayGridUtenti[$lowercaseUSERNAME]['UTECW'][] = '<i style="color: red;">Utente mancante</i>';
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-redo"></i> Inserisci utente Cityware';
                    break;

                case self::C0_n:
                    $arrayGridUtenti[$lowercaseUSERNAME]['UTECWOL'][] = '<i style="color: red;">Utente mancante</i>';
                case self::C1_n:
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-recycle"></i> Rimuovi utente duplicato Cityware';
                    break;

                case self::Cn_0:
                    $arrayGridUtenti[$lowercaseUSERNAME]['UTECW'][] = '<i style="color: red;">Utente mancante</i>';
                case self::Cn_1:
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-recycle"></i> Rimuovi utente duplicato CitywareOnline';
                    break;

                case self::Cn_n:
                    $arrayGridUtenti[$lowercaseUSERNAME]['BONIFICA'] = '<i class="ui-icon ui-icon-wrench"></i> Seleziona un\'opzione di bonifica';
                    break;

                default:
                    unset($arrayGridUtenti[$lowercaseUSERNAME]);
                    break;
            }
        }

        foreach ($arrayGridUtenti as $k => $v) {
            /*
             * Normalizzo gli elenchi di username.
             */

            $arrayGridUtenti[$k]['UTECW'] = implode('<br>', $v['UTECW']);
            $arrayGridUtenti[$k]['UTECWOL'] = implode('<br>', $v['UTECWOL']);
        }

        return $arrayGridUtenti;
    }

    private function getOperazionePerUtente($username) {
        $ITW = $this->accLib->getITW();
        $sql = "SELECT UTELOG FROM UTENTI WHERE " . $ITW->strUpper('UTELOG') . " = " . $ITW->strUpper("'" . addslashes($username) . "'") . "";

        $utenteCityware = $this->libBorUtenti->leggiUtenti($username);
        $utenteCitywareOnline = ItaDB::DBSQLSelect($ITW, $sql);
        $nomiUtenteCityware = array_keys($utenteCityware);
        $nomiUtenteCitywareOnline = array_column($utenteCitywareOnline, 'UTELOG');

        return $this->getOperazionePerArrayUtenti($nomiUtenteCitywareOnline, $nomiUtenteCityware);
    }

    private function getOperazionePerArrayUtenti($tabUtentiCWOL, $tabUtentiCW) {
        if (
            count($tabUtentiCW) === 1 &&
            count($tabUtentiCWOL) === 1
        ) {
            return self::C1_1;
        }

        /*
         * Casi da bonificare...
         */

        /*
         * Caso con due utenti CWOL ed uno CW.
         */

        if (
            count($tabUtentiCW) === 1 &&
            count($tabUtentiCWOL) > 1
        ) {
            return self::Cn_1;
        }

        /*
         * Caso con due utenti CW ed uno CWOL.
         */

        if (
            count($tabUtentiCW) > 1 &&
            count($tabUtentiCWOL) === 1
        ) {
            return self::C1_n;
        }

        /*
         * Caso senza utente CW.
         */

        if (
            count($tabUtentiCW) === 0
        ) {
            return count($tabUtentiCWOL) > 1 ? self::Cn_0 : self::C1_0;
        }

        /*
         * Caso senza utente CWOL.
         */

        if (
            count($tabUtentiCWOL) === 0
        ) {
            return count($tabUtentiCW) > 1 ? self::C0_n : self::C0_1;
        }

        /*
         * Caso con più utenti in entrambi i DB.
         */

        if (
            count($tabUtentiCW) > 1 &&
            count($tabUtentiCWOL) > 1
        ) {
            return self::Cn_n;
        }
    }

    /**
     * Aggiorna i riferimenti nelle tabelle CW per il dato $CODUTE.
     * @param type $CODUTE
     */
    private function operazioneCWAggiornamentoRiferimentiUtente($CODUTE) {
        $CODUTE = strtoupper(trim($CODUTE));

        $errorMessages = array();

        foreach ($this->relUteCW as $tableName => $fieldName) {
            /*
             * Lettura record della relazione
             */

            $risultati = $this->libDB->leggiGeneric($tableName, array($fieldName => $CODUTE));

            foreach ($risultati as $risultato) {
                /*
                 * Aggiorno il campo del CODUTE
                 */

                $dataToUpdate = $risultato;
                $dataToUpdate[$fieldName] = strtoupper($dataToUpdate[$fieldName]);

                /*
                 * Aggiorno il record
                 */

                try {
                    $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
                    $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                    $modelServiceData->addMainRecord($tableName, $dataToUpdate);
                    $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $this->nameForm, $dataToUpdate);
                    $modelService->updateRecord($this->libBorUtenti->getCitywareDB(), $tableName, $modelServiceData->getData(), $recordInfo, $risultato);
                } catch (Exception $e) {
                    $errorMessages[] = "errore aggiornamento $tableName.$fieldName: " . $e->getMessage();
                }
            }
        }

        if (count($errorMessages)) {
            $this->errMessage = sprintf("Si sono verificati %d errori durante l'aggiornamento dei riferimenti:\n- %s", count($errorMessages), implode("\n- ", $errorMessages));
            return false;
        }

        return true;
    }

    /**
     * Inserisci l'UTECOD mancante nell'anagrafica utenti CW.
     * @param type $UTELOG
     */
    private function operazioneCWInserisciUtente($UTELOG) {
        /*
         * Prendo il record utenti in modo case-insensitive
         */

        $ITW = $this->accLib->getITW();
        $sql = "SELECT * FROM UTENTI WHERE " . $ITW->strUpper('UTELOG') . " = " . $ITW->strUpper("'" . addslashes($UTELOG) . "'") . "";
        $utenti_rec = ItaDB::DBSQLSelect($ITW, $sql, false);

        if (!$utenti_rec) {
            $this->errMessage = "Utente CitywareOnline '$UTELOG' non trovato.";
            return false;
        }

        /*
         * Prendo i record itaEngine
         */

        $UtenteIE_rec = array(
            'UTENTI' => $utenti_rec,
            'RICHUT' => $this->accLib->GetRichut($utenti_rec['UTECOD']),
            'ENV_UTEMETA' => ItaDB::DBSQLSelect($this->accLib->getITALWEB(), "SELECT * FROM ENV_UTEMETA WHERE UTECOD = '{$utenti_rec['UTECOD']}'")
        );

        /*
         * Trasformo il record in formato CW
         */

        $UtenteCW_rec = $this->accLibCityWare->convertUserIE2CW($UtenteIE_rec);

        if ($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE'] == false) {
            unset($UtenteCW_rec['BOR_FRMUTE']['IDFRMUTE']);
        }

        /*
         * Inserisco i default
         */

        if (!$UtenteCW_rec['BOR_UTENTI']['DATAINIZ']) {
            $UtenteCW_rec['BOR_UTENTI']['DATAINIZ'] = date('Ymd');
        }

        $UtenteCW_rec['BOR_UTENTI']['SIGLAUTE'] = 'IT';
        $UtenteCW_rec['BOR_UTENTI']['UTEDB'] = $_POST[$this->nameForm . '_PARINS']['UTEDB'];
        $UtenteCW_rec['BOR_UTENTI']['PWDB'] = $_POST[$this->nameForm . '_PARINS']['PWDDB'];

        /*
         * Password temporanea
         */

        $UtenteCW_rec['BOR_UTENTI']['PWDUTE'] = $this->accLibCityWare->getTempPassword();

        /*
         * Ricreo nel formato originale
         */

        $libBorUtenti_rec_update = array(
            $UtenteCW_rec['BOR_UTENTI']['CODUTE'] => $UtenteCW_rec
        );

        $errorMessages = false;

        $this->libBorUtenti->inserisciUtente($UtenteCW_rec['BOR_UTENTI']['CODUTE'], $libBorUtenti_rec_update, $errorMessages);

        if ($errorMessages) {
            $this->errMessage = "Errore in inserimento utente Cityware '$UTELOG': $errorMessages";
            return false;
        }

        return true;
    }

    /**
     * Cancella l'utente CODUTE presente in minuscolo dal database CW.
     * @param type $CODUTE
     */
    private function operazioneCWCancellaUtenteMinuscolo($CODUTE) {
        /*
         * Prendo il CODUTE dall'elenco degli utenti, escludendo quello in maiuscolo
         */

        $arrayCODUTE = $errorMessages = array();
        $utenteMaiuscoloFound = false;
        $indiceUtentiCityware = $this->libBorUtenti->leggiIndiceUtenti();

        foreach ($indiceUtentiCityware as $recordUtenteCityware) {
            if (
                strtolower($recordUtenteCityware['CODUTE']) == strtolower($CODUTE) &&
                $recordUtenteCityware['CODUTE'] != strtoupper($CODUTE)
            ) {
                $arrayCODUTE[] = $recordUtenteCityware['CODUTE'];
            }

            if ($recordUtenteCityware['CODUTE'] == strtoupper($CODUTE)) {
                $utenteMaiuscoloFound = true;
            }
        }

        if (!$utenteMaiuscoloFound) {
            /*
             * Situazione anomala, blocco la procedura
             */

            $this->errMessage = "Non è stato trovato nessun utente con CODUTE '" . strtoupper($CODUTE) . "'.";
            return false;
        }

        foreach ($arrayCODUTE as $currentCODUTE) {
            foreach ($this->relUteCW as $tableName => $fieldName) {
                cwbDBRequest::getInstance()->startManualTransaction();

                try {
                    $sql = "DELETE FROM $tableName WHERE $fieldName = '$currentCODUTE'";
                    ItaDB::DBSQLExec($this->libBorUtenti->getCitywareDB(), $sql);
                    cwbDBRequest::getInstance()->commitManualTransaction();
                } catch (Exception $e) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    $errorMessages[] = "errore cancellazione $tableName.$fieldName: " . $e->getMessage();
                }
            }
        }

        if (count($errorMessages)) {
            $this->errMessage = "Errore in cancellazione utente Cityware '$CODUTE':\n- " . implode("\n- ", $errorMessages);
            return false;
        }

        return true;
    }

    /**
     * Rimuove per un dato UTELOG l'utente "di troppo" verificando
     * i riferimenti negli applicativi collegati.
     * @param type $UTELOG
     */
    private function operazioneCWOLCancellaUtenteDoppio($UTELOG) {
        $errorMessages = array();

        $ITW = $this->accLib->getITW();
        $sql = "SELECT * FROM UTENTI WHERE " . $ITW->strUpper('UTELOG') . " = " . $ITW->strUpper("'" . addslashes($UTELOG) . "'") . "";
        $utenti_tab = ItaDB::DBSQLSelect($ITW, $sql);

        /*
         * Determino il case utilizzato per individuare l'utente
         * da preservare.
         */

        $configCase = strtoupper(App::getConf('security.insert-user-case'));
        switch ($configCase) {
            case 'UPPER':
                $UTELOG = strtoupper($UTELOG);
                break;

            case 'LOWER':
                $UTELOG = strtolower($UTELOG);
                break;

            default:
                $this->errMessage = "Impossibile determinare il case dell'utente da mantenere in quanto il parametro 'security.insert-user-case' non è stato configurato.";
                return false;
        }

        if (!in_array($UTELOG, array_column($utenti_tab, 'UTELOG'))) {
            $this->errMessage = "L'utente da inserire '$UTELOG' non è stato trovato in anagrafica.";
            return false;
        }

        foreach ($utenti_tab as $utenti_rec) {
            $currentUTELOG = $utenti_rec['UTELOG'];

            if ($currentUTELOG === $UTELOG) {
                continue;
            }

            $audit = "Allineamento utente CitywareOnline '$currentUTELOG'";

            /*
             * Aggiornamento relazioni
             */

            foreach ($this->relUteCWOL as $dbName => $relTable) {
                $currentDatabase = ItaDB::DBOpen($dbName);
                if (!$currentDatabase->exists()) {
                    continue;
                }

                foreach ($relTable as $tableName => $fieldName) {
                    if (!in_array($tableName, $currentDatabase->listTables())) {
                        continue;
                    }

                    try {
                        $tableRecords = ItaDB::DBSQLSelect($currentDatabase, "SELECT * FROM $tableName WHERE $fieldName = '" . addslashes($currentUTELOG) . "'");
                        foreach ($tableRecords as $tableRecord) {
                            $tableRecord[$fieldName] = $UTELOG;
                            $this->updateRecord($currentDatabase, $tableName, $tableRecord, $audit);
                        }
                    } catch (Exception $e) {
                        $errorMessages[] = "errore aggiornamento $dbName.$tableName.$fieldName: " . $e->getMessage();
                    }
                }
            }

            /*
             * Cancellazione record RICHUT
             */

            try {
                $richut_rec = $this->accLib->GetRichut($utenti_rec['UTECOD']);
                if ($richut_rec) {
                    ItaDB::DBDelete($this->accLib->getITW(), 'RICHUT', 'ROWID', $richut_rec['ROWID']);
                }
            } catch (Exception $e) {
                $errorMessages[] = "errore cancellazione RICHUT '$currentUTELOG': " . $e->getMessage();
                return false;
            }

            /*
             * Cancellazione utente
             */

            try {
                ItaDB::DBDelete($this->accLib->getITW(), 'UTENTI', 'ROWID', $utenti_rec['ROWID']);
            } catch (Exception $e) {
                $errorMessages[] = "errore cancellazione UTENTI '$currentUTELOG': " . $e->getMessage();
                return false;
            }
        }

        if (count($errorMessages)) {
            $this->errMessage = sprintf("Si sono verificati %d errori durante l'aggiornamento dei riferimenti:\n- %s", count($errorMessages), implode("\n- ", $errorMessages));
            return false;
        }

        return true;
    }

    private function operazioneUnioneUtenteEsistente($CODUTE, $UTEDEST) {
        /*
         * Determino il case utilizzato per individuare l'utente
         * da preservare.
         * Normalizzo $UTEDEST se impostato il parametro.
         */

        $mainUTELOG = $CODUTE;
        $configCase = strtoupper(App::getConf('security.insert-user-case'));
        switch ($configCase) {
            case 'UPPER':
                $mainUTELOG = strtoupper($mainUTELOG);
                $UTEDEST = strtoupper($UTEDEST);
                break;

            case 'LOWER':
                $mainUTELOG = strtolower($mainUTELOG);
                $UTEDEST = strtolower($UTEDEST);
                break;

            default:
                $this->errMessage = "Impossibile determinare il case dell'utente da mantenere in quanto il parametro 'security.insert-user-case' non è stato configurato.";
                return false;
        }

        $ITW = $this->accLib->getITW();
        $sql = "SELECT * FROM UTENTI WHERE " . $ITW->strUpper('UTELOG') . " = " . $ITW->strUpper("'" . addslashes($CODUTE) . "'") . "";
        $utenti_tab = ItaDB::DBSQLSelect($ITW, $sql);
        foreach ($utenti_tab as $utenti_rec) {
            if ($utenti_rec['UTELOG'] !== $mainUTELOG) {
                $CODUTE = $utenti_rec['UTELOG'];
                break;
            }
        }

        if ($mainUTELOG === $CODUTE) {
            $this->errMessage = "Non è stato trovato l'utente da spostare nell'anagrafica UTENTI.";
            return false;
        }

        /*
         * Effettuo le operazioni di merge nelle tabelle correlate.
         * Verifico se presente il record per l'utente DESTINAZIONE:
         * in caso positivo cancello il record dell'utente convertito,
         * in caso negativo aggiorno il riferimento dell'utente convertito
         * con riferimento all'utente DESTINAZIONE.
         */

        /*
         * Cityware
         */

        $CODUTE_CW = strtoupper(trim($CODUTE));
        $UTEDEST_CW = strtoupper(trim($UTEDEST));

        $errorMessages = array();

        foreach ($this->relUteCW as $tableName => $fieldName) {
            /*
             * Verifico la presenza del record DESTINAZIONE
             */

            $checkResults = $this->libDB->leggiGeneric($tableName, array($fieldName => $UTEDEST_CW));
            if (count($checkResults)) {
                /*
                 * Record presenti, cancello i record dell'utente convertito
                 */

                cwbDBRequest::getInstance()->startManualTransaction();

                try {
                    $sql = "DELETE FROM $tableName WHERE $fieldName = '$CODUTE_CW'";
                    ItaDB::DBSQLExec($this->libBorUtenti->getCitywareDB(), $sql);
                    cwbDBRequest::getInstance()->commitManualTransaction();
                } catch (Exception $e) {
                    cwbDBRequest::getInstance()->rollBackManualTransaction();
                    $errorMessages[] = "errore cancellazione $tableName.$fieldName: " . $e->getMessage();
                }
            } else {
                /*
                 * Record non presenti, aggiorno i record dell'utente convertito
                 */

                $updateResults = $this->libDB->leggiGeneric($tableName, array($fieldName => $CODUTE_CW));
                foreach ($updateResults as $resultRecord) {
                    $dataToUpdate = $resultRecord;
                    $dataToUpdate[$fieldName] = $UTEDEST_CW;

                    try {
                        $modelService = cwbModelServiceFactory::newModelService(cwbModelHelper::modelNameByTableName($tableName), true, false);
                        $modelServiceData = new itaModelServiceData(new cwbModelHelper());
                        $modelServiceData->addMainRecord($tableName, $dataToUpdate);
                        $recordInfo = itaModelHelper::impostaRecordInfo(itaModelService::OPERATION_UPDATE, $this->nameForm, $dataToUpdate);
                        $modelService->updateRecord($this->libBorUtenti->getCitywareDB(), $tableName, $modelServiceData->getData(), $recordInfo, $resultRecord);
                    } catch (Exception $e) {
                        $errorMessages[] = "errore aggiornamento $tableName.$fieldName: " . $e->getMessage();
                    }
                }
            }
        }

        /*
         * CitywareOnline
         * In ogni caso aggiorno i riferimenti dell'utente.
         */

        foreach ($this->relUteCWOL as $dbName => $relTable) {
            $currentDatabase = ItaDB::DBOpen($dbName);
            if (!$currentDatabase->exists()) {
                continue;
            }

            foreach ($relTable as $tableName => $fieldName) {
                if (!in_array($tableName, $currentDatabase->listTables())) {
                    continue;
                }

                $sqlString = "SELECT * FROM $tableName WHERE $fieldName = '$CODUTE'";
                $updateResults = ItaDB::DBSQLSelect($currentDatabase, $sqlString);
                foreach ($updateResults as $resultRecord) {
                    $resultRecord[$fieldName] = $UTEDEST;

                    try {
                        ItaDB::DBUpdate($currentDatabase, $tableName, '', $updateResults);
                    } catch (Exception $e) {
                        $errorMessages[] = "errore aggiornamento $tableName.$fieldName: " . $e->getMessage();
                    }
                }
            }
        }

        /*
         * Verifico la presenza del record nelle tabelle principali.
         * Se presente, cancello il record dell'utente convertito.
         * Se non presente, aggiorno il riferimento nella tabella.
         */

        /*
         * Cityware
         */

        $checkBOR_UTENTI = $this->libDB->leggiGeneric('BOR_UTENTI', array('CODUTE' => $UTEDEST_CW), false);
        if ($checkBOR_UTENTI) {
            try {
                $sql = "DELETE FROM BOR_UTENTI WHERE CODUTE = '$CODUTE_CW'";
                ItaDB::DBSQLExec($this->libBorUtenti->getCitywareDB(), $sql);
                cwbDBRequest::getInstance()->commitManualTransaction();
            } catch (Exception $e) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
                $errorMessages[] = "errore cancellazione BOR_UTENTI.CODUTE: " . $e->getMessage();
            }
        } else {
            $currentBOR_UTENTI = $this->libDB->leggiGeneric('BOR_UTENTI', array('CODUTE' => $CODUTE_CW), false);
            $currentBOR_UTENTI['CODUTE'] = $UTEDEST_CW;

            try {
                ItaDB::DBUpdate($this->libBorUtenti->getCitywareDB(), 'BOR_UTENTI', '', $sql);
                cwbDBRequest::getInstance()->commitManualTransaction();
            } catch (Exception $e) {
                cwbDBRequest::getInstance()->rollBackManualTransaction();
                $errorMessages[] = "errore aggiornamento BOR_UTENTI.CODUTE: " . $e->getMessage();
            }
        }

        /*
         * CitywareOnline
         */

        $sqlString = "SELECT * FROM UTENTI WHERE UTELOG = '" . addslashes($UTEDEST) . "'";
        $checkUTENTI = ItaDB::DBSQLSelect($this->accLib->getITW(), $sqlString, false);
        if ($checkUTENTI) {
            try {
                $sql = "DELETE FROM UTENTI WHERE UTELOG = '" . addslashes($CODUTE) . "'";
                ItaDB::DBSQLExec($this->accLib->getITW(), $sql);
            } catch (Exception $e) {
                $errorMessages[] = "errore cancellazione UTENTI.UTELOG: " . $e->getMessage();
            }
        } else {
            $sqlString = "SELECT * FROM UTENTI WHERE UTELOG = '" . addslashes($CODUTE) . "'";
            $currentUTENTI = ItaDB::DBSQLSelect($this->accLib->getITW(), $sqlString, false);
            $currentUTENTI['UTELOG'] = $UTEDEST;

            try {
                ItaDB::DBUpdate($this->accLib->getITW(), 'UTENTI', 'ROWID', $sql);
            } catch (Exception $e) {
                $errorMessages[] = "errore aggiornamento UTENTI.UTELOG: " . $e->getMessage();
            }
        }

        if (count($errorMessages)) {
            $this->errMessage = sprintf("Si sono verificati %d errori durante l'aggiornamento dei riferimenti:\n- %s", count($errorMessages), implode("\n- ", $errorMessages));
            return false;
        }

        return true;
    }

    private function operazioneCambiaNome($CODUTE, $UTEDEST) {
        /*
         * Verifico che non sia già presente un $UTEDEST.
         */

        $indiceUtentiCityware = $this->libBorUtenti->leggiIndiceUtenti();
        $indiceUtentiCitywareOnline = ItaDB::DBSQLSelect($this->accLib->getITW(), "SELECT UTELOG FROM UTENTI");

        $utenti_cw = array_map('strtoupper', array_column($indiceUtentiCityware, 'CODUTE'));
        $utenti_cwol = array_map('strtoupper', array_column($indiceUtentiCitywareOnline, 'UTELOG'));

        if (in_array(strtoupper($UTEDEST, $utenti_cw)) || in_array(strtoupper($UTEDEST), $utenti_cwol)) {
            $this->errMessage = "Utente di destinazione $UTEDEST già esistente.";
            return false;
        }

        return $this->operazioneUnioneUtenteEsistente($CODUTE, $UTEDEST);
    }

    private function caricaGriglia($id, $opts, $page = null, $rows = null, $sidx = null, $sord = null) {
        TableView::clearGrid($id);

        $gridObj = new TableView($id, $opts);

        $sortIndex = $_POST['sidx'] ?: $sidx ?: '';
        $sortOrder = $_POST['sord'] ?: $sord ?: '';

        $gridObj->setPageNum($page ?: $_POST['page'] ?: $_POST[$id]['gridParam']['page'] ?: 1);
        $gridObj->setPageRows($rows ?: $_POST['rows'] ?: $_POST[$id]['gridParam']['rowNum'] ?: 50);
        $gridObj->setSortIndex($sortIndex);
        $gridObj->setSortOrder($sortOrder);

        return $gridObj->getDataPage('json');
    }

}
