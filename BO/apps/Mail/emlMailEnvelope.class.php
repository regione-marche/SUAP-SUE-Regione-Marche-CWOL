<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    06.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Mail/emlLib.class.php');
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

class emlMailEnvelope {

    const TAG_MESSAGEDATA = 'messageData';
    const TAG_FROMADDRESS = 'fromAddress';
    const TAG_TOADDRESSES = 'toAddresses';
    const TAG_TOADDRESS = 'toAddress';
    const TAG_CCADDRESSES = 'ccAddresses';
    const TAG_CCADDRESS = 'ccAddress';
    const TAG_BCCADDRESSES = 'bccAddresses';
    const TAG_BCCADDRESS = 'bccAddress';
    const TAG_SUBJECT = 'subject';
    const TAG_BODY = 'body';
    const TAG_ATTACHMENTS = 'attachments';
    const TAG_ATTACHMENT = 'attachment';
    const TAG_FILENAME = 'fileName';
    const TAG_FILEPATH = 'filePath';
    const TAG_FILESTREAM = 'fileStream';
    const TAG_EXTRAPARAMETERS = 'extraParameters';
    const TAG_PARAMETER = 'parameter';
    const TAG_PARAMETER_KEY = 'key';
    const TAG_PARAMETER_VALUE = 'value';

    private $errCode;
    private $errMessage;
    private $envelopeDataArray;
    private $envelopeId;
    private $evpStatus;

    function __construct() {
        /*
         * 
         * inizializzo Array
         * 
         */
        $this->envelopeDataArray = array();
        $this->envelopeDataArray[self::TAG_MESSAGEDATA] = array();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getEnvelopeId() {
        return $this->envelopeId;
    }

    function getEvpStatus() {
        return $this->evpStatus;
    }

    function setEvpStatus($evpStatus) {
        $this->evpStatus = $evpStatus;
    }

    /**
     * 
     * Carico a basso livello l'array che generalizza i dati da inviare
     * 
     * @param type $dataArray
     */
    public function setEnvelopeDataArray($dataArray = array()) {
        $this->envelopeDataArray = $dataArray;
    }

    public function getEnvelopeDataArray() {
        return $this->envelopeDataArray;
    }

    /**
     * 
     * @return type
     */
    public function getFromAddress() {
        return $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_FROMADDRESS][itaXML::textNode];
    }

    public function getToAddresses() {
        $retArray = array();
        foreach ($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_TOADDRESSES][self::TAG_TOADDRESS] as $toAddress) {
            $retArray[] = $toAddress[itaXML::textNode];
        }
        return $retArray;
    }

    /**
     * Configura un indirizzo mittente 
     * 
     * @param string $value valore dell'indirizzo
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function setFromAddress($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA] = array();
        }
        $fromAddress = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_FROMADDRESS] = $fromAddress;
    }

    /**
     * Aggiunge un indirizzo di destinazione 
     * 
     * @param string $value valore dell'indirizzo
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function addToAddress($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_TOADDRESSES][self::TAG_TOADDRESS])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_TOADDRESSES][self::TAG_TOADDRESS] = array();
        }
        $toAddress = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_TOADDRESSES][self::TAG_TOADDRESS][] = $toAddress;
    }

    /**
     * Aggiunge un indirizzo di destinazione per conoscenza
     * 
     * @param string $value valore dell'indirizzo
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function addCcAddress($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_CCADDRESSES][self::TAG_CCADDRESS])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_CCADDRESSES][self::TAG_CCADDRESS] = array();
        }
        $ccAddress = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_CCADDRESSES][self::TAG_CCADDRESS][] = $ccAddress;
    }

    /**
     * Aggiunge un indirizzo di destinazione per conoscenza nascosto 
     * 
     * @param string $value valore dell'indirizzo
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function addBccAddress($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_BCCADDRESSES][self::TAG_BCCADDRESS])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_BCCADDRESSES][self::TAG_BCCADDRESS] = array();
        }
        $bccAddress = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_BCCADDRESSES][self::TAG_BCCADDRESS][] = $bccAddress;
    }

    /**
     * Configura il subject della mail 
     * 
     * @param string $value valore dell'oggetto mail
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function setSubject($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA] = array();
        }
        $subject = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_SUBJECT] = $subject;
    }

    /**
     * Configura il body della mail 
     * 
     * @param string $value valore del corpo mail
     * @param string $params parametri eventuali per il valore
     * 
     */
    public function setBody($value, $params = array()) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA] = array();
        }
        $body = array(
            itaXML::textNode => $value,
            itaXML::attribute => $params
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_BODY] = $body;
    }

    /**
     * Aggiunge un allegato da un sorgetnnte base64encoded
     * 
     * @param string $fileName nome originale del file
     * @param string $stream binario dell'allegato in formato base64
     */
    public function addAttachmentFromStream($fileName, $stream) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_ATTACHMENTS][self::TAG_ATTACHMENT])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_ATTACHMENTS][self::TAG_ATTACHMENT] = array();
        }
        $attachment = array(
            self::TAG_FILENAME => array(
                itaXML::textNode => $fileName
            ),
            self::TAG_FILESTREAM => array(
                itaXML::textNode => $stream
            )
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_ATTACHMENTS][self::TAG_ATTACHMENT][] = $attachment;
    }

    /**
     * Aggiunge un parametro generale alla busta
     * 
     * @param string $key chiave parametro
     * @param string $value valore parametro
     */
    public function addExtraParameter($key, $value) {
        if (!isset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_EXTRAPARAMETERS][self::TAG_PARAMETER])) {
            $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_EXTRAPARAMETERS][self::TAG_PARAMETER] = array();
        }
        $parameter = array(
            self::TAG_PARAMETER_KEY => array(
                itaXML::textNode => $key
            ),
            self::TAG_PARAMETER_VALUE => array(
                itaXML::textNode => $value
            )
        );
        $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_EXTRAPARAMETERS][self::TAG_PARAMETER][] = $parameter;
    }

    /**
     * 
     * Costruisce una string XML nel formato MessageData
     * @return string
     */
    public function getEnvelopeDataXML() {
        $XMLString = '';
        $xmlObj = new itaXML();
        $xmlObj->noCDATA();
        $xmlObj->toXml($this->getEnvelopeDataArrayEncoded(), "messageData");
        return $xmlObj->getXml();
    }

    private function encodeXmlNode(&$value, $key) {
        if ($key == itaXml::textNode) {
            $value = htmlentities(utf8_encode($value), ENT_XML1, 'UTF-8');
        }
        if ($key == itaXml::attribute) {
            $value = htmlentities(utf8_encode($value), ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }
    }

    private function getEnvelopeDataArrayEncoded() {
        $encodedArr = $this->envelopeDataArray;
        array_walk_recursive($encodedArr, array($this, 'encodeXmlNode'));
        return $encodedArr;
    }

    /**
     *  Metodo per chiedere status di un envelop
     * 
     * @return boolean
     */
    public function getEnvelopeStatus($idEnvelope) {
        if (!$idEnvelope) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Id Envelope non dichiararo.',
                'return' => array()
            );
            return $toReturn;
        }
        $emlLib = new emlLib();
        $envelope = $emlLib->getEnvelope($idEnvelope);
        if (!$envelope) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Id Envelope non trovato.',
                'return' => array()
            );
            return $toReturn;
        }
        if ($envelope['EVPMAIL_ID'] == '') {
            $toReturn = array(
                'esito' => false,
                'message' => 'Id padre non valorizzato.',
                'return' => array()
            );
            return $toReturn;
        }

        $receipt = $emlLib->getEnvelopeReceipt($envelope['EVPMAIL_ID']);
        if ($receipt) {
            $envelope['RECEIPT'] = $receipt;
        }

        $toReturn = array(
            'esito' => true,
            'message' => '',
            'return' => $envelope
        );
        return $toReturn;
    }

    public function disattivaEnvelope($idEnvelope) {
        if (!$idEnvelope) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Id Envelope non dichiararo.',
                'return' => array()
            );
            return $toReturn;
        }
        $emlLib = new emlLib();
        $envelope = $emlLib->getEnvelope($idEnvelope);
        if (!$envelope) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Id Envelope non trovato.',
                'return' => array()
            );
            return $toReturn;
        }
        if ($envelope['EVPSTATUS'] == 1) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Status dell\'Id Envelope = ' . $envelope['EVPSTATUS'] . ' - ' . $envelope['EVPLASTMESSAGE'],
                'return' => array()
            );
            return $toReturn;
        }
        if ($envelope['FLAG_DIS'] == 1) {
            $toReturn = array(
                'esito' => false,
                'message' => 'Envelope già disattivato da ' . $envelope['FLAG_DIS_UTE'] . ' il ' . $envelope['FLAG_DIS_DATA'] . ' alle ore ' . $envelope['FLAG_DIS_TIME'],
                'return' => array()
            );
            return $toReturn;
        }
        /*
         * setto FLAG_DIS del record MAIL_ENVELOPES
         */
        $emlLib = new emlLib();
        if (!$emlLib->disattivaEnvelope($envelope['ROW_ID'])) {
            $toReturn = array(
                'esito' => false,
                'message' => "Errore nella disattivazione di MAIL_ENVELOPES per ROW_ID = " . $envelope['ROW_ID'],
                'return' => array()
            );
            return $toReturn;
        }
        $toReturn = array(
            'esito' => true,
            'message' => "Disattivazione envelope ROW_ID = " . $envelope['ROW_ID'] . ' riuscita.',
            'return' => array()
        );
        return $toReturn;
    }

    public function loadEnvelope($idEnvelope) {
        if (!$idEnvelope) {
            $this->setErrMessage('Id envelope non passato');
            return false;
        }
        $this->envelopeId = $idEnvelope;
        $emlLib = new emlLib();
        // carico envelope
        $envelope = $emlLib->getEnvelope($idEnvelope);
        if (!$envelope) {
            $this->setErrMessage('Envelope id ' . $this->envelopeId . ' non trovato');
            return false;
        }

        // carico package
        $packageObj = emlSpoolManager::getPackageInstance();
        $packageObj->setPackageId($envelope['PACKAGES_ROWID']);
        $filePath = $packageObj->getPathPackage();

        $xmlEnvelope = file_get_contents($filePath . "/" . $envelope['EVPXMLDATA'] . ".xml");
        $xmlObj = new itaXML();
        $xmlObj->setXmlFromString($xmlEnvelope);
        $envelopeArray = $xmlObj->getArray();

        $env = $envelopeArray['messageData'][0];

        $this->setFromAddress($env['fromAddress'][0]['@textNode']);

        $tos = $env['toAddresses'];
        foreach ($tos as $key => $to) {
            $this->addToAddress($to['toAddress'][0]['@textNode']);
        }

        $ccs = $env['ccAddresses'];
        foreach ($ccs as $key => $cc) {
            $this->addCcAddress($cc['ccAddress'][0]['@textNode']);
        }

        $bccs = $env['bccAddresses'];
        foreach ($bccs as $key => $bcc) {
            $this->addBccAddress($bcc['bccAddress'][0]['@textNode']);
        }

        $this->setSubject($env['subject'][0]['@textNode']);

        $this->setBody($env['body'][0]['@textNode']);

        $attachments = $env['attachments'];
        foreach ($attachments as $key => $attachment) {
            $this->addAttachmentFromStream($attachment['attachment'][0]['fileName'][0]['@textNode'], $attachment['attachment'][0]['fileStream'][0]['@textNode']);
        }

        return true;
    }

    public function getAttachments() {
        $toReturn = array();
        foreach ($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_ATTACHMENTS][self::TAG_ATTACHMENT] as $key => $value) {
            $toReturn[] = array('fileName' => $value['fileName']['@textNode'], 'fileStream' => $value['fileStream']['@textNode']);
        }

        return $toReturn;
    }

    public function getBody() {
        return $this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_BODY];
    }

    public function cleanAttachments() {
        unset($this->envelopeDataArray[self::TAG_MESSAGEDATA][self::TAG_ATTACHMENTS]);
    }

    public function updateData() {
        $xml = $this->getEnvelopeDataXML();

        $emlLib = new emlLib();
        $envelope = $emlLib->getEnvelope($this->envelopeId);
        if (!$envelope) {
            $this->setErrMessage('Envelope id ' . $this->envelopeId . ' non trovato');
            return false;
        }

        if (intval($envelope['EVPSTATUS']) === 1) {
            $this->setErrMessage('Envelope id ' . $this->envelopeId . ' già spedito, impossibile modificarlo');
            return false;
        }

        // carico package
        $packageObj = emlSpoolManager::getPackageInstance();
        $packageObj->setPackageId($envelope['PACKAGES_ROWID']);
        $filePath = $packageObj->getPathPackage();

        if (file_put_contents($filePath . "/" . $envelope['EVPXMLDATA'] . ".xml", $xml) === false) {
            $this->setErrMessage('Errore di salvataggio dell\'XML nella busta di lavoro');
            return false;
        }

        return true;
    }

}
