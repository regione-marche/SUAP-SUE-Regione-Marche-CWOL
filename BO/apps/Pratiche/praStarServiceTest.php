<?php

/**
 *
 * TEST Servizio Web StarService
 *
 * PHP Version 5
 *
 * @category
 * @package    Interni
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    30.03.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPSelec/itaStarServiceClient.class.php');
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';

include_once ITA_BASE_PATH . './apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . './apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
include_once(ITA_LIB_PATH . '/zip/itaZip.class.php');


function praStarServiceTest() {
    $praStarServiceTest = new praStarServiceTest();
    $praStarServiceTest->parseEvent();
    return;
}

class praStarServiceTest extends itaModel {

    public $name_form = "praStarServiceTest";
    public $invioComunicazione_filePath;
    static private $tipoFo = "STARWS";

    function __construct() {
        parent::__construct();

        $this->invioComunicazione_filePath = App::$utente->getKey($this->nameForm . '_invioComunicazione_filePath');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_invioComunicazione_filePath', $this->invioComunicazione_filePath);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':

                $this->setClientConfig();
                Out::setFocus('', $this->name_form . "_CONFIG[wsEndpoint]");
                break;

            case 'onClick':
                switch ($_POST['id']) {

                    case $this->name_form . '_FileLocale':

                        //Out::msgInfo('Dati UpLoad', print_r($this->formData, true));

                        if (!@is_dir(itaLib::getPrivateUploadPath())) {
                            if (!itaLib::createPrivateUploadPath()) {
                                Out::msgStop("Gestione Acquisizioni", "Creazione ambiente di lavoro temporaneo fallita");
                                $this->returnToParent();
                            }
                        }
                        if ($_POST['response'] == 'success') {
                            $origFile = $_POST['file'];
                            $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
                            // Mi genera un nome causale
                            $randName = itaLib::getRandBaseName() . "." . pathinfo($uplFile, PATHINFO_EXTENSION);
                            $destFile = itaLib::getPrivateUploadPath() . "/" . $randName;
                            if (!@rename($uplFile, $destFile)) {
                                Out::msgStop("Upload File:", "Errore in salvataggio del file!");
                                break;
                            }
                            $this->invioComunicazione_filePath = $destFile;
                            Out::valore($this->name_form . '_invioComunicazione_nomeFile', $this->formData['file']);
                            //Out::msgInfo('Base 64', base64_encode(file_get_contents($uplFile)));
                        }


                        break;
                    case $this->name_form . '_callInvioComunicazione':

                        //Out::msgInfo('Base 64', base64_encode(file_get_contents($this->invioComunicazione_filePath)));

                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idPratica' => $this->formData[$this->name_form . '_InvioComunicazione_IdPratica'],
                            'tipoComunicazione' => $this->formData[$this->name_form . '_InvioComunicazione_tipoComunicazione'],
                            'destinatario' => $this->formData[$this->name_form . '_InvioComunicazione_destinatario'],
                            'oggetto' => $this->formData[$this->name_form . '_InvioComunicazione_oggetto'],
                            'messaggio' => $this->formData[$this->name_form . '_InvioComunicazione_messaggio'],
                            'fileZip' => base64_encode(file_get_contents($this->invioComunicazione_filePath))
                        );

                        $retCall = $starClient->ws_InvioComunicazione($param);
                        if (!$retCall) {
                            Out::msgStop("Errore", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }

                        Out::msgInfo("Risultato Invio Comunicazione", print_r($starClient->getResult(), true));
                        //Out::msgInfo("Request", print_r($starClient->getRequest(), true));
                        //Out::msgInfo("Response", print_r($starClient->getResponse(), true));
                        break;

                    case $this->name_form . '_callGetPraticheNuove':
                        $praLib = new praLib();
//                        $PRAM_DB = $praLib->getPRAMDB();
//
//                        Out::msgInfo("DB", print_r($PRAM_DB, true));
//
//
//
//                        $sql = "SELECT * FROM ORARIFO";
//
//                        $Orarifo_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
//                        Out::msgInfo("TABELLA", print_r($Orarifo_tab, true));
//
//
//                        $Orarifo_rec = array(
//                            'ORTSPCOD' => '99',
//                            'ORTIPO' => 'XX'
//                        );
//                        try {
//                            $nRows = ItaDB::DBInsert($PRAM_DB, 'ORARIFO', 'ROWID', $Orarifo_rec);
//                            if ($nRows == -1) {
//                                Out::msgStop("Inserimento", "Inserimento su: " . $Dset . " non avvenuto.");
//                                break;
//                            } else {
//                                $this->setLastInsertId(itaDB::DBLastId($PRAM_DB));
//                                Out::msgInfo("ID", itaDB::DBLastId($PRAM_DB));
//                                break;
//                            }
//                        } catch (Exception $e) {
//                            Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
//                            break;
//                        }
//
//
//                        break;
                        //$frontOffice = praFrontOfficeFactory::getFrontOfficeInstance(array('frontOfficeType'=>'italsoft-ws'));



                        $frontOffice = praFrontOfficeFactory::getFrontOfficeManagerInstance(praFrontOfficeManager::TYPE_FO_STAR_WS);

                        if (!$frontOffice) {
                            Out::msgStop("Errore", "Non ritrovato il metodo per rileggere le pratiche");
                            break;
                        }


                        $this->ctrlPratiche($frontOffice);


                        /*
                          $starClient = new itaStarServiceClient();
                          $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                          $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                          $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                          $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                          $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                          $param = array(
                          'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                          'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                          );

                          $retCall = $starClient->ws_GetPraticheNuove($param);
                          if (!$retCall) {
                          Out::msgStop("Errore GetPraticheNuove", $starClient->getFault() . " " . $starClient->getError);
                          break;
                          }
                          $result = $starClient->getResult();
                          $ItaXmlObj = new itaXML;

                          $retXml = $ItaXmlObj->setXmlFromString($starClient->getResult());
                          if (!$retXml) {
                          Out::msgStop("Errore GetComunicazioniNuove", 'Errore in lettura XML');
                          break;
                          }
                          $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());

                          //Out::msgInfo("idPratica:", print_r($arrayXml['Pratica'][0]['DatiPratica'][0]['IdPratica'][0][itaXML::textNode], true));
                          //Out::msgInfo("", print_r('<pre>' . htmlspecialchars($starClient->getResult()) . '</pre>', true));
                          Out::msgInfo("", print_r($arrayXml, true));
                         */

                        break;

                    case $this->name_form . '_callGetPratica':
                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idPratica' => $this->formData[$this->name_form . '_GetPratica_IdPratica']
                        );

                        $retCall = $starClient->ws_GetPratica($param);
                        if (!$retCall) {
                            Out::msgStop("Errore GetPratica", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        $ItaXmlObj = new itaXML;
                        $retXml = $ItaXmlObj->setXmlFromString($result['xmlResult']);
                        if (!$retXml) {
                            Out::msgStop("Errore GetPratica", 'Errore in lettura XML');
                            break;
                        }
                        $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());

                        // Crea cartella di appoggio per la sessione corrente
                        $pathFile = itaLib::createAppsTempPath('tmp_testStar');
                        Out::msgInfo("Path Temporanea", $pathFile);
                        Out::msgInfo("Response GetPratica: ", print_r($arrayXml, true));
                        file_put_contents("$pathFile/praticaSue.zip", base64_decode($result['fileZip']));
                        itaZip::Unzip("$pathFile/praticaSue.zip", $pathFile);
                        $lista = glob($pathFile . "/*.*");
                        Out::msgInfo("Lista", print_r($lista, true));
                        Out::openDocument(utiDownload::getUrl("praticaSue.zip", "$pathFile/praticaSue.zip"));
                        break;
                    case $this->name_form . '_callSetStatoPratica':
                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
//                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
//                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idPratica' => $this->formData[$this->name_form . '_SetStatoPratica_IdPratica'],
                            'stato' => $this->formData[$this->name_form . '_SetStatoPratica_stato']
                        );

                        $retCall = $starClient->ws_SetStatoPratica($param);
                        if (!$retCall) {
                            Out::msgStop("Errore SetStatoPratica", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        //$ItaXmlObj = new itaXML;
                        /*
                          $retXml = $ItaXmlObj->setXmlFromString($result['xmlResult']);
                          if (!$retXml) {
                          Out::msgStop("Errore SetStatoPratica", 'Errore in lettura XML');
                          break;
                          }
                         */
                        //$arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());
                        //Out::msgInfo("idPratica:", print_r($arrayXml['Pratica'][0]['DatiPratica'][0]['IdPratica'][0][itaXML::textNode], true));
                        //Out::msgInfo("", print_r('<pre>' . htmlspecialchars($starClient->getResult()) . '</pre>', true));
                        //Out::msgInfo("", print_r($arrayXml, true));
                        Out::msgInfo("", $result);

                        break;

                    case $this->name_form . '_callGetComunicazioniNuove':
                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword']
                        );

                        $retCall = $starClient->ws_GetComunicazioniNuove($param);
                        if (!$retCall) {
                            Out::msgStop("Errore GetComunicazioniNuove", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }

                        $ItaXmlObj = new itaXML;
                        $retXml = $ItaXmlObj->setXmlFromString($starClient->getResult());
                        if (!$retXml) {
                            Out::msgStop("Errore GetComunicazioniNuove", 'Errore in lettura XML');
                            break;
                        }
                        $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());

                        //Out::msgInfo("idPratica:", print_r($arrayXml['Pratica'][0]['DatiPratica'][0]['IdPratica'][0][itaXML::textNode], true));
                        //Out::msgInfo("", print_r('<pre>' . htmlspecialchars($starClient->getResult()) . '</pre>', true));
                        Out::msgInfo("", print_r($arrayXml, true));
                        break;
                    case $this->name_form . '_callGetComunicazione':
                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idComunicazione' => $this->formData[$this->name_form . '_GetComunicazione_IdComunicazione']
                        );

                        $retCall = $starClient->ws_GetComunicazione($param);
                        if (!$retCall) {
                            Out::msgStop("Errore GetComunicazione", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        $ItaXmlObj = new itaXML;
                        $retXml = $ItaXmlObj->setXmlFromString($result['xmlResult']);
                        if (!$retXml) {
                            Out::msgStop("Errore GetComunicazione", 'Errore in lettura XML');
                            break;
                        }
                        $arrayXml = $ItaXmlObj->toArray($ItaXmlObj->asObject());

                        // Crea cartella di appoggio per la sessione corrente
                        $pathFile = itaLib::createAppsTempPath('tmp_testStar');
                        Out::msgInfo("Path Temporanea", $pathFile);
                        Out::msgInfo("Response GetComunicazione: ", print_r($arrayXml, true));
                        file_put_contents("$pathFile/ComunicazioneSue.zip", base64_decode($result['fileZip']));
                        itaZip::Unzip("$pathFile/ComunicazioneSue.zip", $pathFile);
                        $lista = glob($pathFile . "/*.*");
                        Out::msgInfo("Lista", print_r($lista, true));
                        Out::openDocument(utiDownload::getUrl("ComunicazioneSue.zip", "$pathFile/ComunicazioneSue.zip"));

                        break;
                    case $this->name_form . '_callSetStatoComunicazione':
                        $starClient = new itaStarServiceClient();
                        $starClient->setWebservices_uri($this->formData[$this->name_form . '_CONFIG']['wsEndpoint']);
                        $starClient->setUsername($this->formData[$this->name_form . '_CONFIG']['wsUser']);
                        $starClient->setPassword($this->formData[$this->name_form . '_CONFIG']['wsPassword']);
                        $starClient->setNamespace($this->formData[$this->name_form . '_CONFIG']['wsNameSpace']);
                        $starClient->setTimeout($this->formData[$this->name_form . '_CONFIG']['wsTimeout']);

                        $param = array(
                            'user' => $this->formData[$this->name_form . '_CONFIG']['wsUser'],
                            'pwd' => $this->formData[$this->name_form . '_CONFIG']['wsPassword'],
                            'idComunicazione' => $this->formData[$this->name_form . '_SetStatoComunicazione_IdComunicazione'],
                            'stato' => $this->formData[$this->name_form . '_SetStatoComunicazione_stato']
                        );

                        $retCall = $starClient->ws_SetStatoComunicazione($param);
                        if (!$retCall) {
                            Out::msgStop("Errore SetStatoComunicazione", $starClient->getFault() . " " . $starClient->getError);
                            break;
                        }
                        $result = $starClient->getResult();
                        $ItaXmlObj = new itaXML;
                        Out::msgInfo("", $result);

                        break;

                    case $this->name_form . '_callScaricaPraticheNuove':
                        /* @var $FOManager praFrontOfficeStarWs */
                        $FOManager = praFrontOfficeFactory::getFrontOfficeManagerInstance();
                        $FOManager->scaricaPraticheNuove();
                        Out::msgInfo("Elaborazione Terminata", print_r($FOManager->getRetStatus(), true));
                        break;

                    case $this->name_form . '_callFormPraticheNuove':
                        $model = 'praCtrRichiesteFO';
                        $_POST = array();
                        $_POST['event'] = 'openform';
                        $_POST['perms'] = $this->perms;
                        $_POST[$model . '_returnModel'] = $this->nameForm;
                        $_POST[$model . '_returnEvent'] = 'returnCtrRichieste';
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_invioComunicazione_filePath');
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    private function setClientConfig() {
        $config_tab = array();
        $devLib = new devLib();
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEENDPOINT', false);
        $config_rec['wsEndpoint'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEUSER', false);
        $config_rec['wsUser'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEPASSWD', false);
        $config_rec['wsPassword'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICENAMESPACE', false);
        $config_rec['wsNameSpace'] = $config_val['CONFIG'];
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICETIMEOUT', false);
        $config_rec['wsTimeout'] = $config_val['CONFIG'];

        Out::valori($config_rec, $this->name_form . '_CONFIG');
    }

    public function ctrlPratiche($frontOffice) {

        $arrayXml = $frontOffice->getElencoRichiesteNuove();

        if (!$arrayXml) {
            Out::msgStop("Errore rilettura dati da CART", "Codice: " . $frontOffice->getErrCode() . " - " . $frontOffice->getErrMessage());
        }

        $arrayPratiche = $arrayXml['Pratica'];

        //Out::msgInfo("Array Pratiche : ".count($arrayPratiche), print_r($arrayPratiche, true));

        $num = 1;
        foreach ($arrayPratiche as $pratica => $datiPratica) {

            $IdPratica = $datiPratica['DatiPratica'][0]['IdPratica'][0][itaXml::textNode];

            //Out::msgInfo("Risultato array ".$num , print_r($IdPratica, true));

            $this->leggiPratica($IdPratica, $frontOffice);

            $num++;
        }
    }

    public function leggiPratica($IdPratica, $frontOffice) {

        $param = array(
            'idPratica' => $IdPratica
        );


        $arrayXml = $frontOffice->getRichiesta($param);
        if (!$arrayXml) {
            Out::msgStop("Errore rilettura dati Pratica da CART", "Codice: " . $frontOffice->getErrCode() . " - " . $frontOffice->getErrMessage());
        }

        $datiXml = $arrayXml[0];
        $zipB64 = $arrayXml[1];

        // Crea cartella di appoggio per la sessione corrente
        $pathFile = itaLib::createAppsTempPath('tmp' . $IdPratica);
        Out::msgInfo("Path Temporanea", $pathFile);
        file_put_contents("$pathFile/praticaSue.zip", base64_decode($zipB64));
        itaZip::Unzip("$pathFile/praticaSue.zip", $pathFile);
        $lista = glob($pathFile . "/*.*");
        Out::msgInfo("Lista", print_r($lista, true));


//        Out::openDocument(utiDownload::getUrl("praticaSue.zip", "$pathFile/praticaSue.zip"));
//        Out::msgInfo("Dati Xml ", print_r($datiXml, true));


        $dataArrivo = $this->getData($datiXml['DatiPratica'][0]['Data_arrivo'][0][itaXml::textNode]);
        $oraArrivo = $this->getOra($datiXml['DatiPratica'][0]['Data_arrivo'][0][itaXml::textNode]);
        $numeroProtocollo = $datiXml['DatiPratica'][0]['Protocollo_numero'][0][itaXml::textNode];
        $dataProtocollo = $this->getData($datiXml['DatiPratica'][0]['Protocollo_data'][0][itaXml::textNode]);
        $oraProtocollo = $this->getOra($datiXml['DatiPratica'][0]['Protocollo_data'][0][itaXml::textNode]);

        $dataScarico = date("Ymd");
        $oraScarico = date("H:i:s");



        $retIndex = null;
        foreach ($lista as $indice => $file) {
            if (strpos($file, 'COPERTINA-') !== false) {
                $retIndex = $indice;
                break;
            }
        }

        if (!$retIndex) {
            Out::msgStop("Errore", "Non trovato il file con la Copertina xml");
            return false;
        }



        $file = $lista[$retIndex];

        //Out::msgInfo("file COPERTINA.XML ", print_r($file, true));

        $ItaXmlObj = new itaXML;
        if (!$ItaXmlObj->setXmlFromFile($file)) {
            Out::msgStop("Errore", "Non trovato il file con la Copertina xml: $file ");
            return false;
        }


        $arrCopertina = $ItaXmlObj->toArray($ItaXmlObj->asObject());
        if (!$arrCopertina) {
            Out::msgStop("Errore", "Non trovato il file con la Copertina xml: $file ");
            return false;
        }

        Out::msgInfo("Contenuto file COPERTINA.XML ", print_r($arrCopertina, true));

        $tipoProcedimento = $arrCopertina['oggettoComunicazione'][0]['tipoProcedimento'][0][itaXml::textNode];
        $azione = $arrCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];

        $descProcedimento = "Codice attivita: " .
                " - " . $azione .
                ". Procedimento " . $tipoProcedimento;

        $praFoList = array(
            //'FOTIPO' => "STARWS",
            'FOTIPO' => self::$tipoFo,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $IdPratica,
            'FOPRADESC' => $descProcedimento,
            'FOPRADATA' => $dataArrivo,
            'FOPRAORA' => $oraArrivo,
            'FOPROTDATA' => $dataProtocollo,
            'FOPROTORA' => $oraProtocollo,
            'FOPROTNUM' => $numeroProtocollo,
            'FOESIBENTE' => $arrCopertina['presentatore'][0]['cognome'][0][itaXml::textNode] . " " .
            $arrCopertina['presentatore'][0]['nome'][0][itaXml::textNode],
            'FODICHIARANTE' => $arrCopertina['richiedente'][0]['cognome'][0][itaXml::textNode] . " " .
            $arrCopertina['richiedente'][0]['nome'][0][itaXml::textNode],
            'FODICHIARANTECF' => $arrCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode],
            'FODICHIARANTEQUALIFICA' => $arrCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode],
            'FOALTRORIFERIMENTODESC' => "Denominazione Impresa",
            'FOALTRORIFERIMENTO' => $arrCopertina['impresa'][0]['denominazione'][0][itaXml::textNode],
            'FOALTRORIFERIMENTOIND' => $arrCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode],
            'FOALTRORIFERIMENTOCAP ' => $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode]
        );


        Out::msgInfo("Dati per praFoList ", print_r($praFoList, true));

        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();


        $salvatoPraFoList = $this->salvaPraFoList($praFoList, $PRAM_DB);

        if ($salvatoPraFoList) {
            // Si salvano gli allegati in PRAFOFILES

            foreach ($lista as $indice => $file) {

                if (strpos($file, '.zip') == false) {

                    $pos = strrpos($file, "/");
                    $pos++;
                    $nomeFile = substr($file, $pos);

                    $praFoFiles = array(
                        // 'FOTIPO' => "STARWS",
                        'FOTIPO' => self::$tipoFo,
                        'FOPRAKEY' => $IdPratica,
                        'FILESHA2' => hash_file('sha256', $file),
                        'FILEID' => $nomeFile,
                        'FILENAME' => $nomeFile,
                        'FILEFIL' => $nomeFile,
                    );


                    $this->salvaPraFoFile($praFoFiles, $PRAM_DB);
                }
            }
        }
    }

    public function salvaPraFoList($praFoList, $PRAM_DB) {


        $sql = "SELECT * FROM PRAFOLIST "
                . " WHERE PRAFOLIST.FOTIPO = '" . $praFoList['FOTIPO'] . "'"
                . " AND PRAFOLIST.FOPRAKEY = '" . $praFoList['FOPRAKEY'] . "'";


        $praFoList_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        if (!$praFoList_tab) {

            try {
                $nRows = ItaDB::DBInsert($PRAM_DB, 'PRAFOLIST', 'ROWID', $praFoList);
                if ($nRows == -1) {
                    Out::msgStop("Inserimento", "Inserimento su PRAFOLIST non avvenuto.");
                    return false;
                }
            } catch (Exception $e) {
                Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
                return false;
            }
        } else
            return false;

        return true;
    }

    public function salvaPraFoFile($praFoFiles, $PRAM_DB) {

        try {
            $nRows = ItaDB::DBInsert($PRAM_DB, 'PRAFOFILES', 'ROWID', $praFoFiles);
            if ($nRows == -1) {
                Out::msgStop("Inserimento", "Inserimento su PRAFOFILES non avvenuto.");
                return false;
            }
        } catch (Exception $e) {
            Out::msgStop("Errore in Inserimento", $e->getMessage(), '600', '600');
            return false;
        }


        return true;
    }

    public function getData($timeStamp) {
        // Formato $timeStamp è 2017-09-11 00:00:00.0

        $data = substr($timeStamp, 0, 4) . substr($timeStamp, 5, 2) . substr($timeStamp, 8, 2);

        return $data;
    }

    public function getOra($timeStamp) {
        // Formato $timeStamp è 2017-09-11 00:00:00.0

        $ora = substr($timeStamp, 11, 8);

        return $ora;
    }

}
