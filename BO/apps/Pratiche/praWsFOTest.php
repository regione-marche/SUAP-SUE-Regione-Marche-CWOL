<?php

/**
 *
 * TEST PRAWSFO WS-CLIENT
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft snc
 * @license
 * @version    03.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praWsFrontOffice.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

function praWsFOTest() {
    $praWsFOTest = new praWsFOTest();
    $praWsFOTest->parseEvent();
    return;
}

class praWsFOTest extends itaModel {

    public $nameForm = "praWsFOTest";

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    private function setClientConfig($wsClient) {
        /* @var $WSClient praWsFrontOffice */
        $wsClient->setWebservices_uri($_POST[$this->nameForm . "_CONFIG"]["wsEndpoint"]);
        $wsClient->setWebservices_wsdl($_POST[$this->nameForm . "_CONFIG"]['wsWsdl']);
        $wsClient->setNamespace("");
        $wsClient->setTimeout(1200);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->nameForm, "", true, "desktopBody");
                Out::show($this->nameForm);
                //inizializzo i valori di configurazione della chiamata
//                include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
//                $devLib = new devLib();
//                $uri = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSENDPOINT', false);
//                $wsdl = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSWSDL', false);
//                $username = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSUSERNAME', false);
//                $password = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSPASSWORD', false);
//                $codiceAOO = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSCODICEAOO', false);
//                $codiceEnte = $devLib->getEnv_config('LEONARDOSWSCONNECTION', 'codice', 'LEONARDOWSCODICEENTE', false);
//                //
//                Out::valore($this->nameForm . "_CONFIG[wsEndpoint]", $uri['CONFIG']);
//                Out::valore($this->nameForm . "_CONFIG[wsWsdl]", $wsdl['CONFIG']);
//                Out::valore($this->nameForm . "_CONFIG[wsUser]", $username['CONFIG']);
//                Out::valore($this->nameForm . "_CONFIG[wsPassword]", $password['CONFIG']);
//                Out::valore($this->nameForm . "_CONFIG[wsCodiceAOO]", $codiceAOO['CONFIG']);
//                Out::valore($this->nameForm . "_CONFIG[wsCodiceEnte]", $codiceEnte['CONFIG']);
                //
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_callLogin':
                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);
                        $params = array(
                            'userName' => $_POST[$this->nameForm . "_CONFIG"]["wsUser"],
                            'userPassword' => $_POST[$this->nameForm . "_CONFIG"]["wsPassword"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"]
                        );

                        $wsCall = $wsClient->ws_GetItaEngineContextToken($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault in fase di prenotazione del token: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore in fase di prenotazione del token: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $Token = $wsClient->getResult();
                        Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Token . '</pre>');
                        break;
                    case $this->nameForm . '_callGetAllegatoRichiesta':
                        Out::html($this->nameForm . "_divBase64", "");
                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);
                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_token1"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'rowid' => $_POST[$this->nameForm . "_rowid"],
                        );

                        $wsCall = $wsClient->ws_getRichiestaAllegatoForRowid($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel leggere l'allegato: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore ne leggere l'allegato: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $base64 = $wsClient->getResult();
                        //file_put_contents("C:/file.log", $base64);
                        Out::html($this->nameForm . "_divBase64", $base64);
                        break;
                    case $this->nameForm . '_callSyncFascicolo':
                        $gesnum = $_POST[$this->nameForm . "_gesnum"];

                        $praLib = new praLib();

                        $proges_rec = $praLib->GetProges($gesnum, 'codice');
                        if (!$proges_rec) {
                            Out::msgInfo("Attenzione", "Non trovato il fascicolo con il numero " . $gesnum);
                            return;
                        }

                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);


                        $arrayProges = $proges_rec;
                        unset($arrayProges['ROWID']);

                        //$arrayProges['GESOGG'] = addslashes($arrayProges['GESOGG']);
                        //unset($arrayProges['GESOGG']);
//                        $arrayProges['GESNUM'] = '2019000050';
//                        $arrayProges['GESORA'] = '01:12:33';
//                        $arrayProges['GESKEY'] = 'Simone';

                        $dati = array();
                        $dati['PROGES'][0] = $arrayProges;

                        //Out::msgInfo("Dati", print_r($dati,true));
                        //return;


                        $fascicoloJason = json_encode(itaLib::utf8_encode_recursive($dati));
                        //$fascicolo = serialize(json_encode($dati, true));

                        $fascicolo = base64_encode($fascicoloJason);

//                        Out::msgInfo("Fascicolo", print_r($fascicolo,true));

                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_token2"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'fascicolo' => $fascicolo,
                        );

                        //Out::msgInfo("Parametri", print_r($params,true));
                        //return;

                        $wsCall = $wsClient->ws_SyncFascicolo($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel riportare il fascicolo: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore nel riportare il fascicolo: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $risposta = $wsClient->getResult();

                        Out::msgInfo("Risposta", $risposta);

                        break;

                    case $this->nameForm . '_callSyncPasso':
                        $propak = $_POST[$this->nameForm . "_propak"];

                        $praLib = new praLib();

                        $propas_rec = $praLib->GetPropas($propak, 'propak');
                        if (!$propas_rec) {
                            Out::msgInfo("Attenzione", "Non trovato il passo con propak = " . $propak);
                            return;
                        }

                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);


                        $arrayPropas = $propas_rec;
                        unset($arrayPropas['ROWID']);

                        //unset($arrayPropas['PROALL']);
                        //$arrayPropas['PROPAK'] = '9999999999999999999999';
                        //$arrayPropas['PROSER'] = 'Ugo';

                        $dati = array();
                        $dati['PROPAS'][0] = $arrayPropas;

//                        Out::msgInfo("Dati", print_r($dati,true));
                        //$passoJason = json_encode($dati, true);
                        //$fascicolo = serialize(json_encode($dati, true));
                        $passoJason = json_encode(itaLib::utf8_encode_recursive($dati));

                        $passo = base64_encode($passoJason);

//                        Out::msgInfo("Fascicolo", print_r($fascicolo,true));


                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenPasso"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'passo' => $passo,
                        );

//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                        $wsCall = $wsClient->ws_SyncPasso($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel riportare il passo: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore nel riportare il passo: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $risposta = $wsClient->getResult();

                        Out::msgInfo("Risposta", $risposta);

                        break;

                    case $this->nameForm . '_callSyncAllegatiInfo':
                        $propak = $_POST[$this->nameForm . "_propakInfo"];

                        $praLib = new praLib();

                        $propas_rec = $praLib->GetPropas($propak, 'propak');
                        if (!$propas_rec) {
                            Out::msgInfo("Attenzione", "Non trovato il passo con propak = " . $propak);
                            return;
                        }

                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);


                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenAllegatoInfo"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'passo' => $propak,
                        );

//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                        $wsCall = $wsClient->ws_SyncAllegatiInfo($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel leggere gli allegati: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore nel leggere gli allegati: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $risposta = $wsClient->getResult();

                        $dati = base64_decode($risposta);

                        //$arrayDati = json_decode($dati,true);
                        $arrayDati = itaLib::utf8_decode_recursive(json_decode($dati, true));

                        if (!is_array($arrayDati)) {
                            Out::msgInfo("Errore", "La risposta non è un array");
                            break;
                        }

                        $numero = $arrayDati['NUMERO'][0];

                        //Out::msgInfo("FO Numero Allegati", $numero);


                        $allegati = $arrayDati['PASDOC'];

                        //Out::msgInfo("FO Allegati", print_r($allegati, true));

                        $this->sistemaAllegati($propak, $numero, $allegati);

                        break;


                    case $this->nameForm . '_callDeleteAllegatoPasso':
                        $propak = $_POST[$this->nameForm . "_propakDelete"];
                        $prosha2 = $_POST[$this->nameForm . "_improntaFileDelete"];

                        $praLib = new praLib();

                        $propas_rec = $praLib->GetPropas($propak, 'propak');
                        if (!$propas_rec) {
                            Out::msgInfo("Attenzione", "Non trovato il passo con propak = " . $propak);
                            return;
                        }

                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);


                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenDeleteAllegato"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'chiavePasso' => $propak,
                            'allegatoSha2' => $prosha2,
                        );

//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                        $wsCall = $wsClient->ws_DeleteAllegatoPasso($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel leggere gli allegati: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore nel leggere gli allegati: " . $wsClient->getError();
                            }
                            Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                            break;
                        }
                        $risposta = $wsClient->getResult();

                        if ($risposta == 'success') {
                            Out::msgInfo($risposta, "Cancellazione eseguita con Successo");
                        }

                        //Out::msgInfo("Risposta", $risposta);
                        break;


                    case $this->nameForm . '_callSyncAllegatiDelete':
                        $praLib = new praLib();

                        $sql = "SELECT * FROM PROPAS WHERE PROPART = 1 ORDER BY PROPAK";
                        $prapas_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);

                        // Salvo il file con i PROPAK nella directory di appoggio D:\works\phpDev\data\tmp\itaEngine
                        $pathFile = itaLib::createAppsTempPath('propak');
//                        $pathFile = itaLib::createAppsTempPath('tmp' . $IdPratica);
                        $nomeFile = $pathFile . "/PROPAK.txt";

                        Out::msgInfo($pathFile, $nomeFile);

                        unlink($nomeFile);

                        // Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
                        //$dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);

                        foreach ($prapas_tab as $prapas_rec) {
                            file_put_contents($nomeFile, $prapas_rec['PROPAK'] . "\n", FILE_APPEND);
                        }

                        
                        // istanza della classe ZipArchive
                        $zip = new ZipArchive();
                        // nome del file zip che voglio creare
                        $nomeZip = $pathFile . "/PROPAK.zip";

                        unlink($nomeZip);

                        // creo il zip
                        if ($zip->open($nomeZip, ZIPARCHIVE::CREATE) !== TRUE) {
                            // blocco il codice se la creazione del zip fallisce
                            Out::msgInfo("Attenzione !!!", "impossibile creare il file zip");
                            return;
                        }
                        // aggiungo al file zip il file 'file1.txt'
                        $zip->addFile($nomeFile, "propak.txt");
                        // chiudo il file zip e salvo tutte le modifiche fatte ad esso
                        $zip->close();


                        $wsClient = new praWsFrontOffice();
                        $this->setClientConfig($wsClient);

//                        Out::msgInfo("", base64_encode(file_get_contents($nomeZip)));

                        $params = array(
                            'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenAllegatiDelete"],
                            'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                            'stream' => base64_encode(file_get_contents($nomeZip)),
                        );

//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                        $wsCall = $wsClient->ws_SyncAllegatiDelete($params);
                        if (!$wsCall) {
                            if ($wsClient->getFault()) {
                                $Message = "Fault nel sincronizzare gli articoli cancellati: " . $wsClient->getFault();
                            } elseif ($wsClient->getError()) {
                                $Message = "Errore nel sincronizzare gli articoli cancellati: " . $wsClient->getError();
                            }
                            Out::msgInfo("SyncAllegatiDelete Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
//                            itaFileUtils::removeDir($pathFile);
                            break;
                        }
                        $risposta = $wsClient->getResult();

                        if ($risposta == 'success') {
                            Out::msgInfo($risposta, "Cancellazione Articoli non pubblicati eseguita con Successo");
                        }

                        
//                        unlink($nomeZip);
//                        unlink($nomeFile);
                        // Cancella directory utilizzata per salvare i files
                        itaFileUtils::removeDir($pathFile);
                        
                        

                        break;


                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function sistemaAllegati($propak, $numero, $arrayAllegatiFO) {
        // Controlla gli allegati riletti e se trovi allegati mancanti vanno inviati
        $praLib = new praLib();
        $wsClient = new praWsFrontOffice();
        $this->setClientConfig($wsClient);

        $arrayAllegatiDel = array();
        $arrayAllegatiIns = array();

        $praLib = new praLib();
        $pasDocBO_tab = $praLib->GetPasdoc($propak, 'codice', true);
        if ($pasDocBO_tab) {
            // Si scorrono gli allegati del BO e si controlla se è prresente nel FO
            foreach ($pasDocBO_tab as $pasDocBO_rec) {
                // Se allegato non è da pubblicare 
                if (!$pasDocBO_rec['PASPUB']) {
                    // Questo controllo non va fatto, perchè se l'allegato era stato pubblicato, nel
                    // ciclo sotto sugli allegti FO, lo metterebbe tra gli allegti da eliminare
//                    // Vedere se presente nel FO e cancellarlo
//                    $trovato = false;
//                    foreach($arrayAllegatiFO as $allegatoFO){
//
//                        if ($allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']){
//                            $trovato = true;
//                            break;
//                        }
//
//                    }
//                    
//                    if ($trovato){
//                        $arrayAllegatiDel[] = $allegatoFO;
//                    }

                    continue;
                }

                $trovato = false;
                foreach ($arrayAllegatiFO as $allegatoFO) {

                    if ($allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']) {
                        $trovato = true;
                        if ($allegatoFO['PASNOT'] != $pasDocBO_rec['PASNOT']) {
                            // Se le note del docukento sono cambiate, si cancella e si reinserisce
                            $arrayAllegatiDel[] = $pasDocBO_rec;

                            $arrayAllegatiIns[] = $pasDocBO_rec;
                        }

                        break;
                    }
                }

                if (!$trovato) {
                    $arrayAllegatiIns[] = $pasDocBO_rec;
                }
            }

            // Scorre gli allegati del FO e controlla se qualcuno è stato eliminato 
            foreach ($arrayAllegatiFO as $allegatoFO) {
                $trovato = false;
                foreach ($pasDocBO_tab as $pasDocBO_rec) {

                    if ($pasDocBO_rec['PASPUB'] && $allegatoFO['PASSHA2'] == $pasDocBO_rec['PASSHA2']) {
                        $trovato = true;
                        break;
                    }
                }

                if (!$trovato) {
                    $arrayAllegatiDel[] = $allegatoFO;
                }
            }
        } else {
            $arrayAllegatiDel = $arrayAllegatiFO;
        }

        Out::msgInfo("+ Allegati INS", print_r($arrayAllegatiIns, true));

        Out::msgInfo("- Allegati DEL", print_r($arrayAllegatiDel, true));

//        return;
        // Gli allegati presenti in $arrayAllegatiDel si cancellano con il metodo DeleteAllegatoPasso
        if ($arrayAllegatiDel) {
            foreach ($arrayAllegatiDel as $allegato) {

                $params = array(
                    'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenAllegatoInfo"],
                    'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                    'chiavePasso' => $allegato['PASKEY'],
                    'allegatoSha2' => $allegato['PASSHA2'],
                );


//                        Out::msgInfo("Parametri", print_r($params,true));
//                        return;

                $wsCall = $wsClient->ws_DeleteAllegatoPasso($params);
                if (!$wsCall) {
                    if ($wsClient->getFault()) {
                        $Message = "Fault nella cancellazione degli allegati: " . $wsClient->getFault();
                    } elseif ($wsClient->getError()) {
                        $Message = "Errore nella cancellazione  degli  allegati: " . $wsClient->getError();
                    }
                    Out::msgInfo("Errore Cancellazione", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                    break;
                }
                $risposta = $wsClient->getResult();
            }
        }

        // Gli allegati presenti in $arrayAllegatiIns vanno inviati con il metodo putAllegatoPasso
        if ($arrayAllegatiIns) {
            foreach ($arrayAllegatiIns as $allegato) {

                $dir = $praLib->SetDirectoryPratiche(substr($propak, 0, 4), $propak, "PASSO", false);

                $filename = $dir . "/" . $allegato['PASFIL'];

                if (file_exists($filename)) {
                    $fileBinario = file_get_contents($filename);


                    $allegatoSped = array();
                    $allegatoSped['nomeFile'] = $allegato['PASNAME'];
                    $allegatoSped['sha256digest'] = $allegato['PASSHA2'];
                    $allegatoSped['stream'] = base64_encode($fileBinario);
                    $allegatoSped['note'] = $allegato['PASNOT'];

//                    Out::msgInfo("Allegati da inserire", print_r($allegatoSped, true));

                    $params = array(
                        'itaEngineContextToken' => $_POST[$this->nameForm . "_tokenAllegatoInfo"],
                        'domainCode' => $_POST[$this->nameForm . "_CONFIG"]["wsCodiceEnte"],
                        'chiavePasso' => $propak,
                        'allegato' => $allegatoSped,
                    );

                    //                        Out::msgInfo("Parametri", print_r($params,true));
                    //                        return;

                    $wsCall = $wsClient->ws_putAllegatoPasso($params);
                    if (!$wsCall) {
                        if ($wsClient->getFault()) {
                            $Message = "Fault nell'invio degli allegati: " . $wsClient->getFault();
                        } elseif ($wsClient->getError()) {
                            $Message = "Errore nell'invio degli  allegati: " . $wsClient->getError();
                        }
                        Out::msgInfo("Login Result", '<pre style="font-size:1.5em">' . $Message . '</pre>');
                        break;
                    }
                    $risposta = $wsClient->getResult();

//                    Out::msgInfo("Risposta", print_r($risposta,true));
                }
            }
        }
    }

}
