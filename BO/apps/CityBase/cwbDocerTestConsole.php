<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaLdap.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocer/itaDocerClientFactory.class.php';

function cwbDocerTestConsole() {
    $cwbDocerTestConsole = new cwbDocerTestConsole();
    $cwbDocerTestConsole->parseEvent();
    return;
}

class cwbDocerTestConsole extends itaFrontController {

    // Nome form da innestare per la singola riga di test
    const INNER_FORM = 'cwbDocerTestRow';
    const DEFAULT_CODICE_ENTE = 'C_F632';
    const DEFAULT_CODICE_AOO = 'C_F632';
    const DEFAULT_GRUPPO_UTENTI = "TG_20"; //id del gruppo da aggiornare  
    const DEFAULT_UTENTE = "TEST_GROUP_10_USER_001"; //id del utente  

    private $currentTestId;
    private $searchCriteria;
    // Array che contiene la definizione di tutti i test
    // (Vedi documento: https://drive.google.com/drive/folders/0B3FODK-pBzWIbGhJaG9mVXlaclk)
    private $TEST_DEF = array(
        1 => array(
            'id' => 1,
            'alias' => 'REGISTRO-01',
            'name' => 'Verifica configurazione iniziale LDAP',
            'des' => 'Verificare la configurazione del sistema di autenticazione e suo funzionamento',
            'testable' => 1,
            'customFieldsRenderer' => 'test1Renderer',
            'defaultValues' => array(
                'ldap_host' => 'srvcity-demo.gruppoapra.com',
                'ldap_port' => 389,
                'ldap_dn' => 'ou=People,dc=paldemo,dc=com',
                'ldap_username' => 'paldemo',
                'ldap_password' => 'paldemo'
            )
        ),
        2 => array(
            'id' => 2,
            'alias' => 'REGISTRO-02',
            'name' => 'Verifica configurazione iniziale',
            'des' => 'Verificare che siano state definite le configurazioni minime iniziali: Mapping Tipi (TYPE_ID) e relativi metadati',
            'testable' => 1,
            'customFieldsRenderer' => 'test2Renderer',
            'defaultValues' => array(
                'test2_codiceEnte' => self::DEFAULT_CODICE_ENTE,
                'test2_codiceAOO' => self::DEFAULT_CODICE_AOO
            )
        ),
        3 => array(
            'id' => 3,
            'alias' => 'REGISTRO-03',
            'name' => 'Primo Riversamento Gruppi - Utenti in maniera sincrona',
            'des' => 'Verificare la corretta inizializzazione della struttura di Gruppi ed Utenti',
            'testable' => 1,
            'customFieldsRenderer' => 'test3Renderer'
        ),
        4 => array(
            'id' => 4,
            'alias' => 'REGISTRO-04',
            'name' => 'Riversamento massivo delle anagrafiche già presenti nel verticale',
            'des' => 'Verificare il corretto funzionamento dell?utility di allineamento iniziale delle anagrafiche già presenti nel verticale al
momento dell\'attivazione di DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test4Renderer'
        ),
        6 => array(
            'id' => 6,
            'alias' => 'REGISTRO-06',
            'name' => 'Aggiornamento sincrono gruppi: inserimento, modifica, rimozione, aggiunta utente, rimozione utente',
            'des' => 'Verificare che alla creazione / modifica / rimozione di un gruppo del verticale, vi sia la corrispondenza sincrona in
DocER, nonchè l\'aggiunta e la rimozione di un utente da un gruppo',
            'testable' => 1,
            'customFieldsRenderer' => 'test7Renderer',
            'defaultValues' => array(
                'test6_firstname' => 'Mario',
                'test6_lastname' => 'Rossi',
                'test6_updateFirstname' => 'Luca',
                'test6_updateLastname' => 'Bianchi',
            )
        ),
        7 => array(
            'id' => 7,
            'alias' => 'REGISTRO-INT-1008',
            'name' => 'Aggiornamento Sincrono Gruppi ed Utenti: rimozione cumulativa',
            'des' => 'Verificare che alla storicizzazione di una UO con più soggetti associati, vengano sincronizzati sia il Gruppo che gli Utenti in DocER',
            'testable' => 1,
            'hide' => 1,
            'internal' => 1,
            'customFieldsRenderer' => 'test7Renderer'
        ),
        8 => array(
            'id' => 8,
            'alias' => 'REGISTRO-05',
            'name' => 'Allineamento iniziale sincrono dei documenti già presenti',
            'des' => 'Verificare il corretto funzionamento dell\'utility di allineamento iniziale dei documenti già presenti nel verticale al momento dell\'attivazione di DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test8Renderer',
            'defaultValues' => array(
                'test8_TYPE_ID' => 'GENERICO'
            )
        ),
        9 => array(
            'id' => 9,
            'alias' => 'REGISTRO-INT-1009',
            'name' => 'Riversamento dei documenti nel repository documentale DocER',
            'des' => 'Verificare il corretto riversamento dei documenti nel repository documentale DocER.',
            'testable' => 0,
            'internal' => 1,
            'hide' => 1
        ),
        10 => array(
            'id' => 10,
            'alias' => 'REGISTRO-10',
            'name' => 'Riversamento dei documenti riservati nel repository documentale DocER',
            'des' => 'Verificare il corretto riversamento dei documenti riservati nel repository documentale DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test10Renderer',
            'defaultValues' => array(
                'test10_GROUP_ID' => self::DEFAULT_GRUPPO_UTENTI,
                'test10_GROUP_ID_PERMISSION' => 0,
                'test10_USER_ID' => self::DEFAULT_UTENTE,
                'test10_USER_ID_PERMISSION' => 1
            )
        ),
        11 => array(
            'id' => 11,
            'alias' => 'REGISTRO-INT-1010',
            'name' => 'Riversamento dei documenti nel repository documentale DocER per documenti creati in stato "non riservato" e modificati in "riservati"',
            'des' => 'Verificare il corretto riversamento dei documenti nel repository documentale DocER per documenti non riservati che lo diventano',
            'testable' => 0,
            'internal' => 1,
            'hide' => 1
        ),
        12 => array(
            'id' => 12,
            'alias' => 'REGISTRO-INT-1011',
            'name' => 'Riversamento dei documenti nel repository documentale DocER con eccezione',
            'des' => 'Se viene indicata una tipologia documentale NON PRESENTE in DocER il test deve restituire un\'eccezione con messaggio di errore esplicativo.',
            'testable' => 0,
            'internal' => 1,
            'hide' => 1
        ),
        13 => array(
            'id' => 13,
            'alias' => 'REGISTRO-08',
            'name' => 'Gestione dei titolari',
            'des' => 'Verificare la corretta interrogazione da parte del verticale dei titolari già presenti in DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test13Renderer',
            'defaultValues' => array(
                'test13_CLASSIFICA' => 'TIT_TEST1',
                'test13_COD_ENTE' => 'C_F632',
                'test13_COD_AOO' => 'C_F632',
            )
        ),
        14 => array(
            'id' => 14,
            'alias' => 'REGISTRO-09',
            'name' => 'Gestione dei fascicoli (interrogazione)',
            'des' => 'Verificare la corretta interrogazione da parte del verticale di fascicoli già presenti in DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test14Renderer',
            'defaultValues' => array(
                'test14_CLASSIFICA' => 'TIT_TEST1',
                'test14_COD_ENTE' => 'C_F632',
                'test14_COD_AOO' => 'C_F632',
                'test14_PROGR_FASCICOLO' => 1,
                'test14_ANNO_FASCICOLO' => 2017
            )
        ),
        15 => array(
            'id' => 15,
            'alias' => 'REGISTRO-09/a',
            'name' => 'Creazione di fascicoli',
            'des' => 'Verificare la corretta creazione da parte del verticale di fascicoli con relativo riversamento sul sistema DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test15Renderer',
            'defaultValues' => array(
                'test15_ANNO_FASCICOLO' => 2017,
                'test15_CLASSIFICA' => 'TIT_TEST1',
                'test15_COD_AOO' => self::DEFAULT_CODICE_AOO,
                'test15_COD_ENTE' => self::DEFAULT_CODICE_ENTE,
                'test15_DES_FASCICOLO' => "prova inserimento fascicolo",
                'test15_DATA_APERTURA' => "2017-01-01",
                'test15_CF_PERSONA' => "PRGLNZDKDTFPEMFD",
                'test15_CF_AZIENDA' => "1",
                'test15_ID_PROC' => "0001",
                'test15_ID_IMMOBILE' => "123456789"
            )
        ),
        16 => array(
            'id' => 16,
            'alias' => 'REGISTRO-11',
            'name' => 'Richieste di fascicolazione',
            'des' => 'Verificare la corretta fascicolazione su DocER da parte del verticale per i documenti già riversati su DocER in precedenza',
            'testable' => 1,
            'customFieldsRenderer' => 'test16Renderer',
            'defaultValues' => array(
                'test16_PROGR_FASCICOLO' => 7,
                'test16_ANNO_FASCICOLO' => 2017
            )
        ),
        17 => array(
            'id' => 17,
            'alias' => 'REGISTRO-12',
            'name' => 'Richieste di protocollazione',
            'des' => 'Verificare la corretta protocollazione su DocER da parte del verticale per i documenti già riversati su DocER in precedenza',
            'testable' => 1,
            'customFieldsRenderer' => 'test17Renderer',
            'defaultValues' => array(
                'test17_TIPO_PROTOCOLLAZIONE' => "ND",
                'test17_NUM_PG' => 99999,
                'test17_REGISTRO_PG' => "Registro di protocollo",
                'test17_DATA_PG' => "2017-11-08",
                'test17_TIPO_FIRMA' => "NF",
            )
        ),
        18 => array(
            'id' => 18,
            'alias' => 'REGISTRO-INT-1012',
            'name' => 'Richieste di protocollazione dei documenti con eccezione',
            'des' => 'Verificare l\'errore di protocollazione per i documenti già riversati su DocER in assenza dei requisiti minimi e restituzione di un messaggio di errore esplicativo',
            'testable' => 1,
            'hide' => 1,
            'internal' => 1,
            'customFieldsRenderer' => 'test18Renderer',
            'defaultValues' => array(
                'test18_DOCID' => 330,
                'test18_TIPO_PROTOCOLLAZIONE' => "I",
                'test18_NUM_PG' => 99999,
                'test18_REGISTRO_PG' => "Registro di protocollo",
                'test18_DATA_PG' => "2017-11-08",
                'test18_TIPO_FIRMA' => "NF",
            )
        ),
        19 => array(
            'id' => 19,
            'alias' => 'REGISTRO-13',
            'name' => 'Ricerca e Visualizzazione dei documenti archiviati in DocER',
            'des' => 'Verificare, nel verticale, la corretta visualizzazione dei documenti presenti in DocER con la possibilità di allegare il relativo file ad un documento già presente sul verticale stesso.',
            'testable' => 1,
            'customFieldsRenderer' => 'test19Renderer',
        ),
        20 => array(
            'id' => 20,
            'alias' => 'REGISTRO-15',
            'name' => 'Annullamento di una registrazione particolare',
            'des' => 'Verificare la possibilità ed il riversamento in DOCER dell\'annullamento della registrazione particolare',
            'testable' => 1,
            'customFieldsRenderer' => 'test20Renderer',
        ),
        21 => array(
            'id' => 21,
            'alias' => 'REGISTRO-14',
            'name' => 'Richiesta di registrazione particolare',
            'des' => 'Verificare il corretto funzionamento della richiesta di registrazione particolare ad un registro gestito da un altro
verticale di registro',
            'testable' => 1,
            'customFieldsRenderer' => 'test21Renderer',
//            'defaultValues' => array(
//                'test21_DOCID' => 321
//                'test21_ID_REGISTRO' => "Registro di protocollo",
//                'test21_N_REGISTRAZ' => 99999,
//                'test21_D_REGISTRAZ' => "2017-11-08",
//                'test21_TIPO_FIRMA' => "NF",
//            )
        ),
        22 => array(
            'id' => 22,
            'alias' => 'REGISTRO-INT-1004',
            'name' => 'Provider di registrazione particolare di documento già registrato',
            'des' => 'Utilizzo del Provider di Registrazione particolare per verificare la restituzione dei dati di registrazione di un documento già registrato',
            'testable' => 1,
            'internal' => 1,
            'hide' => 1
        ),
        23 => array(
            'id' => 23,
            'alias' => 'REGISTRO-INT-1005',
            'name' => 'Provider di registrazione particolare - controllo dati in input',
            'des' => 'Verificare il controllo dei dati obbligatori (con restituzione di un messaggio di errore esplicativo in caso negativo) del servizio di registrazione particolare',
            'testable' => 1,
            'internal' => 1,
            'hide' => 1
        ),
        24 => array(
            'id' => 24,
            'alias' => 'REGISTRO-INT-1006',
            'name' => 'Provider di registrazione particolare - controllo diritti utente',
            'des' => 'Verificare il controllo dei diritti dell\'utente che accede al servizio di registrazione particolare',
            'testable' => 1,
            'internal' => 1,
            'hide' => 1
        ),
        25 => array(
            'id' => 25,
            'alias' => 'REGISTRO-INT-1007',
            'name' => 'Provider di registrazione particolare - esecuzione registrazione particolare',
            'des' => 'Esecuzione della registrazione particolare di un documento \'da registrare\'',
            'testable' => 1,
            'internal' => 1,
            'hide' => 1
        ),
        26 => array(
            'id' => 26,
            'alias' => 'REGISTRO-07',
            'name' => 'Aggiornamento sincrono utenti: inserimento, modifica, rimozione',
            'des' => 'Verificare che alla creazione / modifica / rimozione di un utente del verticale, vi sia la corrispondenza sincrona in
DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test6Renderer',
            'defaultValues' => array(
                'test6_firstname' => 'Mario',
                'test6_lastname' => 'Rossi',
                'test6_updateFirstname' => 'Luca',
                'test6_updateLastname' => 'Bianchi',
            )
        //'customFieldsRenderer' => 'test26Renderer',
//            'defaultValues' => array(
//                'test6_firstname' => 'Mario',
//                'test6_lastname' => 'Rossi',
//                'test6_updateFirstname' => 'Luca',
//                'test6_updateLastname' => 'Bianchi',
//            )
        ),
        27 => array(
            'id' => 27,
            'alias' => 'REGISTRO-09/b',
            'name' => 'Creazione di sottofascicoli',
            'des' => 'Verificare la corretta creazione da parte del verticale di sottofascicoli con relativo riversamento sul sistema DocER',
            'testable' => 1,
            'customFieldsRenderer' => 'test27Renderer',
            'defaultValues' => array(
                'test27_PARENT_PROGR_FASCICOLO' => 1,
                'test27_ANNO_FASCICOLO' => 2017,
                'test27_CLASSIFICA' => 'TIT_TEST1',
                'test27_COD_AOO' => self::DEFAULT_CODICE_AOO,
                'test27_COD_ENTE' => self::DEFAULT_CODICE_ENTE,
                'test27_DES_FASCICOLO' => "prova inserimento sottofascicolo",
                'test27_DATA_APERTURA' => "2017-01-01",
                'test27_CF_PERSONA' => "PRGLNZDKDTFPEMFD",
                'test27_CF_AZIENDA' => "1",
                'test27_ID_PROC' => "0001",
                'test27_ID_IMMOBILE' => "123456789"
            )
        ),
        28 => array(
            'id' => 28,
            'alias' => 'REGISTRO-16',
            'name' => 'Provider - Registrazione particolare',
            'des' => 'Verificare la corretta esecuzione della registrazione particolare di un documento da parte del provider',
            'testable' => 0
        ),
        29 => array(
            'id' => 29,
            'alias' => 'REGISTRO-17',
            'name' => 'Provider - Registrazione particolare di documenti con workflow di firma annesso',
            'des' => 'Verificare il corretto funzionamento del workflow di firma annesso da parte del provider, e il conseguente
versionamento',
            'testable' => 0
        ),
        30 => array(
            'id' => 30,
            'alias' => 'REGISTRO-18',
            'name' => 'Sincronizzazione delle anagrafiche custom',
            'des' => 'Verificare la corretta creazione/modifica da parte del verticale di anagrafiche custom con relativo riversamento sul
sistema DocER',
            'testable' => 0
        ),
        31 => array(
            'id' => 31,
            'alias' => 'REGISTRO-19',
            'name' => 'Gestione dei documenti riservati',
            'des' => 'Verificare la corretta gestione della visibilità dei documenti riservati',
            'testable' => 0
        ),
        32 => array(
            'id' => 32,
            'alias' => 'REGISTRO-20',
            'name' => 'Richiesta invio PEC',
            'des' => 'Verificare la possibilità di richiedere l\'invio PEC di documenti protocollati in uscita',
            'testable' => 1
        ),
        33 => array(
            'id' => 33,
            'alias' => 'REGISTRO-13/a',
            'name' => 'Download documento',
            'des' => 'Effettua il download del documento specificato',
            'testable' => 1,
            'customFieldsRenderer' => 'test33Renderer'
        ),
        1000 => array(
            'id' => 1000,
            'alias' => 'REGISTRO-INT-1000',
            'name' => 'Lettura dati ente',
            'des' => 'Effettua la lettura dei dati dell\'Ente utilizzando il web service getEnte',
            'testable' => 1,
            'internal' => 1
        ),
        1001 => array(
            'id' => 1001,
            'alias' => 'REGISTRO-INT-1001',
            'name' => 'Lettura dati AOO',
            'des' => 'Effettua la lettura dei dati dell\'AOO utilizzando il web service getAOO',
            'testable' => 1,
            'internal' => 1,
            'customFieldsRenderer' => 'test1001Renderer',
            'defaultValues' => array(
                'test1001_COD_ENTE' => 'C_F632',
                'test1001_COD_AOO' => 'C_F632',
            )
        ),
        1002 => array(
            'id' => 1002,
            'alias' => 'REGISTRO-INT-1002',
            'name' => 'Ricerca Anagrafiche',
            'des' => 'Effettua la ricerca delle anagrafiche utilizzando il web service searchAnagrafiche',
            'testable' => 1,
            'internal' => 1,
            'customFieldsRenderer' => 'test1002Renderer',
        ),
        1003 => array(
            'id' => 1003,
            'alias' => 'REGISTRO-INT-1003',
            'name' => 'Login',
            'des' => 'Verificare il normale accesso di un utente standard',
            'testable' => 1,
            'internal' => 1,
            'customFieldsRenderer' => 'test1003Renderer',
            'defaultValues' => array(
                'login_username' => 'admin_potenzapicena',
                'login_password' => '',
                'login_codiceEnte' => 'C_F632',
                'login_application' => '',
            )
        ),
    );

    function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbDocerTestConsole';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);

        $this->searchCriteria = App::$utente->getKey($this->nameForm . '_searchCriteria');
        if ($this->searchCriteria === null) {
            $this->searchCriteria = array();
        }
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_searchCriteria', $this->searchCriteria);
        }
    }

    protected function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_searchCriteria');
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function parseEvent() {
        switch ($_POST['event']) {
            case 'openform':
                $this->initialize();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                    default:
                        if (preg_match('/.*_(.*)_info/', $_POST['id'], $matches) === 1) {
                            $test = $matches[1];
                            $this->info($test);
                            break;
                        }
                        if (preg_match('/.*_(.*)_setDefaultValues/', $_POST['id'], $matches) === 1) {
                            $test = $matches[1];
                            $this->setDefaultValues($test);
                            break;
                        }
                        if (preg_match('/.*_(.*)_executeTest/', $_POST['id'], $matches) === 1) {
                            $test = $matches[1];
                            $this->currentTestId = $test;
                            $testParamsKeys = array_filter(array_keys($_POST), array($this, 'filterByTestId'));
                            $testParams = array();
                            foreach ($testParamsKeys as $key) {
                                $tokens = explode(self::INNER_FORM . '_', $key);
                                $sanitizedKey = $tokens[1];
                                $tokens = explode('_' . $test, $sanitizedKey);
                                $sanitizedKey = $tokens[0];
                                $testParams[$sanitizedKey] = $_POST[$key];
                            }
                            $this->test($test, $testParams);
                            break;
                        }
                        if (preg_match('/.*_(.*)_detailsShow/', $_POST['id'], $matches) === 1) {
                            $test = $matches[1];
                            $this->showDetails($test);
                            break;
                        }
                        if (preg_match('/.*_(.*)_details_hide/', $_POST['id'], $matches) === 1) {
                            $test = $matches[1];
                            $this->hideDetails($test);
                            break;
                        }
                }
            case 'addGridRow':
                switch ($_POST['id']) {
                    case self::INNER_FORM . '_test1002_GRID_CRITERIA_1002':
                        $this->addNewSearchCriteria();
                        $this->loadGridSearchCriteria($_POST['id']);
                        break;
                    case self::INNER_FORM . '_test19_GRID_CRITERIA_19':
                        $this->addNewSearchCriteria();
                        $this->loadGridSearchCriteria($_POST['id']);
                        break;
                }

            case 'delGridRow':
                switch ($_POST['id']) {
                    case self::INNER_FORM . '_test1002_GRID_CRITERIA_1002':
                        $this->deleteSearchCriteria();
                        $this->loadGridSearchCriteria($_POST['id']);
                        break;
                    case self::INNER_FORM . '_test19_GRID_CRITERIA_19':
                        $this->deleteSearchCriteria();
                        $this->loadGridSearchCriteria($_POST['id']);
                        break;
                }
            case 'afterSaveCell':
                switch ($_POST['id']) {
                    case self::INNER_FORM . '_test1002_GRID_CRITERIA_1002':
                        $this->syncSearchCriteria();
                        break;
                    case self::INNER_FORM . '_test19_GRID_CRITERIA_19':
                        $this->syncSearchCriteria();
                        break;
                }
        }
    }

    private function initialize() {
        usort($this->TEST_DEF, array($this, "cmp"));

        foreach ($this->TEST_DEF as $test) {
            if (!isset($test['hide']) || (isset($test['hide']) && $test['hide'] !== 1)) {
                $this->buildRow($test);
            }
        }
    }

    private function buildRow($test) {
        $testId = self::INNER_FORM . '_' . $test['id'];
        itaLib::openInner(self::INNER_FORM, '', true, $this->nameForm . '_workSpace', '', '', $testId);

        // Mostra il pulsante di test solo se il test è eseguibile
        if ($test['testable'] === 1) {
            Out::show($testId . '_executeTest');
        } else {
            Out::hide($testId . '_executeTest');
        }

        if (array_key_exists('internal', $test) && $test['internal'] === 1) {
            Out::css($testId . '_test', 'border', '1px solid #000');
        }

        // Mostra pulsante per impostazione valori di default
        if (array_key_exists('defaultValues', $test)) {
            Out::show($testId . '_setDefaultValues');
        } else {
            Out::hide($testId . '_setDefaultValues');
        }

        Out::hide($testId . '_detailsShow');
        Out::hide($testId . '_detailsDiv');

        Out::html($testId . '_name', $test['alias'] . ' - ' . $test['name']);

        // Custom Fields
        if (array_key_exists('customFieldsRenderer', $test)) {
            call_user_func_array(array($this, $test['customFieldsRenderer']), array($test['id'], $test['id'] . '_customFields'));
            Out::css($testId . '_customFields', 'padding', '5px 10px');
            Out::css($testId . '_customFields', 'margin-top', '15px');
        }
    }

    private function info($testId) {
        Out::msgInfo("DESCRIZIONE TEST", $this->TEST_DEF[$testId]['des']);
    }

    private function setDefaultValues($testId) {
        $defaultValues = $this->TEST_DEF[$testId]['defaultValues'];
        foreach ($defaultValues as $key => $value) {
            Out::valore(self::INNER_FORM . '_' . $key . '_' . $testId, $value);
        }
    }

    private function test($testId, $testParams) {
        $test = $this->TEST_DEF[$testId];
        $testId = self::INNER_FORM . '_' . $test['id'];

        // Esegue test
        $executorName = 'test' . $test['id'] . 'Executor';
        $testResult = call_user_func_array(array($this, $executorName), array($test, $testParams));

        // Imposta layout interfaccia in funzione del risultato         
        Out::css($testId . '_detailsShow_lbl', 'padding', '0 15px');
        if ($testResult['result'] === true) {
            Out::html($testId . '_detailsShow_lbl', 'OK');
            Out::css($testId . '_detailsShow_lbl', 'background-color', '#98FB98');
            Out::css($testId . '_details_message', 'color', '#006400');
        } else {
            Out::html($testId . '_detailsShow_lbl', 'KO');
            Out::css($testId . '_detailsShow_lbl', 'background-color', '#FFA07A');
            Out::css($testId . '_details_message', 'color', '#F00');
        }

        Out::html($testId . '_details_message', $testResult['message']);
        Out::show($testId . '_detailsShow');
    }

    private function showDetails($testId) {
        Out::show(self::INNER_FORM . '_' . $testId . '_detailsDiv');
    }

    private function hideDetails($testId) {
        Out::hide(self::INNER_FORM . '_' . $testId . '_detailsDiv');
    }

    private function filterByTestId($item) {
        return $this->startsWith($item, self::INNER_FORM) && $this->endsWith($item, $this->currentTestId);
    }

    private function startsWith($haystack, $needle) {
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== false;
    }

    private function endsWith($haystack, $needle) {
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    private function test1Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'ldap_host_' . $testId,
            'label' => array('text' => 'LDAP Host', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 80
            )
        );
        $fields[] = array(
            'type' => 'ita-number',
            'id' => 'ldap_port_' . $testId,
            'label' => array('text' => 'LDAP Port', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 10
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'ldap_dn_' . $testId,
            'label' => array('text' => 'LDAP DN', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 80
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'ldap_username_' . $testId,
            'label' => array('text' => 'Username', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'ldap_password_' . $testId,
            'label' => array('text' => 'Password', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test2Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test2_codiceEnte_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 80
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test2_codiceAOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 80
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test3Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test3_groupId_' . $testId,
            'label' => array('text' => 'ID Gruppo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test4Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test4_classifica_' . $testId,
            'label' => array('text' => 'Classifica', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test4_parent_classifica_' . $testId,
            'label' => array('text' => 'Parent Classifica', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test4_des_titolario_' . $testId,
            'label' => array('text' => 'Descrizione Titolario', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test1003Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'login_username_' . $testId,
            'label' => array('text' => 'Username', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'login_password_' . $testId,
            'label' => array('text' => 'Password', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'login_codiceEnte_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'login_application_' . $testId,
            'label' => array('text' => 'Applicazione', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test6Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_userId_' . $testId,
            'label' => array('text' => '(INS) ID Utente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_firstname_' . $testId,
            'label' => array('text' => '(INS) First Name', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_lastname_' . $testId,
            'label' => array('text' => '(INS) Last Name', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_updateUserId_' . $testId,
            'label' => array('text' => '(UPD) ID Utente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_updateFirstname_' . $testId,
            'label' => array('text' => '(UPD) First Name', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_updateLastname_' . $testId,
            'label' => array('text' => '(UPD) Last Name', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test6_deleteUserId_' . $testId,
            'label' => array('text' => '(DEL) ID Utente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test7Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test7_gruppoId_' . $testId,
            'label' => array('text' => 'id Gruppo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test8Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test8_TYPE_ID_' . $testId,
            'label' => array('text' => 'TYPE_ID', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test8_DOCNAME_' . $testId,
            'label' => array('text' => 'Nome documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test8_path_' . $testId,
            'label' => array('text' => 'Path', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 80
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test10Renderer($testId, $container) {
        $fields = array();

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test10_DOC_ID_' . $testId,
            'label' => array('text' => 'DOC_ID documento a cui assegnare i permessi', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test10_GROUP_ID_' . $testId,
            'label' => array('text' => 'Id del gruppo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test10_GROUP_ID_PERMISSION_' . $testId,
            'label' => array('text' => 'valore permesso da assocaire al gruppo 0/1/2 ', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test10_USER_ID_' . $testId,
            'label' => array('text' => 'Id del utente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test10_USER_ID_PERMISSION_' . $testId,
            'label' => array('text' => 'valore permesso da associare utente 0/1/2 ', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test13Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test13_CLASSIFICA_' . $testId,
            'label' => array('text' => 'Classifica', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test13_COD_ENTE_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test13_COD_AOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test14Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test14_PROGR_FASCICOLO_' . $testId,
            'label' => array('text' => 'Progr. Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test14_ANNO_FASCICOLO_' . $testId,
            'label' => array('text' => 'Anno Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test14_CLASSIFICA_' . $testId,
            'label' => array('text' => 'Classifica', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test14_COD_ENTE_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test14_COD_AOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test15Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_PROGR_FASCICOLO_' . $testId,
            'label' => array('text' => 'Progr. Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_ANNO_FASCICOLO_' . $testId,
            'label' => array('text' => 'Anno Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_CLASSIFICA_' . $testId,
            'label' => array('text' => 'Classifica della voce Titolario padre', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_COD_ENTE_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_COD_AOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_DES_FASCICOLO_' . $testId,
            'label' => array('text' => 'Descrizione fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_DATA_APERTURA_' . $testId,
            'label' => array('text' => 'Data apertura fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_DATA_CHIUSURA_' . $testId,
            'label' => array('text' => 'Data chiusura fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_CF_PERSONA_' . $testId,
            'label' => array('text' => 'Codice fiscale della persona di riferimento per il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_CF_AZIENDA_' . $testId,
            'label' => array('text' => 'Codice fiscale dell\'azienda di riferimento del fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_ID_PROC_' . $testId,
            'label' => array('text' => 'Id del procedimento a cui si riferisce il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test15_ID_IMMOBILE_' . $testId,
            'label' => array('text' => 'Id dell\'immobile a cui si riferisce il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test16Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test16_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test16_PROGR_FASCICOLO_' . $testId,
            'label' => array('text' => 'Progr. Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test16_ANNO_FASCICOLO_' . $testId,
            'label' => array('text' => 'Anno fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );


        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test17Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_TIPO_PROTOCOLLAZIONE_' . $testId,
            'label' => array('text' => 'Tipo protocollazione E,I,U,ND', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_NUM_PG_' . $testId,
            'label' => array('text' => 'Numero di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_REGISTRO_PG_' . $testId,
            'label' => array('text' => 'Registro di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_DATA_PG_' . $testId,
            'label' => array('text' => 'Data di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test17_TIPO_FIRMA_' . $testId,
            'label' => array('text' => 'Tipo firma', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );


        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test18Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_TIPO_PROTOCOLLAZIONE_' . $testId,
            'label' => array('text' => 'Tipo protocollazione E,I,U,ND', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_NUM_PG_' . $testId,
            'label' => array('text' => 'Numero di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_REGISTRO_PG_' . $testId,
            'label' => array('text' => 'Registro di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_DATA_PG_' . $testId,
            'label' => array('text' => 'Data di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test18_TIPO_FIRMA_' . $testId,
            'label' => array('text' => 'Tipo firma', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );


        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    //Aggiunge la grid libera per cercare i metadati sui documenti 
    private function test19Renderer($testId, $container) {
        $gridName = 'test19_GRID_CRITERIA_' . $testId;
        $fields = array();

        $fields[] = array(
            'type' => 'jqgrid',
            'id' => $gridName,
            'newline' => 1,
            'columns' => array(
                array('id' => 'KEY', 'label' => 'Chiave', 'width' => "100px", 'class' => '{editable: true,cellEdit:true}'),
                array('id' => 'VALUE', 'label' => 'Valore', 'width' => "100px", 'class' => '{editable: true,cellEdit:true}')
            ),
            'properties' => array('width' => '700', 'rowNum' => '10', 'caption' => "'Parametri'", 'readerId' => "'id'", 'navGrid' => 'true', 'navButtonAdd' => 'true', 'navButtonDel' => 'true', 'resizeToParent' => 'true', 'cellEdit' => 'true')
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);

        // Carica griglia        
        $this->loadGridSearchCriteria($gridName);
    }
    
    private function test20Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test20_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );        
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test20_M_ANNULL_REGISTRAZ_' . $testId,
            'label' => array('text' => 'Motivo annullamento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test20_P_ANNULL_REGISTRAZ_' . $testId,
            'label' => array('text' => 'Provved. annullamento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        
        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }
    
    private function test21Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test21_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test21_ID_REGISTRO_' . $testId,
            'label' => array('text' => 'Identificativo del registro particolare assegnato al documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test21_N_REGISTRAZ_' . $testId,
            'label' => array('text' => 'Numero di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test21_D_REGISTRAZ_' . $testId,
            'label' => array('text' => 'Data di Protocollo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test21_TIPO_FIRMA_' . $testId,
            'label' => array('text' => 'Tipo firma', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test27Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_PROGR_FASCICOLO_' . $testId,
            'label' => array('text' => 'Progr. Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_PARENT_PROGR_FASCICOLO_' . $testId,
            'label' => array('text' => 'Progr. Fasc. Padre', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_ANNO_FASCICOLO_' . $testId,
            'label' => array('text' => 'Anno Fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_CLASSIFICA_' . $testId,
            'label' => array('text' => 'Classifica della voce Titolario padre', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_COD_ENTE_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_COD_AOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_DES_FASCICOLO_' . $testId,
            'label' => array('text' => 'Descrizione fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_DATA_APERTURA_' . $testId,
            'label' => array('text' => 'Data apertura fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_DATA_CHIUSURA_' . $testId,
            'label' => array('text' => 'Data chiusura fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_CF_PERSONA_' . $testId,
            'label' => array('text' => 'Codice fiscale della persona di riferimento per il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_CF_AZIENDA_' . $testId,
            'label' => array('text' => 'Codice fiscale dell\'azienda di riferimento del fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_ID_PROC_' . $testId,
            'label' => array('text' => 'Id del procedimento a cui si riferisce il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test27_ID_IMMOBILE_' . $testId,
            'label' => array('text' => 'Id dell\'immobile a cui si riferisce il fascicolo', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test33Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test33_DOCID_' . $testId,
            'label' => array('text' => 'ID Documento', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test1001Renderer($testId, $container) {
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test1001_COD_ENTE_' . $testId,
            'label' => array('text' => 'Codice Ente', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test1001_COD_AOO_' . $testId,
            'label' => array('text' => 'Codice AOO', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 20
            )
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);
    }

    private function test1002Renderer($testId, $container) {
        $gridName = 'test1002_GRID_CRITERIA_' . $testId;
        $fields = array();
        $fields[] = array(
            'type' => 'ita-edit',
            'id' => 'test1002_TYPE_' . $testId,
            'label' => array('text' => 'Tipo Anagrafica', 'position' => 'sx', 'style' => 'width:250px'),
            'newline' => 1,
            'properties' => array(
                'size' => 40
            )
        );
        $fields[] = array(
            'type' => 'jqgrid',
            'id' => $gridName,
            'newline' => 1,
            'columns' => array(
                array('id' => 'KEY', 'label' => 'Chiave', 'width' => "100px", 'class' => '{editable: true,cellEdit:true}'),
                array('id' => 'VALUE', 'label' => 'Valore', 'width' => "100px", 'class' => '{editable: true,cellEdit:true}')
            ),
            'properties' => array('width' => '700', 'rowNum' => '10', 'caption' => "'Parametri'", 'readerId' => "'id'", 'navGrid' => 'true', 'navButtonAdd' => 'true', 'navButtonDel' => 'true', 'resizeToParent' => 'true', 'cellEdit' => 'true')
        );

        cwbLibHtml::componentiDinamici(self::INNER_FORM, $container, $fields);
        cwbLibHtml::attivaJSElemento(self::INNER_FORM . '_' . $container);

        // Carica griglia        
        $this->loadGridSearchCriteria($gridName);
    }

    // Verifica configurazione iniziale LDAP
    private function test1Executor($test, $params) {
        $ldapParams = array(
            "LdapHost" => $params['ldap_host'],
            "LdapPort" => $params['ldap_port'],
            "LdapBaseDN" => $params['ldap_dn']
        );
        $ldap = itaLdap::getLdapAuthenticator($ldapParams);
        $authResult = $ldap->authenticate($params['ldap_username'], $params['ldap_password']);
        $testResult = array(
            'result' => $authResult,
            'message' => ($authResult ? 'Autenticazione LDAP OK per utente: ' . $params['ldap_username'] : $ldap->getLastErrorMessage())
        );
        return $testResult;
    }

    // Verifica configurazione iniziale
    private function test2Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Legge dati Ente
        $token = $auth->getResult();
        $resultTest = $docer->ws_getDocumentTypesByAOO(array(
            'TOKEN' => $token,
            'COD_ENTE' => $params['test2_codiceEnte'],
            'COD_AOO' => $params['test2_codiceAOO'],
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Primo Riversamento Gruppi - Utenti in maniera sincrona
    private function test3Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Legge dati Ente
        $token = $auth->getResult();

        // Imposta dati mock        
        $users = array();
        $userIds = array();
        for ($i = 1; $i < 10; $i++) {
            $id = 'TEST_GROUP_' . $params['test3_groupId'] . '_USER_' . $i;
            $userIds[] = $id;
            $users[] = array(
                array(
                    'key' => 'USER_ID',
                    'value' => $id
                ),
                array(
                    'key' => 'USER_PASSWORD',
                    'value' => 'changeit'
                ),
                array(
                    'key' => 'FULL_NAME',
                    'value' => 'TEST_GROUP_' . $params['test3_groupId'] . '_USER_' . $i . ' FULLNAME'
                ),
                array(
                    'key' => 'COD_ENTE',
                    'value' => self::DEFAULT_CODICE_ENTE
                ),
                array(
                    'key' => 'COD_AOO',
                    'value' => self::DEFAULT_CODICE_AOO
                ),
                array(
                    'key' => 'FIRST_NAME',
                    'value' => 'TEST_GROUP_' . $params['test3_groupId'] . '_USER_' . $i . ' FIRSTNAME'
                ),
                array(
                    'key' => 'LAST_NAME',
                    'value' => 'TEST_GROUP_' . $params['test3_groupId'] . '_USER_' . $i . ' LASTNAME'
                )
            );
        }
        $mockData = array(
            'GROUPINFO' => array(
                array(
                    'key' => 'GROUP_ID',
                    'value' => 'TG_' . $params['test3_groupId']
                ),
                array(
                    'key' => 'GROUP_NAME',
                    'value' => 'TEST_GROUP_' . $params['test3_groupId']
                ),
                array(
                    'key' => 'GRUPPO_STRUTTURA',
                    'value' => true
                )
            ),
            'users' => $users
        );

        // Inserimento gruppo
        $resultTest = $docer->ws_createGroup(array(
            'TOKEN' => $token,
            'GROUPINFO' => $mockData['GROUPINFO']
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // Inserimento utenti
        foreach ($mockData['users'] as $userInfo) {
            $resultTest = $docer->ws_createUser(array(
                'TOKEN' => $token,
                'USERINFO' => $userInfo
            ));
            if (!$resultTest) {
                return $this->handleErrorResult($docer);
            }
        }

        // Associa utenti a gruppo        
        $resultTest = $docer->ws_setUsersOfGroup(array(
            'TOKEN' => $token,
            'GROUPID' => 'TG_' . $params['test3_groupId'],
            'USERS' => $userIds
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResultData($mockData, 'Dati mock inseriti:')
        );
    }

    // Primo Riversamento Gruppi - Utenti in maniera sincrona
    private function test4Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Legge dati Ente
        $token = $auth->getResult();

        $titolarioInfo = array(
            array(
                'key' => 'CLASSIFICA',
                'value' => $params['test4_classifica'],
            ),
            array(
                'key' => 'PARENT_CLASSIFICA',
                'value' => $params['test4_parent_classifica'],
            ),
            array(
                'key' => 'COD_ENTE',
                'value' => self::DEFAULT_CODICE_ENTE
            ),
            array(
                'key' => 'COD_AOO',
                'value' => self::DEFAULT_CODICE_AOO
            ),
            array(
                'key' => 'DES_TITOLARIO',
                'value' => $params['test4_des_titolario']
            ),
            array(
                'key' => 'ENABLED',
                'value' => 'true'
            )
        );

        // Inserimento titolario
        $resultTest = $docer->ws_createTitolario(array(
            'TOKEN' => $token,
            'TITOLARIOINFO' => $titolarioInfo
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResultData($titolarioInfo, 'Dati inseriti:')
        );
    }

    // Login
    private function test1003Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();

        // login
        $result = $this->login($auth, $params);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $testResult = array(
            'result' => true,
            'message' => '<div style="width: 100%">' . $auth->getResult() . '</div>'
        );

        // se il login ha avuto esito positivo, effttua il logout        
        $result = $this->logout($auth, $auth->getResult());
        if (!$result) {
            return $this->handleErrorResult($auth);
        } else {
            $testResult['message'] .= '<div style="margin-top: 10px;"></div><span style="background-color: #FFFF00;"><b>TOKEN DESTROYED AFTER LOGOUT</b></span>';
        }

        return $testResult;
    }

    //Effettua l'inserimento dell'utente 
    private function test6Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Inserimento utente
        $token = $auth->getResult();
        $usersToInsert[] = array(
            array(
                'key' => 'USER_ID',
                'value' => $params['test6_userId'],
            ),
            array(
                'key' => 'FULL_NAME',
                'value' => $params['test6_firstname'] . " " . $params['test6_lastname']
            ),
            array(
                'key' => 'COD_ENTE',
                'value' => self::DEFAULT_CODICE_ENTE
            ),
            array(
                'key' => 'COD_AOO',
                'value' => self::DEFAULT_CODICE_AOO
            ),
            array(
                'key' => 'FIRST_NAME',
                'value' => $params['test6_firstname']
            ),
            array(
                'key' => 'LAST_NAME',
                'value' => $params['test6_lastname']
            )
        );

        // Inserimento utento specifico 
        $resultTestCreate = $docer->ws_createUser(array(
            'TOKEN' => $token,
            'USERINFO' => $usersToInsert[0]
        ));
        if (!$resultTestCreate) {
            return $this->handleErrorResult($docer);
        }

        //Aggiornamento utente  
        $usersToUpdate[] = array(
            array(
                'key' => 'FULL_NAME',
                'value' => $params['test6_updateFirstname'] . " " . $params['test6_updateLastname']
            ),
            array(
                'key' => 'LAST_NAME',
                'value' => $params['test6_updateLastname']
            )
        );
        $resultTestUpdate = $docer->ws_updateUser(array(
            'TOKEN' => $token,
            'USERID' => $params['test6_updateUserId'],
            'USERINFO' => $usersToUpdate[0]
        ));
        if (!$resultTestUpdate) {
            return $this->handleErrorResult($docer);
        }

        // Cancellazione utente 
        $resultTestDelete = $docer->ws_deleteUser(array(
            'TOKEN' => $token,
            'USERID' => $params['test6_deleteUserId'],
            'USERINFO' => $usersToUpdate[0]
        ));
        if (!$resultTestDelete) {
            return $this->handleErrorResult($docer);
        }
        $mockData = array();
        $mockData['insert'] = $usersToInsert;
        $mockData['update'] = $usersToUpdate;
        $mockData['delete'] = $params['test6_deleteUserId'];

        return array(
            'result' => $resultTestCreate && $resultTestUpdate && $resultTestDelete,
            'message' => $this->getMessageResultData($mockData, 'Dati aggiornati:')
        );
    }

    // Aggiorna la descrizione del gruppo 
    // Aggiorna la descrizione del primo utente associato al gruppo
    // Elimina l'ultimo utente associato al gruppo 
    private function test7Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }
        $token = $auth->getResult();

        $groupName = 'TEST_GROUP_UPDATE_' . date("Y-m-d H:i:s");
        $fullGroupName = $groupName . '_FULLNAME';

        $mockData = array(
            'GROUPINFO' => array(
                array(
                    'key' => 'GROUP_NAME',
                    'value' => $groupName
                ),
                array(
                    'key' => 'FULL_NAME',
                    'value' => $fullGroupName
                )
            )
        );
        //Aggiornamento info gruppo 
        $resultTestGroup = $docer->ws_updateGroup(
                array(
                    'TOKEN' => $token,
                    'GROUPID' => $params['test7_gruppoId'],
                    'GROUPINFO' => $mockData['GROUPINFO']
                )
        );

        if (!$resultTestGroup) {
            return $this->handleErrorResult($docer);
        }

        //Effettuo il caricamento degli utenti collegati al gruppo passato 
        $resultUserGrup = $docer->ws_getUsersOfGroup(
                array(
                    'TOKEN' => $token,
                    'GROUPID' => $params['test7_gruppoId']
                )
        );
        if (!$resultUserGrup) {
            return $this->handleErrorResult($auth);
        }

        $users = $docer->getResult();

        if (!empty($users)) {
            //aggiorna la descrizione dell'utente associato al gruppo
            $mockData['USERINFO'] = array(
                'key' => 'FULL_NAME',
                'value' => $users[0] . "_FULLNAME_" . date("Y-m-d H:i:s")
            );

            //Aggiornamento Utente 
            $resultUpdateUser = $docer->ws_updateUser(
                    array(
                        'TOKEN' => $token,
                        'USERID' => $users[0],
                        'USERINFO' => $mockData['USERINFO']
                    )
            );

            if (!$resultUpdateUser) {
                return $this->handleErrorResult($docer);
            }

            $userToRemove = array();
            $userToRemove[] = end($users);
            $mockData['USERTOREMOVE'] = $userToRemove;

            //Elimina l'ultimo utente associato al gruppo 
            $resultRemoveUser = $docer->ws_updateUsersOfGroup(
                    array(
                        'TOKEN' => $token,
                        'GROUPID' => $params['test7_gruppoId'],
                        'USERTOREMOVE' => $mockData['USERTOREMOVE']
                    )
            );

            if (!$resultRemoveUser) {
                return $this->handleErrorResult($docer);
            }
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }
        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResultData($mockData, 'Dati aggiornati:')
        );
    }

    // Allineamento iniziale sincrono dei documenti già presenti
    private function test8Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // Controlla esistenza file
        if (!file_exists($params['test8_path'])) {
            return array(
                'result' => false,
                'message' => 'File ' . $params['test8_path'] . ' non esistente!'
            );
        }

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Inserimento documento
        $token = $auth->getResult();
        $metadata = array(
            array(
                'key' => 'TYPE_ID',
                'value' => $params['test8_TYPE_ID']
            ),
            array(
                'key' => 'DOCNAME',
                'value' => $params['test8_DOCNAME']
            ),
            array(
                'key' => 'COD_ENTE',
                'value' => self::DEFAULT_CODICE_ENTE
            ),
            array(
                'key' => 'COD_AOO',
                'value' => self::DEFAULT_CODICE_AOO
            )
        );

        $resultTest = $docer->ws_createDocument(array(
            'TOKEN' => $token,
            'METADATA' => $metadata,
            'FILE' => $params['test8_path']
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResultSimple($docer, 'ID Documento creato in DocER: ')
        );
    }

    private function test10Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();


        $metadata = array(
            array(
                'key' => $params['test10_GROUP_ID'],
                'value' => $params['test10_GROUP_ID_PERMISSION']
            ),
            array(
                'key' => $params['test10_USER_ID'],
                'value' => $params['test10_USER_ID_PERMISSION']
            )
        );

        $resultTest = $docer->ws_setACLDocument(array(
            'TOKEN' => $token,
            'docId' => $params['test10_DOC_ID'],
            'acls' => $metadata
        ));

        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        $resultPermission = $docer->ws_getACLDocument(array(
            'TOKEN' => $token,
            'docId' => $params['test10_DOC_ID']
        ));

        if (!$resultPermission) {
            return $this->handleErrorResult($docer);
        }

        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Interrogazione di titolari
    private function test13Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Inserimento documento
        $token = $auth->getResult();
        $titolarioId = array();

        $titolarioId[] = array(
            'key' => 'CLASSIFICA',
            'value' => $params['test13_CLASSIFICA']
        );
        $titolarioId[] = array(
            'key' => 'COD_ENTE',
            'value' => $params['test13_COD_ENTE']
        );
        $titolarioId[] = array(
            'key' => 'COD_AOO',
            'value' => $params['test13_COD_AOO']
        );

        $resultTest = $docer->ws_getTitolario(array(
            'TOKEN' => $token,
            'TITOLARIOID' => $titolarioId
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Interrogazione di fascicoli
    private function test14Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Inserimento documento
        $token = $auth->getResult();
        $fascicoloId = array();
        $fascicoloId[] = array(
            'key' => 'PROGR_FASCICOLO',
            'value' => $params['test14_PROGR_FASCICOLO']
        );
        $fascicoloId[] = array(
            'key' => 'ANNO_FASCICOLO',
            'value' => $params['test14_ANNO_FASCICOLO']
        );
        $fascicoloId[] = array(
            'key' => 'CLASSIFICA',
            'value' => $params['test14_CLASSIFICA']
        );
        $fascicoloId[] = array(
            'key' => 'COD_ENTE',
            'value' => $params['test14_COD_ENTE']
        );
        $fascicoloId[] = array(
            'key' => 'COD_AOO',
            'value' => $params['test14_COD_AOO']
        );

        $resultTest = $docer->ws_getFascicolo(array(
            'TOKEN' => $token,
            'FASCICOLOID' => $fascicoloId
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    //Creazione di un fascicolo 
    private function test15Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'FASCICOLOINFO' => array(
                array(
                    'key' => 'PROGR_FASCICOLO',
                    'value' => $params['test15_PROGR_FASCICOLO']
                ),
                array(
                    'key' => 'ANNO_FASCICOLO',
                    'value' => $params['test15_ANNO_FASCICOLO']
                ),
                array(
                    'key' => 'CLASSIFICA',
                    'value' => $params['test15_CLASSIFICA']
                ),
                array(
                    'key' => 'COD_AOO',
                    'value' => $params['test15_COD_AOO']
                ),
                array(
                    'key' => 'COD_ENTE',
                    'value' => $params['test15_COD_ENTE']
                ),
                array(
                    'key' => 'DES_FASCICOLO',
                    'value' => $params['test15_DES_FASCICOLO']
                ),
                array(
                    'key' => 'DATA_APERTURA',
                    'value' => $params['test15_DATA_APERTURA']
                ),
                array(
                    'key' => 'DATA_CHIUSURA',
                    'value' => $params['test15_DATA_CHIUSURA']
                ),
                array(
                    'key' => 'CF_PERSONA',
                    'value' => $params['test15_CF_PERSONA']
                ),
                array(
                    'key' => 'CF_AZIENDA',
                    'value' => $params['test15_CF_AZIENDA']
                ),
                array(
                    'key' => 'ID_PROC',
                    'value' => $params['test15_ID_PROC']
                ),
                array(
                    'key' => 'ID_IMMOBILE',
                    'value' => $params['test15_ID_IMMOBILE']
                )
            )
        );
        $resultTest = $docer->ws_createFascicolo(array(
            'TOKEN' => $token,
            'FASCICOLOINFO' => $mockData["FASCICOLOINFO"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    //Fascicola documento
    private function test16Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'METADATA' => array(
                array(
                    'key' => 'PROGR_FASCICOLO',
                    'value' => $params['test16_PROGR_FASCICOLO']
                ),
                array(
                    'key' => 'ANNO_FASCICOLO',
                    'value' => $params['test16_ANNO_FASCICOLO']
                )
            )
        );
        $resultTest = $docer->ws_fascicolaDocumento(array(
            'TOKEN' => $token,
            'DOCID' => $params['test16_DOCID'],
            'METADATA' => $mockData["METADATA"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    //Protocolla documento
    private function test17Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'METADATA' => array(
                array(
                    'key' => 'TIPO_PROTOCOLLAZIONE',
                    'value' => $params['test17_TIPO_PROTOCOLLAZIONE']
                ),
                array(
                    'key' => 'NUM_PG',
                    'value' => $params['test17_NUM_PG']
                ),
                array(
                    'key' => 'REGISTRO_PG',
                    'value' => $params['test17_REGISTRO_PG']
                ),
                array(
                    'key' => 'DATA_PG',
                    'value' => $params['test17_DATA_PG']
                ),
                array(
                    'key' => 'TIPO_FIRMA',
                    'value' => $params['test17_TIPO_FIRMA']
                )
            )
        );
        $resultTest = $docer->ws_protocollaDocumento(array(
            'TOKEN' => $token,
            'DOCID' => $params['test17_DOCID'],
            'METADATA' => $mockData["METADATA"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    //Solleva eccezione mancanza di destinatari in caso di TipoProtocollazione =I,ND
    private function test18Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'METADATA' => array(
                array(
                    'key' => 'TIPO_PROTOCOLLAZIONE',
                    'value' => $params['test18_TIPO_PROTOCOLLAZIONE']
                ),
                array(
                    'key' => 'NUM_PG',
                    'value' => $params['test18_NUM_PG']
                ),
                array(
                    'key' => 'REGISTRO_PG',
                    'value' => $params['test18_REGISTRO_PG']
                ),
                array(
                    'key' => 'DATA_PG',
                    'value' => $params['test18_DATA_PG']
                ),
                array(
                    'key' => 'TIPO_FIRMA',
                    'value' => $params['test18_TIPO_FIRMA']
                )
            )
        );
        $resultTest = $docer->ws_protocollaDocumento(array(
            'TOKEN' => $token,
            'DOCID' => $params['test18_DOCID'],
            'METADATA' => $mockData["METADATA"]
        ));

        $messageOut = $docer->getFault();
        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => true,
            'message' => $messageOut
        );
    }

    // Ricerca documenti 
    private function test19Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $sc = array();
        foreach ($this->searchCriteria as $criteria) {
            $sc[] = array(
                'key' => $criteria['KEY'],
                'value' => $criteria['VALUE']
            );
        }
        // Ricerca anagrafiche
        $token = $auth->getResult();
        $resultSearch = $docer->ws_searchDocuments(array(
            'TOKEN' => $token,
            'SEARCHCRITERIA' => $sc
        ));
        if (!$resultSearch) {
            return $this->getMessageResult($docer);
        }
        //LISTA DOCUMENTI RICERCATI 
        $docs = $docer->getResult();

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultSearch,
            'message' => $this->getMessageResult($docer)
        );
    }
    
    // Annullamento di una registrazione particolare
    private function test20Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'METADATA' => array(                
                array(
                    'key' => 'D_ANNULL_REGISTRAZ',
                    'value' => date("Y-m-d H:i:s")
                ),
                array(
                    'key' => 'M_ANNULL_REGISTRAZ',
                    'value' => $params['test20_M_ANNULL_REGISTRAZ']
                ),
                array(
                    'key' => 'P_ANNULL_REGISTRAZ',
                    'value' => $params['test20_P_ANNULL_REGISTRAZ']
                ),
                array(
                    'key' => 'ANNULL_REGISTRAZ',
                    'value' => 'ANNULLATO'
                )                   
            )
        );
        $resultTest = $docer->ws_registraDocumento(array(
            'TOKEN' => $token,
            'DOCID' => $params['test20_DOCID'],
            'METADATA' => $mockData["METADATA"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }
    
    //Creazione di un sottofascicolo 
    private function test27Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'FASCICOLOINFO' => array(
                array(
                    'key' => 'PROGR_FASCICOLO',
                    'value' => $params['test27_PROGR_FASCICOLO']
                ),
                array(
                    'key' => 'PARENT_PROGR_FASCICOLO',
                    'value' => $params['test27_PARENT_PROGR_FASCICOLO']
                ),
                array(
                    'key' => 'ANNO_FASCICOLO',
                    'value' => $params['test27_ANNO_FASCICOLO']
                ),
                array(
                    'key' => 'CLASSIFICA',
                    'value' => $params['test27_CLASSIFICA']
                ),
                array(
                    'key' => 'COD_AOO',
                    'value' => $params['test27_COD_AOO']
                ),
                array(
                    'key' => 'COD_ENTE',
                    'value' => $params['test27_COD_ENTE']
                ),
                array(
                    'key' => 'DES_FASCICOLO',
                    'value' => $params['test27_DES_FASCICOLO']
                ),
                array(
                    'key' => 'DATA_APERTURA',
                    'value' => $params['test27_DATA_APERTURA']
                ),
                array(
                    'key' => 'DATA_CHIUSURA',
                    'value' => $params['test27_DATA_CHIUSURA']
                ),
                array(
                    'key' => 'CF_PERSONA',
                    'value' => $params['test27_CF_PERSONA']
                ),
                array(
                    'key' => 'CF_AZIENDA',
                    'value' => $params['test27_CF_AZIENDA']
                ),
                array(
                    'key' => 'ID_PROC',
                    'value' => $params['test27_ID_PROC']
                ),
                array(
                    'key' => 'ID_IMMOBILE',
                    'value' => $params['test27_ID_IMMOBILE']
                )
            )
        );
        $resultTest = $docer->ws_createFascicolo(array(
            'TOKEN' => $token,
            'FASCICOLOINFO' => $mockData["FASCICOLOINFO"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    //Creazione di un sottofascicolo 
    private function test33Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $resultTest = $docer->ws_downloadDocument(array(
            'TOKEN' => $token,
            'DOCID' => $params['test33_DOCID'],
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        $attachment = $docer->getResponseAttachments();
        $path = '/shared/downloaded.pdf';
        file_put_contents($path, $attachment[0]['data']);

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResultData($path, 'Documento scaricato:')
        );
    }

    // Lettura dati Ente
    private function test1000Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        // Legge dati Ente
        $token = $auth->getResult();
        $resultTest = $docer->ws_getEnte(array(
            'TOKEN' => $token,
            'COD_ENTE' => self::DEFAULT_CODICE_ENTE
        ));
        if (!$resultTest) {
            return $this->getMessageResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Registra documento
    private function test21Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $token = $auth->getResult();

        $mockData = array(
            'METADATA' => array(
                array(
                    'key' => 'ID_REGISTRO',
                    'value' => $params['test21_ID_REGISTRO']
                ),
                array(
                    'key' => 'N_REGISTRAZ',
                    'value' => $params['test21_N_REGISTRAZ']
                ),
                array(
                    'key' => 'D_REGISTRAZ',
                    'value' => $params['test21_D_REGISTRAZ']
                ),
                array(
                    'key' => 'TIPO_FIRMA',
                    'value' => $params['test21_TIPO_FIRMA']
                )
            )
        );
        $resultTest = $docer->ws_registraDocumento(array(
            'TOKEN' => $token,
            'DOCID' => $params['test21_DOCID'],
            'METADATA' => $mockData["METADATA"]
        ));
        if (!$resultTest) {
            return $this->handleErrorResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Lettura dati Ente
    private function test1001Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $aooId = array();
        $aooId[] = array(
            'key' => 'COD_ENTE',
            'value' => $params['test1001_COD_ENTE']
        );
        $aooId[] = array(
            'key' => 'COD_AOO',
            'value' => $params['test1001_COD_AOO']
        );

        // Legge dati Ente
        $token = $auth->getResult();
        $resultTest = $docer->ws_getAOO(array(
            'TOKEN' => $token,
            'AOOID' => $aooId
        ));
        if (!$resultTest) {
            return $this->getMessageResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    // Ricerca anagrafiche
    private function test1002Executor($test, $params) {
        $auth = itaDocerClientFactory::getIdentClient();
        $docer = itaDocerClientFactory::getGestDocClient();

        // login
        $result = $this->login($auth);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        $sc = array();
        foreach ($this->searchCriteria as $criteria) {
            $sc[] = array(
                'key' => $criteria['KEY'],
                'value' => $criteria['VALUE']
            );
        }
        // Ricerca anagrafiche
        $token = $auth->getResult();
        $resultTest = $docer->ws_searchAnagrafiche(array(
            'TOKEN' => $token,
            'TYPE' => $params['test1002_TYPE'],
            'SEARCHCRITERIA' => $sc
        ));
        if (!$resultTest) {
            return $this->getMessageResult($docer);
        }

        // logout
        $result = $this->logout($auth, $token);
        if (!$result) {
            return $this->handleErrorResult($auth);
        }

        return array(
            'result' => $resultTest,
            'message' => $this->getMessageResult($docer)
        );
    }

    private function handleErrorResult($client) {
        $errorMessage = '';
        if ($client->getFault()) {
            $errorMessage = '<span style="background-color: #FFFF00;">FAULT: </span>' . $client->getFault();
        } elseif ($client->getError()) {
            $errorMessage = '<span style="background-color: #FFFF00;">ERROR: </span>' . $client->getError();
        } else {
            $errorMessage = '<span style="background-color: #FFFF00;">ERROR</span>';
        }

        return array(
            'result' => false,
            'message' => $errorMessage
        );
    }

    private function getMessageResult($docer) {
        return '<div style="width: 100%"><pre>' . print_r($docer->getResult(), true) . '</pre></div>';
    }

    private function getMessageResultData($data, $preMsg) {
        return '<div style="width: 100%;"><div style="margin-bottom: 5px;">' . $preMsg . '</div><pre>' . print_r($data, true) . '</pre></div>';
    }

    private function getMessageResultSimple($docer, $preMsg) {
        return '<div style="width: 100%;"><div style="margin-bottom: 5px;">' . $preMsg . '<b>' . $docer->getResult() . '</b></div>';
    }

    private function login($docer, $params = null) {
        if (!$params) {
            $params = $this->TEST_DEF[1003]['defaultValues'];
        }

        $wsParams = array(
            "username" => $params['login_username'],
            "password" => $params['login_password'],
            "codiceEnte" => $params['login_codiceEnte'],
            "application" => $params['login_application']
        );
        return $docer->ws_login($wsParams);
    }

    private function logout($docer, $token) {
        $wsParams = array(
            "token" => $token
        );
        return $docer->ws_logout($wsParams);
    }

    private function addNewSearchCriteria() {
        $this->searchCriteria[] = array('KEY' => '', 'VALUE' => '');
    }

    private function deleteSearchCriteria() {
        foreach ($this->searchCriteria as $key => $value) {
            if ($value['id'] == $_POST['rowid']) {
                unset($this->searchCriteria[$key]);
                break;
            }
        }
    }

    private function loadGridSearchCriteria($gridname) {
        TableView::clearGrid($gridname);

        foreach ($this->searchCriteria as $key => $value) {
            if (!$value['id']) {
                $this->searchCriteria[$key]['id'] = uniqid($gridname, true);
            }
        }

        $ita_grid01 = new TableView($gridname, array(
            'arrayTable' => $this->searchCriteria
        ));
        $ita_grid01->setPageNum(1);
        $ita_grid01->setPageRows(999);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($gridname);
    }

    private function syncSearchCriteria() {
        foreach ($this->searchCriteria as $key => $value) {
            if ($value['id'] == $_POST['rowid']) {
                $this->searchCriteria[$key][$_POST['cellname']] = $_POST['value'];
                break;
            }
        }
    }

    static function cmp($a, $b) {
        return strcmp($a['alias'], $b['alias']);
    }

}

