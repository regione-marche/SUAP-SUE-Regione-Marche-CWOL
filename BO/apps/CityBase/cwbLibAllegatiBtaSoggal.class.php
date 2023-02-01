<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegatiInterface.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';

class cwbLibAllegatiBtaSoggal extends cwbLibAllegati implements cwbLibAllegatiInterface {

    private $libDB_BGE;
    private $libDB_BTA_SOGG;

    protected function postConstruct() {
        $this->libDB_BGE = new cwbLibDB_BGE();
        $this->libDB_BTA_SOGG = new cwbLibDB_BTA_SOGG();
    }

    protected function caricaAllegatoCustom($chiaveTestata, $riga, $datiAggiuntivi = array(), $startedTransaction = false, $modVis = false) {
        $filtri = array(
            'PROGSOGG' => $chiaveTestata['PROGSOGG'],
            'RIGA_ALLEG' => $riga
        );
        //Lettura dal Db del documento
        $data = $this->libDB_BTA_SOGG->leggiBtaSoggal($filtri, false);
        //Conversione nell'array model
        $rowModel = $this->allegatoToAllegatoModel($data);
        //Spostamento dati da database ad alfresco se necessario
        if ($modVis !== false) {
            $status = $this->spostaDocumentoToDocumentale($rowModel, $datiAggiuntivi, $startedTransaction);
            if (!$status) {
                return false;
            }
        }
        return $rowModel;
    }

    protected function caricaAllegatiCustom($chiave = array()) {
        $filtri = array(
            'PROGSOGG' => $chiave['PROGSOGG']
        );
        $data = $this->libDB_BTA_SOGG->leggiBtaSoggal($filtri, true);
        
        $allegatiModel = array();
        foreach ($data as $row) {
            $allegatiModel[] = $this->allegatoToAllegatoModel($row);
        }
        return $allegatiModel;
    }
    
    public function getAutorModulo($datiProvenienza=null){
        return 'BTA';
    }
    
    public function getAutorNumero($datiProvenienza=null){
        return 19;
    }

    public function getChiaveNaturaNote() {
        return 'BTA_NOTE';
    }
    
    public function getNomeComponenteHeaderCustom() {
        return 'cwbComponentBtaSoggal';
    }

    public function getNomeComponenteBodyCustom() {
    }

    public function allegatoToAllegatoModel($allegato = array()) {
        if (!empty($allegato)) {
            $rowModel = $this->defineRowModel();
            $rowModel["CHIAVE_ESTERNA"] = array(
                'PROGSOGG' => $allegato['PROGSOGG']
            );
            $rowModel["RIGA"] = $allegato["RIGA_ALLEG"];
            $rowModel["DESCRIZIONE"] = $allegato["DES_ALLEG"];
            $rowModel["CORPO"] = $allegato["TESTOATTO"];
            $rowModel["NATURA"] = $allegato["NATURANOTA"];
            $rowModel["NOME"] = $allegato["NOME_ALLEG"];
            $rowModel["NODE_UUID"] = $allegato["UUID"];
            $rowModel["CODUTEINS"] = $allegato['CODUTEINS'];
            $rowModel['DATAINSER'] = $allegato['DATAINSER'];
            $rowModel['TIMEINSER'] = $allegato['TIMEINSER'];
            $rowModel["CODUTE"] = $allegato['CODUTE'];
            $rowModel['DATAOPER'] = $allegato['DATAOPER'];
            $rowModel['TIMEOPER'] = $allegato['TIMEOPER'];
            $rowModel['FLAG_DIS'] = $allegato['FLAG_DIS'];
            $rowModel['METADATI'] = array(
            );
            return $rowModel;
        }
    }

    public function allegatoModelToAllegato(&$allegato = array(), $rowModel = array()) {
        $allegato["PROGSOGG"] = $rowModel["CHIAVE_ESTERNA"]["PROGSOGG"];
        $allegato["RIGA_ALLEG"] = $rowModel["RIGA"];
        $allegato["DES_ALLEG"] = $rowModel["DESCRIZIONE"];
        $allegato["TESTOATTO"] = $rowModel["CORPO"];
        $allegato["NATURANOTA"] = $rowModel["NATURA"];
        $allegato["NOME_ALLEG"] = $rowModel["NOME"];
        $allegato["UUID"] = $rowModel["NODE_UUID"];
        $allegato['CODUTEINS'] = $rowModel["CODUTEINS"];
        $allegato['DATAINSER'] = $rowModel['DATAINSER'];
        $allegato['TIMEINSER'] = $rowModel['TIMEINSER'];
        $allegato['CODUTE'] = $rowModel["CODUTE"];
        $allegato['DATAOPER'] = $rowModel['DATAOPER'];
        $allegato['TIMEOPER'] = $rowModel['TIMEOPER'];
        $allegato['FLAG_DIS'] = $rowModel['FLAG_DIS'];
    }

    public function initBodyCustom($nameform) {
    }

    public function popolaBodyCustom($metadati, $nameform) {
    }

    public function getConfigDocumentale($datiAggiuntivi, $rowModel) {
        $configDocumentale = array();
        $bge_fepa00 = $this->libDB_BGE->leggiBgeFepa00();
        $idtipdoc = $bge_fepa00['IDTD_CA1B'];
        $configDocumentale['IDTIPDOC'] = $idtipdoc;
        $configDocumentale['PLACE'] = $this->getPlace($bge_fepa00);
        $configDocumentale['METADATA'] = $this->getMetadata($datiAggiuntivi, $rowModel);
        $configDocumentale['ASPECTS'] = $this->getAspectsFromTipdoc($idtipdoc);
        $configDocumentale['CODENTE'] = cwbParGen::getCodente();
        if (empty($configDocumentale['IDTIPDOC'])) {
            $this->setError(-1, "Tipo Documento non trovato ");
            return false;
        }
        if (empty($configDocumentale['PLACE'])) {
            $this->setError(-1, "Place per inserimento sul documentale non valorizzato ");
            return false;
        }

        return $configDocumentale;
    }

    private function getPlace($bge_fepa00) {
        $devLib = new devLib();
        $basePath = $devLib->getEnv_config('ALFCITY', 'codice', 'ALFRESCO_BASEPLACE', false);
        // se finisce per slash lo tolgo
        $config = $basePath['CONFIG'];
        if (substr($config, -1) === '/') {
            $config = substr_replace($config, "", -1);
        }
        $place = $config . $bge_fepa00['DOC_PATH_IN'];

        return $place;
    }

    private function getMetadata($datiAggiuntivi, $rowModel) {
        $metadata = array();
        $metadata['nome_allegato'] = $rowModel['NOME'];
        $metadata['desc_allegato'] = $rowModel['DESCRIZIONE'];
        $metadata['id_unita_doc'] = $rowModel['NOME'];
        $metadata['ger_uuid_padre'] = $datiAggiuntivi['UUID'];
        return $metadata;
    }

    protected function validaCustom($metadata) {
    }

    protected function leggiTipoDocumentoSpecifico($idTipoDocumento) {
        $filtri['IDTIPDOC'] = $idTipoDocumento;
        return $this->libDB_BGD->leggiGeneric('BGD_TIPDOC', $filtri, false);
    }

    public function caricaDatiHeader($chiaveTestata) {
        return $this->libDB_BTA_SOGG->leggiGeneric('BTA_SOGG', $chiaveTestata, false);   
    }

    public function popolaHeaderCustomSpecifica($metadati, $nameform) {
    }

}
