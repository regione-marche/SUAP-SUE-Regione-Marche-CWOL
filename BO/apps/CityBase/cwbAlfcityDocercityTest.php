<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPHPDocumentaleUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPAlfcity/itaDocumentaleAlfrescoUtils.class.php';


define('ALFRESCO_PLACE', '/app:company_home/cm:cityware/cm:ENTE_042002/cm:AOO_atdaa/cm:media/cm:protocollo');
//define('DOC_TYPE', 'DOC_MASTER_CW');    
define('DOC_TYPE', 'SDI_CP_RICEZ');
define('COD_ENTE', '042002');
define('COD_AOO', 'atdaa');

//define('COD_ENTE', 'FALCONARA');  
//define('COD_AOO', 'AOO_FALCO');  

function cwbAlfcityDocercityTest() {
    $cwbAlfcityTest = new cwbAlfcityDocercityTest();
    $cwbAlfcityTest->parseEvent();
    return;
}

class cwbAlfcityDocercityTest extends itaModel {

    private $documentale;
    private $documentaleUtils;

    function __construct() {
        parent::__construct();
        try {
            $this->nameForm = 'cwbAlfcityDocercityTest';
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            $this->close();
        }
    }

    public function parseEvent() {
        //$this->documentale = new itaDocumentale('DOCERCITY');
        $this->documentale = new itaDocumentale('ALFCITY');
        $this->documentaleUtils = new itaPHPDocumentaleUtils('Alfresco');

        //   $this->version();  
        //  $this->insertDocument();    
        //   $this->query();
        //   $this->queryAll();
        //   $this->queryByUUID();
        
        //$this->placeByUUID();
        // $this->countQuery();
        //$this->countQueryAll();
        //  $this->deleteDocumentByUUID();
        //   $this->contentByUUID();
        //  $this->updateDocumentMetadata();
        // helper fatturazione su protocollo
        $uuid = $this->inserisciFlusso();
        $uuidmt = $this->inserisciAnnesso($uuid);
        //    $this->inserisciProtocollo();
        $uuidfatt = $this->spacchettaFlusso($uuid);    
        
            $this->queryByUUID($uuid);
        $this->queryByUUID($uuidmt);
        $this->queryByUUID($uuidfatt[0]);
        //   $this->spacchettaFlussi();
        //    $this->accettaFattura();
//          $this->rifiutaFattura();
        //  $this->aggiornaMetadatiInvioEsitoFattura();
        //   $this->aggiornaMetadatiRicezioneDT();
    }

    private function version() {
        $result = $this->documentale->version();

        Out::msgInfo("dump", $this->documentale->getResult());
    }

    private function insertDocument() {
        //   $fileName = 'provaItaEngine.pdf';
        $fileName = 'flussoprova.xml';
        $mimeType = 'text/plain';
        //    $mimeType = 'application/pdf';
        $contentString = 'Questo file e un flusso di prova caricato da itaEngine';
        //$contentString = file_get_contents('C:/Users/luca.cardinali/Desktop/Nuova cartella/Copia.pdf');
        //    $contentString = file_get_contents('C:/temp/test.pdf');
        $aspects = array(
            'asp_prot' => 1,
            'asp_fasc' => 1,
            'asp_com' => 1,
        );

        $props = array(
            'codfiscale_fornitore' => 'dfsdfsdfsddf',
            'codice_destinatario' => 'csasd23',
            'data_acquisizione' => '*DATE*11-05-2016',
            'idfiscale_fornitore' => 'dfsd44',
            'id_sdi' => '55433',
            'modo_inserimento' => '2',
            'nome_flusso' => 'dasasdd.xml',
            'note_flusso' => ' ',
            'num_fatture_accettate' => '0',
            'num_fatture_accettate_dt' => '0',
            'num_fatture' => '1',
            'num_fatture_rifiutate' => '0',
            'posizione_flusso' => '0',
            'ragione_soc_fornitore' => 'fgdg5',
            'stato_flusso' => '0',
            'uuid_collegato' => ' ', // lasciare vuoto
            'versione_flusso' => 'sdi11',
            // protocollazione            
            'prot_anno' => '2016',
            'prot_data' => '*DATE*11-05-2016',
            'prot_destinatario' => ' ',
            'prot_mittente' => 'SDI',
            'prot_numero' => '111',
            'prot_oggetto' => 'Invio file 23123',
            'prot_riservato' => '*BOOL*0',
            'prot_tipo' => 'A',
            //dati comuni            
            'com_aoo' => 'c_a564',
            'com_area_cityware' => 'F',
            'com_codice_ipa' => 'c_a564',
            'com_descrizione' => 'dasasdd.xml',
            'com_ente' => '2322',
            'com_modulo_cityware' => 'ES',
            'com_nomefile' => 'dasasdd.xml',
            'com_organigramma_corrente' => '1.2.1 ufficio protocollo',
            'com_ruolo_corrente' => 'Protocollatore',
            'com_utente_login' => 'CED',
            // fascicolazione            
            'fasc_uuid' => '345-rfg-345-34|asdsd-34-da-34'     // | per multivalue          
        );
        $result = $this->documentale->insertDocument(DOC_TYPE, ALFRESCO_PLACE, $fileName, $mimeType, $contentString, $aspects, $props);
        if ($result) {
            Out::msgInfo("dump", print_r($this->documentale->getResult(), true));
        } else {
            Out::msgStop("Errore", $this->documentale->getErrCode() . ' - ' . $this->documentale->getErrMessage());
        }
    }

    private function countQuery() {
        $aspects = array();
        $props = array(
            'stato_flusso' => 1,
        );
        if ($this->documentale->countQuery(DOC_TYPE, COD_ENTE, COD_AOO, $aspects, $props)) {
            Out::msgInfo("dump", $this->documentale->getResult());
        } else {
            Out::msgStop("errore", $this->documentale->getErrMessage());
        }
    }

    private function countQueryAll() {
        $aspects = array();
        $props = array();
        if ($this->documentale->countQueryAll(DOC_TYPE, COD_ENTE, COD_AOO, $aspects, $props, '4924N9')) {
            Out::msgInfo("dump", print_r($this->documentale->getResult(), true));
        } else {
            Out::msgStop("dump", print_r($this->documentale->getErrMessage(), true));
        }
    }

    private function deleteDocumentByUUID() {
        $uuid = 'a50250b1-7c41-4822-a318-2fa9beda59f8';
        if ($this->documentale->deleteDocumentByUUID($uuid)) {
            Out::msgInfo("dump", 'cancellato!');
        } else {
            Out::msgStop("dump", 'non cancellato!');
        }
    }

    private function placeByUUID() {
        $uuid = 'bb5d66f8-c003-4d3a-b5be-4ec90376af16';
        if ($this->documentale->placeByUUID($uuid)) {
            Out::msgInfo("dump", $this->documentale->getResult());
        }
    }

    private function updateDocumentMetadata() {
        $aspects = array();
        $props = array(
            'com_utente_login' => 'MISTERX',
        );
        $uuid = 'bad206c9-ccde-4f85-bf76-cd347c298318';
        if ($this->documentale->updateDocumentMetadata($uuid, DOC_TYPE, $aspects, $props)) {
            Out::msgInfo("dump", "Aggiornamento metadati effettuato");
        } else {
            Out::msgInfo("dump", "errore");
        }
    }

    private function query() {
        $aspects = array();
        $props = array(
            'stato_flusso' => 0
        );
        if ($this->documentale->query(DOC_TYPE, COD_ENTE, COD_AOO, $aspects, $props)) {
            Out::msgInfo("dump", print_r($this->estraiRisultato($this->documentale->getResult()), true));
            file_put_contents("C:/temp/risultato.txt", print_r($this->documentale->getResult(), true));
        } else {
            Out::msgStop("Errore", $this->documentale->getErrCode() . ' - ' . $this->documentale->getErrMessage());
        }
    }

    private function queryAll() {
        $aspects = array();
        $props = array();
        if ($this->documentale->queryAll(DOC_TYPE, COD_ENTE, COD_AOO, $aspects, $props)) {
            Out::msgInfo("dump", print_r($this->documentale->getResult(), true));
        } else {
            Out::msgInfo("dump", print_r($this->documentale->getErrMessage(), true));
        }
    }

    private function queryByUUID($uuid) {
        if ($this->documentale->queryByUUID($uuid)) {
            Out::msgInfo("dump", print_r($this->documentale->getResult(), true));
        } else {
            Out::msgInfo("dump", print_r($this->documentale->getErrMessage(), true));
        }
    }

    private function contentByUUID() {
        $uuid = '883e6f12-c4b9-45ee-91d1-901c125b149d';
        if ($this->documentale->contentByUUID($uuid)) {
            if ($this->documentale->getResult() != null) {
                file_put_contents("C:\\temp\\contenuto.pdf", $this->documentale->getResult());
                Out::msgInfo("dump", "Salvato in C:\\temp\\contenuto.pdf");
            } else {
                Out::msgStop("Errore", $this->documentale->getErrMessage());
            }
        } else {
            Out::msgStop("Errore", $this->documentale->getErrMessage());
        }
    }

    private function inserisciFlusso() {
        $flusso = file_get_contents('C:\\temp\\IT01879020517_b61v5.xml.p7m');
        $data = array();
        $data['CODICEFISCALE'] = 'dasdasddas';
        $data['CODICEDESTINATARIO'] = 'UFBGLJ';
        $data['IDFISCALEIVA'] = 'asdsdaasd';
        $data['IDENTIFICATIVOSDI'] = '62593285';
        $data['TOTFATTURE'] = '';
        $data['FORNITORE_DENOMINAZIONE'] = 'pippo';
        $data['VERSIONEFLUSSO'] = 'FPA12';
        $data['ANNO'] = '2017';
        $data['NUMERO'] = '1';
        $data['OGGETTO'] = 'flusso 62593285';
        $data['RISERVATO'] = '0';
        $data['TIPO'] = 'A';
        $data['RUOLO'] = 'protocollatore';

        $this->documentaleUtils->setDizionario($data);
        if ($this->documentaleUtils->inserisciFlussoCP('IT01879020517_b61v5.xml.p7m', $flusso)) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
            return $this->documentaleUtils->getResult();
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function inserisciAnnesso($uuidPadre) {
        $mt = file_get_contents('C:\\temp\\IT01879020517_b61v5_MT_001.xml');
        $data = array();
        $data['IDENTIFICATIVOSDI'] = '62593285';
        $data['TIPOANNESSO'] = 'MT';
        $data['UUIDPADRE'] = $uuidPadre;
        $data['IDUNITADOC'] = 'IT01879020517_b61v5';

        if ($this->documentaleUtils->inserisciAnnessoCP("IT01879020517_b61v5_MT_001.xml", $mt)) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
            return $this->documentaleUtils->getResult();
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function inserisciProtocollo() {
        if ($this->documentaleUtils->inserisciProtocollo("provapro.txt", "questo è un protocollo")) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function spacchettaFlusso($uuid) {
        if ($this->documentaleUtils->spacchettaFlusso($uuid)) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
            return $this->documentaleUtils->getResult();
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function spacchettaFlussi() {
        if ($this->documentaleUtils->spacchettaTuttiIFlussi()) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function accettaFattura() {
        if ($this->documentaleUtils->accettaFattura("64466197-3522-4f92-a342-aaf6d085c823")) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function rifiutaFattura() {
        if ($this->documentaleUtils->rifiutaFattura("95f041da-8c9a-465c-8111-aa4f7f1cd720")) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function aggiornaMetadatiInvioEsitoFattura() {
        if ($this->documentaleUtils->aggiornaMetadatiInvioEsitoFattura("95f041da-8c9a-465c-8111-aa4f7f1cd720")) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function aggiornaMetadatiRicezioneDT() {
        if ($this->documentaleUtils->aggiornaMetadatiRicezioneDT("95f041da-8c9a-465c-8111-aa4f7f1cd720", true)) {
            Out::msgInfo("salvato", $this->documentaleUtils->getResult());
        } else {
            Out::msgStop("Errore", $this->documentaleUtils->getErrMessage());
        }
    }

    private function estraiRisultato($queryResult) {
        if ($queryResult['QUERYRESULT'] && $queryResult['QUERYRESULT'][0]['RESULTS']) {
            return $queryResult['QUERYRESULT'][0]['RESULTS'];
        }

        return null;
    }

    public function GetMetadatiDocumentaleByUUID($UUID, $returnNodeValuesPlain = true) {
        $documentale = new itaDocumentale('ALFCITY');
        $documentale->setUtf8_encode(true);
        $documentale->setUtf8_decode(true);
        $ResultQery = $documentale->queryByUUID($UUID);
        if (!$ResultQery) {
            $this->setErrCode(-1);
            $this->setErrMessage("UUID non presente. " . $documentale->getErrMessage());
            return false;
        }

        $ArrayResultComplete = $documentale->getResult();
        $ArrayDati = array();
        $ArrayResult = $ArrayResultComplete['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'];

        if ($returnNodeValuesPlain) {
            foreach ($ArrayResult as $Valore) {
                $Name = $Valore['NAME'][0]['@textNode'];
                $Value = $Valore['VALUE'][0]['@textNode'];
                $ArrayDati[$Name] = $Value;
            }
        } else {
            // Valutare bene quale attributo tornere.
            foreach ($ArrayResult as $Valore) {
                $Name = $Valore['NAME'][0]['@textNode'];
                $Value = $Valore['VALUE'][0]['@textNode'];
                $Attributo = $Valore['VALUE'][0]['@attributes'];
                $ArrayDati[$Name] = array('Valore' => $Value, 'Attributo' => $Attributo);
            }
        }
//        Out::msgInfo('daty', print_r($ArrayDati, true));
        return $ArrayDati;
    }
}

