<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DATI A SITO FRONT OFFICE DI CART TOSCANA
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi / Michele Moscioni
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    03.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praFrontOfficeManager.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPSelec/itaStarServiceClient.class.php');

class praFrontOfficeStarWs extends praFrontOfficeManager {

    function __construct() {
        parent::__construct();
    }

    public function getElencoRichiesteNuove() {
        $starClient = new itaStarServiceClient();

// Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($starClient);


        $retCall = $starClient->ws_GetPraticheNuove();
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage($starClient->getFault() . " " . $starClient->getError);
            return false;
        }
//$result = $starClient->getResult();
        $ItaXmlObj = new itaXML;

        $retXml = $ItaXmlObj->setXmlFromString($starClient->getResult());
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in lettura XML');
            return false;
        }
        return $ItaXmlObj->toArray($ItaXmlObj->asObject());
    }

    public function getRichiesta($param) {
        $starClient = new itaStarServiceClient();

// Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($starClient);


        $retCall = $starClient->ws_GetPratica($param);
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage($starClient->getFault() . " " . $starClient->getError);
            return false;
        }
        $result = $starClient->getResult();


        $ItaXmlObj = new itaXML;

        $retXml = $ItaXmlObj->setXmlFromString($result['xmlResult']);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore in lettura XML');
            return false;
        }
        return array($ItaXmlObj->toArray($ItaXmlObj->asObject()), $result['fileZip']);
    }

    private function setClientConfig($starClient) {
        $config_tab = array();
        $devLib = new devLib();
        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEENDPOINT', false);
// $config_rec['wsEndpoint'] = $config_val['CONFIG'];
        $starClient->setWebservices_uri($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEUSER', false);
//$config_rec['wsUser'] = $config_val['CONFIG'];
        $starClient->setUsername($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICEPASSWD', false);
// $config_rec['wsPassword'] = $config_val['CONFIG'];
        $starClient->setPassword($config_val['CONFIG']);

        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICENAMESPACE', false);
//$config_rec['wsNameSpace'] = $config_val['CONFIG'];
        $starClient->setNamespace($config_val['CONFIG']);


        $config_val = $devLib->getEnv_config('SELECTSTARSERVICE', 'codice', 'WSSTARSERVICETIMEOUT', false);
//$config_rec['wsTimeout'] = $config_val['CONFIG'];
        $starClient->setTimeout($config_val['CONFIG']);
    }

    /**
     *
     * @return boolean
     */
    public function scaricaPraticheNuove() {

        $this->retStatus = array(
            'Status' => true,
            'Lette' => 0,
            'Scaricate' => 0,
            'Errori' => 0,
            'Messages' => array()
        );

        $arrayXml = $this->getElencoRichiesteNuove();
        if (!$arrayXml) {
            return false;
        }
        $arrayPratiche = $arrayXml['Pratica'];
        $this->retStatus['Lette'] = count($arrayPratiche);
        foreach ($arrayPratiche as $datiPratica) {
            $IdPratica = $datiPratica['DatiPratica'][0]['IdPratica'][0][itaXml::textNode];
            if (!$this->scaricaPratica($IdPratica, $datiPratica)) {
                $this->retStatus['Errori'] += 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = $this->getErrMessage();
            } else {
                $this->retStatus['Scaricate'] += 1;
            }
        }
        return true;
    }

    /**
     *
     * @param type $IdPratica
     * @return boolean
     */
    public function scaricaPratica($IdPratica, $datiPratica) {

        $param = array(
            'idPratica' => $IdPratica
        );
        $arrayXml = $this->getRichiesta($param);
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore lettura dati Pratica da CART " . $this->getErrMessage());
//Out::msgStop("Errore rilettura dati Pratica da CART", "Codice: " . $frontOffice->getErrCode() . " - " . $frontOffice->getErrMessage());
            return false;
        }

        $datiXml = $arrayXml[0];
        $zipB64 = $arrayXml[1];

        /*
         * Crea cartella di appoggio per la sessione corrente
         *
         */


        $pathFile = itaLib::createAppsTempPath('tmp' . $IdPratica);

        /*
         * TODO: verificare se il nome del file zip può essere letto da qualche dato o se possimao dargli un nome generico
         */
        if (!@file_put_contents("$pathFile/$IdPratica.zip", base64_decode($zipB64))) {
//if (@file_put_contents("$pathFile/$IdPratica.zip", base64_decode($zipB64))) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel decodificare il file ZIP" . $this->getErrMessage());
            return false;
        }
        if (!itaZip::Unzip("$pathFile/$IdPratica.zip", $pathFile)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nel scompattare il file ZIP con gli allegati della pratica" . $this->getErrMessage());
            return false;
        }
        $lista = glob($pathFile . "/*.*");
        if ($lista === false) {
            $this->setErrCode(-1);
            $this->setErrMessage("Nella directory $pathFile non sono stati ritrovati gli allegati" . $this->getErrMessage());
            return false;
        }

//$dataArrivo = date('Ymd', strtotime($datiXml['DatiPratica'][0]['Data_arrivo'][0][itaXml::textNode]));

        $dataArrivo = $this->getData($datiXml['DatiPratica'][0]['Data_arrivo'][0][itaXml::textNode]);
//$oraArrivo = $this->getOra($datiXml['DatiPratica'][0]['Data_arrivo'][0][itaXml::textNode]);
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

        if ($retIndex === null) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non trovato il file con la Copertina xml per la pratica $IdPratica" . $this->getErrMessage());
            return false;
        }

        $file = $lista[$retIndex];

        $ItaXmlObj = new itaXML;
        if (!$ItaXmlObj->setXmlFromFile($file)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non trovato il file con la Copertina xml: $file ");
            return false;
        }

        $arrCopertina = $ItaXmlObj->toArray($ItaXmlObj->asObject());
        if (!$arrCopertina) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura Dati copertina non riuscita per il file: $file ");
            return false;
        }

        $descProcedimento = '';
//$descProcedimento = 'Procedimento Edilizio';
        for ($num = 0; $num < count($datiPratica['EndoEdilizia'][0]['Edilizia']); ++$num) {

            $descProcedimento = $descProcedimento . $datiPratica['EndoEdilizia'][0]['Edilizia'][$num]['Codice_Ed'][0][itaXml::textNode]
                    . " -> " .$datiPratica['EndoEdilizia'][0]['Edilizia'][$num]['Desc_Ed'][0][itaXml::textNode]
                    . "; ";
//$descProcedimento = $descProcedimento . "Codice: " . $datiPratica['EndoEdilizia'][0]['Edilizia'][$num]['Codice_Ed'][0][itaXml::textNode] ;
//$descProcedimento = $descProcedimento . " - " . $datiPratica['EndoEdilizia'][0]['Edilizia'][$num]['Desc_Ed'][0][itaXml::textNode] . "; ";
        }

        $tipoProcedimento = $arrCopertina['oggettoComunicazione'][0]['tipoProcedimento'][0][itaXml::textNode];
        $azione = $arrCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];

        $descProcedimento = $descProcedimento .
                " " . $azione .
                " - Procedimento " . $tipoProcedimento;

        $oraArrivo = $this->getOra($arrCopertina['oggettoComunicazione'][0]['dataPresentazione'][0][itaXml::textNode]);

        $sportelloSuap = $arrCopertina['oggettoComunicazione'][0]['sportelloSuap'][0]['codice'][0][itaXml::textNode];

        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_STAR_WS,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $IdPratica,
            'FOIDPRATICA' => $IdPratica,
            'FOTIPOSTIMOLO' => praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_STAR_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA],
            'FOPRASPACATA' => $sportelloSuap,
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
            'FOALTRORIFERIMENTOCAP' => $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode],
            'FOMETADATA' => json_encode($arrCopertina)
        );

//Rimette nel vettore i dati di copertina
//$arr = json_decode($praFoList_rec['FOMETADATA']);
// Si salvano gli allegati in PRAFOFILES

        $praFoFiles_tab = array();
        foreach ($lista as $file) {
            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'zip') {
                $nomeFile = pathinfo($file, PATHINFO_BASENAME);
                $praFoFiles_rec = array(
                    'FOTIPO' => praFrontOfficeManager::TYPE_FO_STAR_WS,
                    'FOPRAKEY' => $IdPratica,
                    'FILESHA2' => hash_file('sha256', $file),
                    'FILEID' => $nomeFile,
                    'FILENAME' => $nomeFile,
                    'FILEFIL' => itaLib::getRandBaseName() . '.' . pathinfo($file, PATHINFO_EXTENSION),
                    'TMP_SOURCEFILE' => $file,
                );
                $praFoFiles_tab[] = $praFoFiles_rec;
            }
        }

        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => $praFoFiles_tab,
        );

        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            return false;
        }
        
        //Si imposta la pratica come letta, invocando il servizio web SetStatoPratica
        $this->impostaPraticaLetta($praFoList_rec);
        
        return true;
    }


    public function checkFoAcqPreconditions($param) {
        $prafolist_rec = $param['prafolist_rec'];

        if (!$prafolist_rec) {
            self::$lasErrCode = -1;
            self::$lasErrMessage = 'Lettura della pratica non avvenuto.';
            return false;
        }

        /**
         *
         * Verifico se i codici procedimento cart sono correttamente Mappati su cwol
         */
        $retDecode = $this->decodeEndoProcedimenti($prafolist_rec);

        //TODO
        //Ciclo su $retDecode nel caso non ci sia record id PRAFODECODE
        if (in_array('', $retDecode)) {

            $indice = array_search('', $retDecode);

            $model = 'praFoDecodeGest';
            itaLib::openDialog($model);
            /* @var $modelObj praAssegnaPraticaSimple */
            $modelObj = itaModel::getInstance($model);
            $modelObj->setReturnModel($param['returnModel']);
            $modelObj->setReturnEvent('onClick');
            $modelObj->setReturnId($param['returnId']);
            $modelObj->setEvent('openform');
            $modelObj->setTipoFo(praFrontOfficeManager::TYPE_FO_STAR_WS);
            //Da valorizzare, prendendola da $retDecode, quando si gestisce
            $modelObj->setChiaveFo($indice);
            $modelObj->setDialog(true);
            $modelObj->parseEvent();
        }
        else return true;

    }

    public function checkFoPostconditions($params) {

    }

    private function getProges_rec($praFoList_rec, $praFoDecode_rec) {

        $gesDre = date("Ymd");
        $gesDri = $praFoList_rec['FOPRADATA'];
        $gesOra = $praFoList_rec['FOPRAORA'];
        $gesPra = "";
// $gesPra = $praFoList_rec['FOPRAKEY'] // Codice pratica per Regione Toscana è 'NGRLCN51D03Z600C-22062018-1208'
        $gesPro = $praFoDecode_rec['FODESTPRO'];    // ANAPRA.PRANUM  "000001";
        $gesTsp = $praFoDecode_rec['FODESTTSP'];   // PRAFODECODE.FODESTTSP  (1)
        $gesSpa = "0";
        $gesEve = $praFoDecode_rec['FODESTEVCOD']; // PRAFODECODE.FODESTEVCOD
//$gesEve = "6";
        $gesSeg = "ALTRO";


        $gesRes = "000001";   // ANAPRA.PRARES
        $anapra_rec = $this->praLib->GetAnapra($praFoDecode_rec['FODESTPRO']);

        if ($anapra_rec) {
            $gesRes = $anapra_rec['PRARES'];   // ANAPRA.PRARES
        }



        $arrayProges = array(
            'GESDRE' => $gesDre,
            'GESDRI' => $gesDri,
            'GESORA' => $gesOra,
            'GESPRA' => $gesPra,
            'GESPRO' => $gesPro,
            'GESRES' => $gesRes,
            'GESTSP' => $gesTsp,
            'GESSPA' => $gesSpa,
            'GESEVE' => $gesEve,
            'GESSEG' => $gesSeg,
            'GESDCH' => ''
        );

        return $arrayProges;
    }

    private function getAnades_rec($praFoList_rec) {

        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);


        $desRuo = "0001";  // Codice Esibente
        $desNom = $praFoList_rec['FOESIBENTE'];
        $desInd = "";
        $desCap = "";
        $desCit = "";
        $desPro = "";
        $desEma = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];
        $desFis = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];


        $arrayAnades = array(
            'DESRUO' => $desRuo,
            'DESNOM' => $desNom,
            'DESIND' => $desInd,
            'DESCAP' => $desCap,
            'DESCIT' => $desCit,
            'DESPRO' => $desPro,
            'DESEMA' => $desEma,
            'DESFIS' => $desFis
        );

        return $arrayAnades;
    }

    private function getArrayAllegati($praFoList_rec) {
        $arrayAllegati = array();

        $praLib = new praLib();
//$PRAM_DB = $praLib->getPRAMDB();

        $anno = substr($praFoList_rec['FOPRADATA'], 0, 4);

// Copia il file dalla cartella temporea in quella restituida dal metodo SetDirectoryPratiche
        $dir = $praLib->SetDirectoryPratiche($anno, $praFoList_rec['FOPRAKEY'], $praFoList_rec['FOTIPO']);


// Leggo record di PRAFOFILES
        $sql = "SELECT * FROM PRAFOFILES"
                . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

        $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);

        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            $id = $praFoFiles_rec['ROW_ID'];
            $dataFile = $dir . "/" . $praFoFiles_rec['FILEFIL'];
            $fileName = $praFoFiles_rec['FILENAME'];
            $fileInfo = "";
            $allegato = array(
                'ID' => $id,
                'DATAFILE' => $dataFile,
                'FILENAME' => $fileName,
                'FILEINFO' => $fileInfo,
                'PRAFOFILES_ROW_ID' => $praFoFiles_rec['ROW_ID']
            );

            array_push($arrayAllegati, $allegato);
            unset($allegato);  // Svuota il vettore

        }

        return $arrayAllegati;
    }

    private function getXmlInfo($praFoList_rec, $praFoDecode_rec, $procSecondari, $principale = 'true', $progressivo = '001', $codEndoSecondario = '') {

        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);

        $arrayProRic = $this->getProric_rec($praFoList_rec, $praFoDecode_rec, $principale, $progressivo);

        if ($principale) {
            $ricnum = $praFoList_rec['FOIDPRATICA'];   // Numero della Richiesta
        } else {
            $ricnum = $praFoList_rec['FOIDPRATICA'] . "_" . $progressivo;   // Numero della Richiesta
        }

        $arrayRicDoc = array();
        $praLib = new praLib();
        if ($principale) {

            // Riporto RICDOC - Leggo record di PRAFOFILES
            $sql = "SELECT * FROM PRAFOFILES "
                    . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                    . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

            $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);


            foreach ($praFoFiles_tab as $praFoFiles_rec) {

                $iteKey = $ricnum;
                $fileName = $praFoFiles_rec['FILENAME'];
                $docSha2 = $praFoFiles_rec['FILESHA2']; // hash_file('sha256', $filename);   
                $docPrt = "";

                $allegato = array(
                    'ITEKEY' => $iteKey,
                    'DOCNAME' => $fileName,
                    'DOCSHA2' => $docSha2,
                    'DOCPRT' => $docPrt
                );

                //array_push($arrayRicDoc, $allegato);

                $recordRicDoc[] = array(
                    'RECORD' => array_map(array($this, 'encodeForXml'), $allegato)
                );

                unset($allegato);  // Svuota il vettore
                
            }
            $arrayRicDoc[] = array($recordRicDoc);
        } else {
            if ($codEndoSecondario) {
                $iteKeyAccorpata = $praLib->keyGenerator($praFoDecode_rec['FODESTPRO']);
                
                $ricIte = array(
                    'RICNUM' => $ricnum,
                    'ITECOD' => $praFoDecode_rec['FODESTPRO'],
                    'ITEDES' => "Passo Richiesta Accorpata",
                    'ITEKEY' => $iteKeyAccorpata,
                    'ITEPUB' => 1
                );

                $recordRicIte[] = array(
                    'RECORD' => array_map(array($this, 'encodeForXml'), $ricIte)
                );

                $arrayRicIte[] = array($recordRicIte);
                
            }
        }


// Riporto RICDAG
        $arrayRicDag = array();
        $recordRicDag = array();

// Simulo caricamento di RICDAG
//ESIBENTE
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_NOME", $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_COGNOME", $arrayCopertina['presentatore'][0]['cognome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "ESIBENTE_CODICEFISCALE_CFI", $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode]);

//DICHIARANTE
//$recordRicDag[] = $this->getRicDag("DICHIARANTE_COGNOME_NOME", $praFoList_rec['FODICHIARANTE']);
//$recordRicDag[] = $this->getRicDag("DICHIARANTE_CODICEFISCALE_CFI", $praFoList_rec['FODICHIARANTECF']);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NOME", $arrayCopertina['richiedente'][0]['nome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_COGNOME", $arrayCopertina['richiedente'][0]['cognome'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CODICEFISCALE_CFI", $arrayCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NASCITACOMUNE", $arrayCopertina['richiedente'][0]['luogo-nascita'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NASCITADATA_DATA", $arrayCopertina['richiedente'][0]['data-nascita'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CITTADINANZA", $arrayCopertina['richiedente'][0]['cittadinanza'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_QUALIFICA", $arrayCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZACOMUNE", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZAPROVINCIA_PV", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['provincia'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZAVIA", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_RESIDENZACAP_CAP", $arrayCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['cap'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_PEC", $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_CELLULARE", $arrayCopertina['recapiti'][0]['cellulare'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_TELEFONO", $arrayCopertina['recapiti'][0]['telefono'][0][itaXml::textNode]);

// IMPRESA
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_RAGIONESOCIALE", $arrayCopertina['impresa'][0]['denominazione'][0][itaXml::textNode]);

        if ($arrayCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] === "Legale Rappresentante") {
            $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NATURALEGA_RADIO", "R");
        } else if ($arrayCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] === "Titolare") {
            $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "DICHIARANTE_NATURALEGA_RADIO", "T");
        }

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_CODICEFISCALE_CFI", $arrayCopertina['impresa'][0]['codice-fiscale'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDECOMUNE", $arrayCopertina['impresa'][0]['sede-legale'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGPROVINCIA_PV", $arrayCopertina['impresa'][0]['sede-legale'][0]['provincia'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGVIA", $arrayCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMPRESA_SEDELEGCAP", $arrayCopertina['impresa'][0]['sede-legale'][0]['cap'][0][itaXml::textNode]);

// INSEDIAMENTOLOCALE
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INSEDIAMENTOLOCALE_VIA", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IC_INDIR_INS_PROD", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_VIA", $arrayCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_LOCALITA", $arrayCopertina['impiantoProduttivo'][0]['comune'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_CAP", $arrayCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "INTER_PROVINCIA", $arrayCopertina['impiantoProduttivo'][0]['provincia'][0][itaXml::textNode]);

        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_CATEGORIA", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_SEZIONE", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_FOGLIO", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['foglio'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_PARTICELLA", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['numero'][0][itaXml::textNode]);
        $recordRicDag[] = $this->getRicDag($praFoDecode_rec['FODESTPRO'], "IMM_SUBALTERNO", $arrayCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['subalterno'][0][itaXml::textNode]);

        $arrayRicDag[] = array($recordRicDag);


        // Riporto le RICHIESTE_ACCORPATE, solo se è Procedimento Principale
        $arrayRichieste_Accorpate = $richieste_Accorpate = array();
        if ($principale) {

            $xmlInfo = array();

            foreach ($procSecondari as $key => $app) {

                $xmlInfo = array(
                    "XMLINFO" => $app['XMLINFO']
                );

                $richieste_Accorpate[] = array_map(array($this, 'encodeForXml'), $xmlInfo);
            }
            $arrayRichieste_Accorpate[] = array($richieste_Accorpate);
        }



        $arrayProRic = array_map(array($this, 'encodeForXml'), $arrayProRic);

        //       $arrayRichieste_Accorpate = array_map(array($this, 'encodeForXml'), $arrayRichieste_Accorpate);
        //Out::msgInfo("Richieste Acc", print_r($arrayRichieste_Accorpate, true));

        $root = array(
            'PRORIC' => array($arrayProRic),
            'RICITE' => $arrayRicIte,
            'RICDOC' => $arrayRicDoc,
            'RICDAG' => $arrayRicDag,
            'RICHIESTE_ACCORPATE' => $arrayRichieste_Accorpate // array()
        );



//array_walk_recursive($root, array($this,'encodeForXml'));

        $arrayXmlInfo = array(
            'ROOT' => $root
        );

        return $arrayXmlInfo;
    }
    

    private function getArrayCampo($codProc, $dagKey, $ricDat) {

        $campo = array(
            'ITECOD' => $codProc,
            'ITEKEY' => $codProc,
            'DAGKEY' => $dagKey,
            'RICDAT' => $ricDat
        );

        return $campo;
    }

    private function encodeForXml($value) {
        return array(itaXML::textNode => htmlspecialchars(utf8_encode($value)));
    }

    private function getRicDag($codProc, $nomeCampo, $valore) {

        $campo = $this->getArrayCampo($codProc, $nomeCampo, $valore);
//$campo = $this->getArrayCampo("DICHIARANTE_COGNOME_NOME", $praFoList_rec['FODICHIARANTE']);
        $recordRicDag[] = array(
            'RECORD' => array_map(array($this, 'encodeForXml'), $campo)
        );

        return $recordRicDag;
    }

    public function decodeEndoProcedimenti($prafolist_rec) {
        $arrayCopertina = json_decode($prafolist_rec['FOMETADATA'], true);
        $retDecode = array();
        $variazione = $arrayCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];
        foreach ($arrayCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0]['ns2:idEndoprocedimento'] as $arrayEndo) {
            $idEndo = $arrayEndo[itaXml::textNode];
            $idEndo = str_replace(" ", "_", $idEndo);
            $retDecode[$idEndo . "/" . $variazione] = $this->decodificaEndo($idEndo, $variazione);
        }
        return $retDecode;
    }

    private function decodificaEndo($idEndo, $variazione) {
        $endo1 = str_replace(" ", "_", $idEndo);
        $decodifica = $endo1 . "/" . $variazione;

        $sql = "SELECT * FROM PRAFODECODE WHERE FOSRCKEY = '$decodifica' "
                . "AND PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_STAR_WS . "'";
        $praFoDecode_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);
        if (!$praFoDecode_rec) {
            return null;
        } else {
            return $praFoDecode_rec;
        }
        //TODO: Controlla se presente record di PRACODECODE per FOSRCKEY = $decodifica
        //return $decodifica;
        //return $decodifica;
    }

    public function getDescrizioneGeneraleRichiestaFo($prafolist_rec, $nameForm = '') {
        $decodeEndo = $this->decodeEndoProcedimenti($prafolist_rec);
        $htmEndoProc = '<div style="width:400px;">';
        //Out::msginfo('ret', print_r($decodeEndo, true));
        foreach ($decodeEndo as $key => $EndoProcedimento) {
            $keyProcedimentoStar = str_replace('/', '-', $key);
            if ($EndoProcedimento) {
                $keyProcedimentoProt = $EndoProcedimento['FODESTPRO'];
                $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:green; color:white;">Procedimento ' . $key . ' associato a ' . $keyProcedimentoProt;
                $htmEndoProc .= ' <a href="#" id="' . $keyProcedimentoStar . '" class="ita-hyperlink {event:\'DettaglioProcedimento\'}"><span title="Dettaglio Procedimento" class="ita-icon ita-icon-cerca-24x24" style="display:inline-block; vertical-align:top;"></span></a></div>';
            } else {
                $htmEndoProc .= '<div style="border-color:white; border-style:solid; padding:2px; background-color:red; color:white;">Procedimento ' . $key . ' non associato. ';
                $htmEndoProc .= '<a href="#" id="' . $keyProcedimentoStar . '" class="ita-hyperlink {event:\'AssociaProcedimenti\'}"><span title="Associa Procedimento" class="ita-icon ita-icon-edit-24x24" style="display:inline-block; vertical-align:top;"></span></a></div>';
            }
        }
        $htmEndoProc .= "</div>";

        $arrCopertina = json_decode($prafolist_rec['FOMETADATA'], true);
        $descrizione = '<div style="padding:5px;">';
        $descrizione .= "La richiesta: " . $arrCopertina['idDomanda'][0][itaXml::textNode]
                . " è stata ricevuta. <br/> "
                . "Di seguito si riporta il riepilogo del procedimento attivato: <br/> <br/> $htmEndoProc"
                . "DATI RICHIEDENTE <br/>"
                . "Qualifica: " . $arrCopertina['richiedente'][0]['qualita-richiedente'][0][itaXml::textNode] . "<br/>"
                . "Nominativo: " . $arrCopertina['richiedente'][0]['cognome'][0][itaXml::textNode]
                . " " . $arrCopertina['richiedente'][0]['nome'][0][itaXml::textNode] . "<br/>"
                . "Codice Fiscale: " . $arrCopertina['richiedente'][0]['codice-fiscale'][0][itaXml::textNode] . "<br/>"
                . "Cittadinanza: " . $arrCopertina['richiedente'][0]['cittadinanza'][0][itaXml::textNode] . "<br/>"
                . "Nato a: " . $arrCopertina['richiedente'][0]['luogo-nascita'][0][itaXml::textNode]
                . " il " . $arrCopertina['richiedente'][0]['data-nascita'][0][itaXml::textNode] . "<br/>"
        ;

//Visualizzo la REsidenza se in Italia
        if ($arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]) {
            $descrizione = $descrizione . "Residente in: " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['indirizzo'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['comune'][0][itaXml::textNode]
                    . " (" . $arrCopertina['richiedente'][0]['residenza'][0]['residenza-italia'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
            ;
        }

        $descrizione = $descrizione . "<br/>"
                . "DATI IMPRESA <br/>"
                . "Ragione Sociale: " . $arrCopertina['impresa'][0]['denominazione'][0][itaXml::textNode] . "<br/>"
                . "Forma Giuridica: " . $arrCopertina['impresa'][0]['forma-giuridica'][0][itaXml::textNode] . "<br/>"
        ;

//Visualizzo la Sede Legale
        if ($arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]) {
            $descrizione = $descrizione . "Sede in: " . $arrCopertina['impresa'][0]['sede-legale'][0]['indirizzo'][0][itaXml::textNode]
                    . "&nbsp;&nbsp;&nbsp; " . $arrCopertina['impresa'][0]['sede-legale'][0]['comune'][0][itaXml::textNode]
                    . " (" . $arrCopertina['impresa'][0]['sede-legale'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
            ;
        }

        $descrizione = $descrizione . "<br/>"
                . "DATI ATTIVITA' <br/>"
                . "Indirizzo: " . $arrCopertina['impiantoProduttivo'][0]['indirizzo'][0][itaXml::textNode] . "<br/>"
                . "Comune: " . $arrCopertina['impiantoProduttivo'][0]['comune'][0][itaXml::textNode]
                . " (" . $arrCopertina['impiantoProduttivo'][0]['provincia'][0][itaXml::textNode] . ") <br/>"
                . " Cap: " . $arrCopertina['impiantoProduttivo'][0]['cap'][0][itaXml::textNode] . "<br/>"
                . "<br/> "
                . "DATI CATASTALI <br/>"
                . "Categoria: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['categoria'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Foglio: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['foglio'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Numero: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['numero'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Subalterno: " . $arrCopertina['impiantoProduttivo'][0]['dati-catastali'][0]['subalterno'][0][itaXml::textNode]
                . "<br/> <br/> "
                . "RECAPITI <br/>"
                . "Pec: " . $arrCopertina['recapiti'][0]['pec'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Telefono: " . $arrCopertina['recapiti'][0]['telefono'][0][itaXml::textNode]
                . "&nbsp;&nbsp;&nbsp;  Cellulare: " . $arrCopertina['recapiti'][0]['cellulare'][0][itaXml::textNode];
        $descrizione .= '</div>';
        return $descrizione;
    }

    public function getAllegatiRichiestaFo($prafolist_rec, $allegatiInfocamere) {
        
        $sql = "SELECT * FROM PRAFOFILES WHERE FOTIPO = '" . addslashes($prafolist_rec['FOTIPO']) . "'"
                . " AND FOPRAKEY = '" . addslashes($prafolist_rec['FOPRAKEY']) . "'";

        $praFoFiles_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, true);
        if (!$praFoFiles_tab) {
            return null;
        } else {
            return $praFoFiles_tab;
        }
        
    }
    
    public function apriPasso($praFoList_rec, $Propas_rec) {
        
        if ($praFoList_rec['FOTIPOSTIMOLO'] != praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_STAR_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]){

            $model = 'praPasso';
            $_POST = array();
            $_POST['rowid'] = $Propas_rec['ROWID'];
            $_POST['modo'] = "edit";
            //$_POST['perms'] = $this->perms;
            $_POST[$model . '_returnModel'] = '';
            $_POST[$model . '_returnMethod'] = 'returnPraPasso';
            itaLib::openForm($model);
            $objModel = itaModel::getInstance($model);
            $objModel->setEvent("openform");
            $objModel->parseEvent();
            
            return true;
        }
        return false;
    }

    
    public function getAllegato($prafolist_rec, $rowidAlle) {
        $sql = "SELECT * FROM PRAFOFILES WHERE ROW_ID = " . $rowidAlle;
        $prafofiles_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        //Out::msgInfo("PRAFOFILES", print_r($prafofiles_rec,true));
        
        $anno = substr($prafolist_rec['FOPRADATA'], 0, 4);
        $dir = $this->praLib->SetDirectoryPratiche($anno, $prafofiles_rec['FOPRAKEY'], $prafofiles_rec['FOTIPO']);
        
        //Out::msgInfo("Dir", $dir);
        
        
        return array('FILENAME' => $prafofiles_rec['FILENAME'], 'DATAFILE' => $dir . "/" . $prafofiles_rec['FILEFIL']);
    }
    
    public function getDataModelAcq($praFoList_rec, $dati = array()) {
        $datamodelArray = array();

        if ($praFoList_rec['FOTIPOSTIMOLO'] != praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_STAR_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA]) {
            // Stimolo collegato ad una pratica già riportata
            $datamodelArray = $this->getDataModelAcqStimolo($praFoList_rec);
        } else {
            // Nuova pratica
            $datamodelArray = $this->getDataModelAcqNuovaPratica($praFoList_rec);
        }

        return $datamodelArray;
    }

    public function getDataModelAcqNuovaPratica($praFoList_rec) {
        $datamodelArray = array();
        $this->praLib = new praLib();


        $arrayCopertina = json_decode($praFoList_rec['FOMETADATA'], true);

        $variazione = $arrayCopertina['oggettoComunicazione'][0]['azione'][0][itaXml::textNode];

        $procPrincipale = array();
        $procSecondari = array();
        foreach ($arrayCopertina['oggettoComunicazione'][0]['endoprocedimenti'][0] ['ns2:idEndoprocedimento'] as $endoProcedimenti) {

            $endo = $endoProcedimenti[itaXml::textNode];
            $endo1 = str_replace(" ", "_", $endo);

            $procAppoggio = array(
                'CODICE' => $endo,
                'ENDO1' => $endo1,
                'XMLINFO' => "XMLINFO_" . $endo1 . ".xml"
            );


            if (empty($procPrincipale)) {
                $procPrincipale = $procAppoggio;
            } else {
                array_push($procSecondari, $procAppoggio);
            }
        }

        if (!isset($procPrincipale)) {
            //@TODO - Caricamento non può essere fatto
            return false;
        }

        //Out::msgInfo("Procedimento Principale", print_r($procPrincipale, true));
        //Out::msgInfo("Procedimenti Secondari", print_r($procSecondari, true));

        $sql = "SELECT * FROM PRAFODECODE "
                . " WHERE PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_STAR_WS . "' "
                . " AND PRAFODECODE.FOSRCKEY = '" . $procPrincipale['ENDO1'] . "/" . $variazione . "'";

        $praFoDecode_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$praFoDecode_rec) {
            return false;
        }

        $sqlIteevt = "SELECT * FROM ITEEVT WHERE ITEPRA = '" . $praFoDecode_rec['FODESTPRO'] .
                "' AND IEVCOD = '" . $praFoDecode_rec['FODESTEVCOD'] .
                "' AND IEVTSP = " . $praFoDecode_rec['FODESTTSP'];
        $iteevt_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sqlIteevt, false);

        $arrayProRic = $this->getProric_rec($praFoList_rec, $praFoDecode_rec);


        /*
         * Riempire $datamodelArray con PROGES_REC e ANADES_REC
         * Vedi documento XMind
         */
        $arrayProges_rec = $this->getProges_rec($praFoList_rec, $praFoDecode_rec);


        // Carico vettore $$arrayAnades_rec con i dati di ANADES_REC
        $arrayAnades_rec = $this->getAnades_rec($praFoList_rec);


        /**
         * Sistema XMLINFO per pratiche Secondarie (Arcorpate) e per la principale
         */
        // Crea cartella di appoggio per la sessione corrente
        $pathFile = itaLib::createAppsTempPath('tmp' . $praFoList_rec['FOPRAKEY']);


        $indice = 0;

        foreach ($procSecondari as $app) {

            $indice ++;
            if ($indice > 99) {
                $progressivo = $indice;
            } else if ($indice > 9) {
                $progressivo = "0" . $indice;
            } else {
                $progressivo = "00" . $indice;
            }


            $sql = "SELECT * FROM PRAFODECODE "
                    . " WHERE PRAFODECODE.FOTIPO = '" . praFrontOfficeManager::TYPE_FO_STAR_WS . "' "
                    . " AND PRAFODECODE.FOSRCKEY = '" . $app['ENDO1'] . "/" . $variazione . "'";

            $praFoDecodeSec_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

            if ($praFoDecodeSec_rec) {

                $arrayxmlInfo = $this->getXmlInfo($praFoList_rec, $praFoDecodeSec_rec, $procSecondari, false, $progressivo, $app['CODICE']);

                $file = $pathFile . "/" . $app['XMLINFO'];

                $ItaXmlObj = new itaXML;

                $ItaXmlObj->noCDATA();

                $ItaXmlObj->toXML($arrayxmlInfo);
                $xmlInfoString = $ItaXmlObj->getXml();

                //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
                // Salvare il contenuto di questa stringa in un file
                file_put_contents($file, $xmlInfoString);
            }
        }

        /**
         * Genero XMLINFO per la pratica principale
         */
        $arrayxmlInfo = $this->getXmlInfo($praFoList_rec, $praFoDecode_rec, $procSecondari);

        $file = $pathFile . "/XMLINFO.xml";

        //Out::msgInfo("Directory XMLINF", $pathFile);

        $ItaXmlObj = new itaXML;

        $ItaXmlObj->noCDATA();

        $ItaXmlObj->toXML($arrayxmlInfo);
        $xmlInfoString = $ItaXmlObj->getXml();

        //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
        // Salvare il contenuto di questa stringa in un file
        file_put_contents($file, $xmlInfoString);

//Out::msgInfo("File XMLINFO ", print_r($file, true));
//return false;
//
//Out::msgInfo("Array XMLINFO ", print_r($arrayxmlInfo, true));
//
//return false;

        $arrayAllegati = $this->getArrayAllegati($praFoList_rec);


        $datamodel = array(
            'tipoInserimento' => "PRAFOLIST",
            'tipoReg' => "consulta",
            'PRAFOLIST_REC' => $praFoList_rec,
            'PRORIC_REC' => $arrayProRic,
            'PROGES_REC' => $arrayProges_rec,
            'ANADES_REC' => $arrayAnades_rec,
            'ALLEGATI' => $arrayAllegati,
            'XMLINFO' => $file,
            'esterna' => true,
            'starweb' => false,
            'escludiPassiFO' => true,
            'progressivoDaRichiesta' => false
        );

//        Out::msgInfo("Data Model ", print_r($datamodel, true));
//        return false;
        
        array_push($datamodelArray, $datamodel);
        unset($datamodel);  // Svuota il vettore
        //Out::msgInfo("Data Model Array", print_r($datamodelArray, true));
//Out::msgInfo("Dentro praFoList2DataModelStarWs ", print_r($praFoList_rec['ROW_ID'], true));


        return $datamodelArray;
    }
    
    public function getDataModelAcqStimolo($praFoList_rec) {
        $datamodelArray = array();
        $this->praLib = new praLib();


        //Trovo il fascicolo (PROGES) collegato allo stimolo da riportare
        $sql = "SELECT * FROM PRAFOLIST WHERE PRAFOLIST.FOIDPRATICA = '" . $praFoList_rec['FOIDPRATICA'] . "'"
                . " AND PRAFOLIST.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "'"
                . " AND PRAFOLIST.FOTIPOSTIMOLO = '" . praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_STAR_WS][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA] . "'";

        $prafolistPrinc_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$prafolistPrinc_rec) {
            return $datamodelArray;
        }

        $sql = "SELECT * FROM PROGES WHERE PROGES.GESNUM = " . $prafolistPrinc_rec['FOGESNUM'];

        $proges_rec = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql, false);

        if (!$proges_rec) {
            return $datamodelArray;
        }


        // Riempire $datamodelArray con PROGES_REC e ANADES_REC
        // Vedi documento XMind

        $arrayProges_rec = $this->getProges_recStimolo($praFoList_rec, $proges_rec);


        // Carico vettore $$arrayAnades_rec con i dati di ANADES_REC
        $arrayAnades_rec = $this->getAnadesStimolo_rec($praFoList_rec, $prafolistPrinc_rec);

        // carico il vettore $arrayProRic_rec
        $arrayProRic_rec = $this->getProRic_recStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        // Crea cartella di appoggio per la sessione corrente
        $pathFile = itaLib::createAppsTempPath('tmp' . $praFoList_rec['FOPRAKEY']);

        // Genero XMLINFO per la pratica principale
        $arrayxmlInfo = $this->getXmlInfoStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        $file = $pathFile . "/XMLINFO.xml";


        $ItaXmlObj = new itaXML;

        $ItaXmlObj->noCDATA();

        $ItaXmlObj->toXML($arrayxmlInfo);
        $xmlInfoString = $ItaXmlObj->getXml();

        //Out::msgInfo("Stringa XMLINFO ", print_r($xmlInfoString, true));
        // Salvare il contenuto di questa stringa in un file
        file_put_contents($file, $xmlInfoString);



//Out::msgInfo("File XMLINFO che creo ", $file);
//Out::msgInfo("Array XMLINFO ", print_r($arrayxmlInfo, true));

        $arrayAllegati = $this->getArrayAllegati($praFoList_rec);

        $datamodel = array(
            'tipoInserimento' => "PRAFOLIST",
            'PRAFOLIST_REC' => $praFoList_rec,
            'PROGES_REC' => $arrayProges_rec,
            'ANADES_REC' => $arrayAnades_rec,
            'PRORIC_REC' => $arrayProRic_rec,
            'ALLEGATI' => $arrayAllegati,
            'XMLINFO' => $file,
            'esterna' => true,
            'starweb' => false,
            'escludiPassiFO' => true,
            'progressivoDaRichiesta' => false,
            'tipoReg' => "integrazione"
        );
        //Out::msgInfo("Data Model ", print_r($datamodel, true));

        array_push($datamodelArray, $datamodel);
        unset($datamodel);  // Svuota il vettore
        //Out::msgInfo("Data Model Array", print_r($datamodelArray, true));
//Out::msgInfo("Dentro praFoList2DataModelStarWs ", print_r($praFoList_rec['ROW_ID'], true));


        return $datamodelArray;
    }

    private function getProric_rec($praFoList_rec, $praFoDecode_rec, $principale = 'true', $progressivo = '001') {
        //Out::msgInfo("Principale", $principale . $progressivo);
        if ($principale) {
            $ricnum = $praFoList_rec['FOIDPRATICA'];   // Numero della Richiesta
        } else {
            $ricnum = $praFoList_rec['FOIDPRATICA'] . "_" . $progressivo;   // Numero della Richiesta
        }
        $ricpro = $praFoDecode_rec['FODESTPRO'];  // "000001";  // Numero procedimento (ANAPRA.PRANUM)
        $riceve = $praFoDecode_rec['FODESTEVCOD'];   // Codice Evento  (6)
        $rictsp = $praFoDecode_rec['FODESTTSP'];  //Codice Sportello ("1")
        $ricstt = $praFoDecode_rec['FODESTSTT'];   // Codice Settore ("1")
        $ricatt = $praFoDecode_rec['FODESTATT'];    // Codice Attivita  ("1")
        $ricseg = "0";   // Tipo Segnalazione  ("0")

        $ricres = "000001";  // Codice Responsabile
        $anapra_rec = $this->praLib->GetAnapra($praFoDecode_rec['FODESTPRO']);

        if ($anapra_rec) {
            $ricres = $anapra_rec['PRARES'];   // ANAPRA.PRARES
        }

        $ricrun = '';
        if (!$principale) {
            $ricrun = $praFoList_rec['FOIDPRATICA'];
        }

        $arrayProRic = array(
            'RICNUM' => $ricnum,
            'RICKEY' => $ricnum,
            'RICPRO' => $ricpro,
            'RICRES' => $ricres,
            'RICEVE' => $riceve,
            'RICTSP' => $rictsp,
            'RICSTT' => $ricstt,
            'RICATT' => $ricatt,
            'RICSEG' => $ricseg,
            'RICRUN' => $ricrun,
//            'RICCOG' => "Rossi",
//            'RICNOM' => "Valentino",
//            'RICEMA' => "ppp@mail.it",
//            'RICFIS' => "frfgtg67y89y789o",
            'RICDRE' => date('Ymd'),
            'RICDAT' => $praFoList_rec['FOPRADATA'],
            'RICTIM' => $praFoList_rec['FOPRAORA'],
            'RICNPR' => substr($praFoList_rec['FOPROTDATA'], 0, 4) . $praFoList_rec['FOPROTNUM'],
            'RICDPR' => $praFoList_rec['FOPROTDATA'],
        );


        return $arrayProRic;
    }
    
    private function getProges_recStimolo($praFoList_rec, $proges_rec) {

        $gesDre = date("Ymd");
        $gesDri = $praFoList_rec['FOPRADATA'];
        $gesOra = $praFoList_rec['FOPRAORA'];
        $gesPra = "";
// $gesPra = $praFoList_rec['FOPRAKEY'] // Codice pratica per Regione Toscana è 'NGRLCN51D03Z600C-22062018-1208'
        $gesPro = $proges_rec['GESPRO'];    // ANAPRA.PRANUM  "000001";
        $gesTsp = $proges_rec['GESTSP'];   // PRAFODECODE.FODESTTSP  (1)
        $gesSpa = "0";
        $gesEve = $proges_rec['GESEVE'];
        $gesSeg = $proges_rec['GESSEG'];


        $gesRes = $proges_rec['GESRES'];


        $arrayProges = array(
            'GESDRE' => $gesDre,
            'GESDRI' => $gesDri,
            'GESORA' => $gesOra,
            'GESPRA' => $gesPra,
            'GESPRO' => $gesPro,
            'GESRES' => $gesRes,
            'GESTSP' => $gesTsp,
            'GESSPA' => $gesSpa,
            'GESEVE' => $gesEve,
            'GESSEG' => $gesSeg,
            'GESDCH' => ''
        );

        return $arrayProges;
    }

    private function getAnadesStimolo_rec($praFoList_rec, $praFoListPrinc_rec) {

        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);


        $desRuo = "0001";  // Codice Esibente
        $desNom = $praFoList_rec['FOESIBENTE'];
        $desInd = "";
        $desCap = "";
        $desCit = "";
        $desPro = "";
        $desEma = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];
        $desFis = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];


        $arrayAnades = array(
            'DESRUO' => $desRuo,
            'DESNOM' => $desNom,
            'DESIND' => $desInd,
            'DESCAP' => $desCap,
            'DESCIT' => $desCit,
            'DESPRO' => $desPro,
            'DESEMA' => $desEma,
            'DESFIS' => $desFis
        );

        return $arrayAnades;
    }

    private function getXmlInfoStimolo($praFoList_rec, $praFoListPrinc_rec, $proges_rec) {

        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);

        $arrayProRic_rec = $this->getProRic_recStimolo($praFoList_rec, $prafolistPrinc_rec, $proges_rec);

        $ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta


        /*
         * XML RICITE (Fittizio per caricamento allegati integrazione)
         */
        $arrayRicIte = array();
        $passo = array(
            'RICNUM' => $ricnum,
            'ITEKEY' => $ricnum,
        );
        $recordRicite[] = array(
            'RECORD' => array_map(array($this, 'encodeForXml'), $passo)
        );
        $arrayRicIte[] = array($recordRicite);



        $arrayRicDoc = array();

        $praLib = new praLib();

        // Riporto RICDOC - Leggo record di PRAFOFILES
        $sql = "SELECT * FROM PRAFOFILES "
                . " WHERE PRAFOFILES.FOTIPO = '" . $praFoList_rec['FOTIPO'] . "' "
                . " AND PRAFOFILES.FOPRAKEY = '" . $praFoList_rec['FOPRAKEY'] . "' ";

        $praFoFiles_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);


        foreach ($praFoFiles_tab as $praFoFiles_rec) {

            $iteKey = $ricnum;
            $fileName = $praFoFiles_rec['FILENAME'];
            $docSha2 = $dir . "/" . $praFoFiles_rec['FILEFIL'];
            $docPrt = "";

            $allegato = array(
                'ITEKEY' => $iteKey,
                'DOCNAME' => $fileName,
                'DOCSHA2' => $docSha2,
                'DOCPRT' => $docPrt
            );

//array_push($arrayRicDoc, $allegato);

            $recordRicDoc[] = array(
                'RECORD' => array_map(array($this, 'encodeForXml'), $allegato)
            );

            unset($allegato);  // Svuota il vettore
        }
        $arrayRicDoc[] = array($recordRicDoc);

// Riporto RICDAG
        $arrayRicDag = array();
        $recordRicDag = array();

//ESIBENTE
//        $recordRicDag[] = $this->getRicDag($proges_rec['GESPRO'], "ESIBENTE_NOME", $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode]);
        // Riporto le RICHIESTE_ACCORPATE, solo se è Procedimento Principale
        $arrayRichieste_Accorpate = array();


        $arrayProRic = array_map(array($this, 'encodeForXml'), $arrayProRic);


        $root = array(
            'PRORIC' => array($arrayProRic),
            'RICITE' => $arrayRicIte,
            'RICDOC' => $arrayRicDoc,
            'RICDAG' => $arrayRicDag,
            'RICHIESTE_ACCORPATE' => $arrayRichieste_Accorpate // array()
        );



//array_walk_recursive($root, array($this,'encodeForXml'));
//Out::msgInfo("Info", print_r($root, true));

        $arrayXmlInfo = array(
            'ROOT' => $root
        );

        return $arrayXmlInfo;
    }

    private function getProRic_recStimolo($praFoList_rec, $praFoListPrinc_rec, $proges_rec) {
        $arrayCopertina = json_decode($praFoListPrinc_rec['FOMETADATA'], true);

        $esibenteNome = $arrayCopertina['presentatore'][0]['nome'][0][itaXml::textNode];
        $esibenteCognome = $arrayCopertina['presentatore'][0]['cognome'][0][itaXml::textNode];
        $esibenteCodFisc = $arrayCopertina['presentatore'][0]['codice-fiscale'][0][itaXml::textNode];
        $esibenteMail = $arrayCopertina['recapiti'][0]['pec'][0][itaXml::textNode];

        $ricnum = $praFoList_rec['FOPRAKEY'];   // Numero della Richiesta
        $ricpro = $proges_rec['GESPRO'];  // "000001";  // Numero procedimento (ANAPRA.PRANUM)
        $riceve = $proges_rec['GESEVE'];   // Codice Evento  (6)
        $rictsp = $proges_rec['GESTSP'];  //Codice Sportello ("1")
        $ricstt = $proges_rec['GESSTT'];   // Codice Settore ("1")
        $ricatt = $proges_rec['GESATT'];    // Codice Attivita  ("1")
        $ricseg = "0";   // Tipo Segnalazione  ("0")
        $ricres = $proges_rec['GESRES'];   // ANAPRA.PRARES


        $ricrun = '';  // Si valorizza solo per le pratiche accorpate

        $ricrpa = $praFoListPrinc_rec['FOIDPRATICA'];   // FOPRAKEY DELLA PRATICA PADRE


        $arrayProRic = array(
            'RICNUM' => $ricnum,
            'RICKEY' => $ricnum,
            'RICPRO' => $ricpro,
            'RICRES' => $ricres,
            'RICEVE' => $riceve,
            'RICTSP' => $rictsp,
            'RICSTT' => $ricstt,
            'RICATT' => $ricatt,
            'RICSEG' => $ricseg,
            'RICRUN' => $ricrun,
            'RICCOG' => $esibenteCognome, // Dati Esibente
            'RICNOM' => $esibenteNome,
            'RICEMA' => $esibenteMail,
            'RICFIS' => $esibenteCodFisc,
            'RICDRE' => date('Ymd'),
            'RICDAT' => $praFoList_rec['FOPRADATA'],
            'RICTIM' => $praFoList_rec['FOPRAORA'],
            'RICNPR' => substr($praFoList_rec['FOPROTDATA'], 0, 4) . $praFoList_rec['FOPROTNUM'],
            'RICDPR' => $praFoList_rec['FOPROTDATA'],
            'RICRPA' => $ricrpa    // FOPRAKEY DELLA PRATICA PADRE
        );

        return $arrayProRic;
    }
    
    public function caricaRichiestaFO($prafolist_rec) {
        $ret_esito = null;
        if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito)) {
            $this->retStatus['Errori'] = 1;
            $this->retStatus['Status'] = false;
            $this->retStatus['Messages'] = "Errore di acquisizione: " . praFrontOfficeManager::$lasErrMessage;
            return false;
        }
        return $ret_esito;
    }

    private function impostaPraticaLetta($praFoList_rec){
        
        $starClient = new itaStarServiceClient();

// Inizializza i parametri per il collegamento (Url; UserName; Password ecc..)
        $this->setClientConfig($starClient);

        $param = array(
            'idPratica' => $praFoList_rec['FOIDPRATICA'],
            'stato' => "LETTA"
        );

        $retCall = $starClient->ws_SetStatoPratica($param);
        if (!$retCall) {
            $this->setErrCode(-1);
            $this->setErrMessage($starClient->getFault() . " " . $starClient->getError);
            return false;
        }
        $result = $starClient->getResult();
        
    }

    
}
