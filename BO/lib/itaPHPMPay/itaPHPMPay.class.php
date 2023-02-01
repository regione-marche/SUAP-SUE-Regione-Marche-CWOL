<?php

/**
 *
 * Classe per collegamento ws IRIDE
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPIride
 * @author     Mario Mazza <martio.mazza@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    07.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php';
require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class itaPHPMPay {

    private $urlRID;
    private $urlRedirect;
    private $urlPID;
    private $codicePortale;
    private $encryptIV;
    private $encryptKey;
    private $timeout;
    private $debug;
    private $urlRitorno;
    private $urlNotifica;
    private $urlBack;
    private $errMessage;

    function __construct($libErr = null) {
        
    }

    function __destruct() {
        
    }

    public function getUrlRID() {
        return $this->urlRID;
    }

    public function getUrlRedirect() {
        return $this->urlRedirect;
    }

    public function getUrlPID() {
        return $this->urlPID;
    }

    public function getCodicePortale() {
        return $this->codicePortale;
    }

    public function getEncryptIV() {
        return $this->encryptIV;
    }

    public function getEncryptKey() {
        return $this->encryptKey;
    }

    public function getTimeout() {
        return $this->timeout;
    }

    public function getDebug() {
        return $this->debug;
    }

    public function getUrlRitorno() {
        return $this->urlRitorno;
    }

    public function getUrlNotifica() {
        return $this->urlNotifica;
    }

    public function getUrlBack() {
        return $this->urlBack;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setUrlRID($urlRID) {
        $this->urlRID = $urlRID;
    }

    public function setUrlRedirect($urlRedirect) {
        $this->urlRedirect = $urlRedirect;
    }

    public function setUrlPID($urlPID) {
        $this->urlPID = $urlPID;
    }

    public function setCodicePortale($codicePortale) {
        $this->codicePortale = $codicePortale;
    }

    public function setEncryptIV($encryptIV) {
        $this->encryptIV = $encryptIV;
    }

    public function setEncryptKey($encryptKey) {
        $this->encryptKey = $encryptKey;
    }

    public function setTimeout($timeout) {
        $this->timeout = $timeout;
    }

    public function setDebug($debug) {
        $this->debug = $debug;
    }

    public function setUrlRitorno($urlRitorno) {
        $this->urlRitorno = $urlRitorno;
    }

    public function setUrlNotifica($urlNotifica) {
        $this->urlNotifica = $urlNotifica;
    }

    public function setUrlBack($urlBack) {
        $this->urlBack = $urlBack;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function prepareBufferDatiRequest($request) {

        $BD = '<PaymentRequest>' .
                '<PortaleID>' . $this->getCodicePortale() . '</PortaleID>' .
                '<Funzione>PAGAMENTO</Funzione>' .
                '<URLDiRitorno>' . htmlentities($this->getUrlRitorno(), ENT_COMPAT, 'ISO-8859-1') . '</URLDiRitorno>' .
                '<URLDiNotifica>' . htmlentities($this->getUrlNotifica(), ENT_COMPAT, 'ISO-8859-1') . '</URLDiNotifica>' .
                '<URLBack>' . htmlentities($this->getUrlBack(), ENT_COMPAT, 'ISO-8859-1') . '</URLBack>' .
                '<CommitNotifica>' . $request['CommitNotifica'] . '</CommitNotifica>' .
                '<UserData>' .
                '<EmailUtente>' . $request['EmailUtente'] . '</EmailUtente>' .
                '<IdentificativoUtente>' . $request['IdentificativoUtente'] . '</IdentificativoUtente>' .
                '</UserData>' .
                '<ServiceData>' .
                '<CodiceUtente>' . $request['CodiceUtente'] . '</CodiceUtente>' .
                '<CodiceEnte>' . $request['CodiceEnte'] . '</CodiceEnte>' .
                '<TipoUfficio>' . $request['TipoUfficio'] . '</TipoUfficio>' .
                '<CodiceUfficio>' . $request['CodiceUfficio'] . '</CodiceUfficio>' .
                '<TipologiaServizio>' . $request['TipologiaServizio'] . '</TipologiaServizio>' .
                '<NumeroOperazione>' . $request['NumeroOperazione'] . '</NumeroOperazione>' .
                '<NumeroDocumento>' . $request['NumeroDocumento'] . '</NumeroDocumento>' .
                '<AnnoDocumento>' . $request['AnnoDocumento'] . '</AnnoDocumento>' .
                '<Valuta>' . $request['Valuta'] . '</Valuta>' .
                '<Importo>' . $request['Importo'] . '</Importo>' .
                '<MarcaDaBolloDigitale>' .
                '<ImportoMarcaDaBolloDigitale>' . $request['ImportoMarcaDaBolloDigitale'] . '</ImportoMarcaDaBolloDigitale>' .
                '<SegnaturaMarcaDaBolloDigitale>' . $request['SegnaturaMarcaDaBolloDigitale'] . '</SegnaturaMarcaDaBolloDigitale>' .
                '<TipoBolloDaErogare>' . $request['TipoBolloDaErogare'] . '</TipoBolloDaErogare>' .
                '<ProvinciaResidenza>' . $request['ProvinciaResidenza'] . '</ProvinciaResidenza>' .
                '</MarcaDaBolloDigitale>' .
                '<DatiSpecifici>' . $request['DatiSpecifici'] . '</DatiSpecifici>' .
                '</ServiceData>' .
//                        '<AccountingData>' .
//                        '<ImportiContabili>' .
//                        '<ImportoContabile>' .
//                        '<Identificativo></Identificativo>' .
//                        '<Valore></Valore>' .
//                        '</ImportoContabile>' .
//                        '</ImportiContabili>' .
//                        '<EntiDestinatari>' .
//                        '<EnteDestinatario>' .
//                        '<CodiceEntePortaleEsterno>111111111111</CodiceEntePortaleEsterno>' .
//                        '<DescrEntePortaleEsterno>Ente 111111111111</DescrEntePortaleEsterno>' .
//                        '<Valore>' . $importo . '</Valore>' .
//                        '<Causale><![CDATA[Causale per ente 111111111111]]></Causale>' .
//                        '<ImportoContabileIngresso></ImportoContabileIngresso>' .
//                        '<ImportoContabileUscita></ImportoContabileUscita>' .
//                        '<CodiceUtenteBeneficiario>000TO</CodiceUtenteBeneficiario>' .
//                        '<CodiceEnteBeneficiario>06954</CodiceEnteBeneficiario>' .
//                        '<TipoUfficioBeneficiario></TipoUfficioBeneficiario>' .
//                        '<CodiceUfficioBeneficiario></CodiceUfficioBeneficiario>' .
//                        '</EnteDestinatario>' .
//                        '</EntiDestinatari>' .
//                        '</AccountingData>' .
                '</PaymentRequest>';

        return $BD;
    }

    public function getBufferInvio($data) {
        return $this->creaBuffer($data);
    }

    protected function creaBuffer($bufferDati) {
        $buffer = "";

        $sTagOrario = date('YmdHi');
        $hash = md5($this->getEncryptIV() . $bufferDati . $this->getEncryptKey() . $sTagOrario);
        $bufferDatiCrypt = base64_encode($bufferDati);
        $buffer = "<Buffer>" .
                "<TagOrario>" . $sTagOrario . "</TagOrario>" .
                "<CodicePortale>" . $this->getCodicePortale() . "</CodicePortale>" .
                "<BufferDati>" . $bufferDatiCrypt . "</BufferDati>" .
                "<Hash>" . $hash . "</Hash>" .
                "</Buffer>";
        return $buffer;
    }

    public function getRID($request) {
        $bufferDati = $this->prepareBufferDatiRequest($request);
        file_put_contents("/tmp/bufferDati.log", $bufferDati);
        $buffer = $this->getBufferInvio($bufferDati);
        file_put_contents("/tmp/buffer.log", $buffer);

        return $this->inviaRequestS2S($buffer, 'RID');
    }

    protected function inviaRequestS2S($buffer, $modo = 'RID') {
        $headers = array(
            'User-Agent' => $_SERVER['HTTP_USER_AGENT']
        );
        $parametri = array(
            'buffer' => $buffer
        );
        $itaRestClient = new itaRestClient();
        $itaRestClient->setTimeout(20);
        $itaRestClient->setDebugLevel($this->debug);
        $itaRestClient->setCurlopt_useragent($_SERVER['HTTP_USER_AGENT']);
        $url = "";
        switch ($modo) {
            case 'RID':
                $url = $this->getUrlRID();
                break;
            case 'PID':
                $url = $this->getUrlPID();
                break;
            default:
                $this->setErrMessage("Tipo di URL non definito");
                return false;
                break;
        }
        if (!$itaRestClient->get($url, $parametri, $headers)) {
            $this->errMessage = $itaRestClient->getErrMessage();
            return false;
        }
        return $itaRestClient->getResult();
    }

    public function getPaymentData($request) {
        $bufferDati = $request;
        $buffer = $this->getBufferInvio($bufferDati);

        return $this->inviaRequestS2S($buffer, 'PID');
    }

}
