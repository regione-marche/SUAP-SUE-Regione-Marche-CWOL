<?php

require_once ITA_LIB_PATH . '/itaPHPNamirial/itaSDocClient.class.php';

class SDocTransfer extends SDoc {

    /**
     * 
     * @return boolean
     */
    public function ws_initializeUploadFileTemp($param) {
        $operation = 'initializeUploadFileTemp';
        $action = 'http://tempuri.org/ITransferService/initializeUploadFileTemp';
        return $this->ws_call($operation, $param, $action);
    }

    /**
     * 
     * @param array $param
     * @return boolean
     */
    public function ws_uploadFileTemp($param, $additionalSoapHeaders = array(), $attachments = array()) {
        $operation = 'RemoteFileInfo';
        $action = 'http://tempuri.org/ITransferService/uploadFileTemp';
        $ns = $this->getTempns();
        $xml .= '<' . $ns . ':FileByteStream>' . $param['FileByteStream'] . '</' . $ns . ':FileByteStream>';
        return $this->ws_call($operation, $xml, $action, $additionalSoapHeaders, $attachments);
    }

    /**
     * 
     * Prototipo con MTOM
     * 
     * @param array $param
     * @return boolean
     */
    public function ws_uploadFileTempXop($param, $additionalSoapHeaders = array(), $attachments = array()) {
        $operation = 'RemoteFileInfo';
        $ns = $this->getTempns();
        $action = 'http://tempuri.org/ITransferService/uploadFileTemp';
        $xml = '<' . $ns . ':RemoteFileInfo>';
        $xml .='<xop:Include href="' . $param['FileByteStream'] . '"/>';
        $xml .= '<' . $ns . ':/RemoteFileInfo>';
        return $this->ws_call($operation, $xml, $action, $additionalSoapHeaders, $attachments);
    }

}
