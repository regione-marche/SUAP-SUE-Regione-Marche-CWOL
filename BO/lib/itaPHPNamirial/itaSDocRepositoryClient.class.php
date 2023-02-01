<?php

require_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocClient.class.php';

class SDocRepository extends SDoc {

    /**
     * 
     * @return boolean
     */
    public function ws_getRepositories() {
        $operation = 'getRepositories';
        $action = 'http://tempuri.org/IRepositoryService/getRepositories';
        return $this->ws_call($operation, array(), $action);
    }

    /**
     * 
     * @param array $param
     * @return boolean
     */
    public function ws_getRepositoryInfo($param) {
        $operation = 'getRepositoryInfo';
        $action = 'http://tempuri.org/IRepositoryService/getRepositoryInfo';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * 
     * @return boolean
     */
    public function ws_getReservedExtensions() {
        $operation = 'getReservedExtensions';
        $action = 'http://tempuri.org/IRepositoryService/getReservedExtensions';
        return $this->ws_call($operation, array(), $action);
    }

    /**
     * Chiamata per Conservazione Documento
     * @param arry $param
     * @return boolean
     */
    public function ws_createDocument($param) {
        $operation = 'createDocument';
        $action = 'http://tempuri.org/IRepositoryService/createDocument';
        /*
         * Creazione XML già serializzato
         */
        /* @var nusoap::soalval */
        $ns = $this->getTempns();
        $Xml = '     
        <' . $ns . ':repositoryId>' . $param['repositoryId'] . '</' . $ns . ':repositoryId>
            <' . $ns . ':properties xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <a:KeyValueOfstringanyType>
                    <a:Key>PdV_VerificaFirmaFiles</a:Key>
                    <a:Value i:type="b:boolean" xmlns:b="http://www.w3.org/2001/XMLSchema">false</a:Value>
                </a:KeyValueOfstringanyType>
                <a:KeyValueOfstringanyType>
                    <a:Key>Firma_OggettiProduttore</a:Key>
                    <a:Value i:type="b:boolean" xmlns:b="http://www.w3.org/2001/XMLSchema">false</a:Value>
                </a:KeyValueOfstringanyType>
            </' . $ns . ':properties>
            <' . $ns . ':contentStream xmlns:a="http://docs.oasis-open.org/ns/cmis/core/200908/" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <a:filename>' . $param['filename'] . '</a:filename>
                <a:length>' . $param['length'] . '</a:length>
                <a:mimeType/>
                <a:stream>' . $param['stream'] . '</a:stream>
            </' . $ns . ':contentStream>
            <' . $ns . ':versioningState>none</' . $ns . ':versioningState>
            <' . $ns . ':policies xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance"/>
        ';

        return $this->ws_call($operation, $Xml, $action);
    }

    public function ws_requestCreateDocumentFile($param) {
        $operation = 'requestCreateDocumentFile';
        $action = 'http://tempuri.org/IRepositoryService/requestCreateDocumentFile';

        /*
         * Creazione XML già serializzato
         */
        /* @var nusoap::soalval */
        $ns = $this->getTempns();
        $Xml = '     
            <' . $ns . ':properties xmlns:a="http://schemas.microsoft.com/2003/10/Serialization/Arrays" xmlns:i="http://www.w3.org/2001/XMLSchema-instance">
                <a:KeyValueOfstringanyType>
                    <a:Key>PdV_VerificaFirmaFiles</a:Key>
                    <a:Value i:type="b:boolean" xmlns:b="http://www.w3.org/2001/XMLSchema">false</a:Value>
                </a:KeyValueOfstringanyType>
                <a:KeyValueOfstringanyType>
                    <a:Key>Firma_OggettiProduttore</a:Key>
                    <a:Value i:type="b:boolean" xmlns:b="http://www.w3.org/2001/XMLSchema">false</a:Value>
                </a:KeyValueOfstringanyType>
            </' . $ns . ':properties>
            <' . $ns . ':fileId>' . $param['fileId'] . '</' . $ns . ':fileId>
            ';

        return $this->ws_call($operation, $Xml, $action);
    }

    public function ws_getPendingRequest($param) {
        $operation = 'getPendingRequest';
        $action = 'http://tempuri.org/IRepositoryService/getPendingRequest';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * Chiamata per Estrazione del rapporto di versamento
     * @param arry $param
     * @return boolean
     */
    public function ws_exportRdV($param) {
        $operation = 'exportRdV';
        $action = 'http://tempuri.org/IRepositoryService/exportRdV';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * Chiamata per estrarre lo stato del versamento
     * @param arry $param
     * @return boolean
     */
    public function ws_getPdV($param) {
        $operation = 'getPdV';
        $action = 'http://tempuri.org/IRepositoryService/getPdV';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * Chiamata per generare il pacchetto di distribuzione generato
     * @param arry $param
     * @return boolean
     */
    public function ws_createPdD($param) {
        $operation = 'createPdD';
        $action = 'http://tempuri.org/IRepositoryService/createPdD';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * Chiamata per Estrazione Elenco di RDV specifici
     * @param arry $param
     * @return boolean
     */
    public function ws_getAllRdV($param) {
        //
        $operation = 'getAllRdV';
        $action = 'http://tempuri.org/IRepositoryService/getAllRdV';
        return $this->ws_call($operation, $param, $action);
    }

}
