<?php

/**
 * PHP Version 5
 *
 * @category   CORE
 * @package    /lib/itaPHPCore
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @author  Lorenzo Pergolini  <l.pergolini#apa.it>
 * @license 
 * @version    19.05.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
class SmartAgent {

    private $host = 'localhost';
    private $port = '8088';
    private $enabled;

    public function __construct() {
        $this->port = (App::getConf('SmartAgent.port')) ? App::getConf('SmartAgent.port') : $this->port;
        $this->enabled = (App::getConf('SmartAgent.enabled')) ? App::getConf('SmartAgent.enabled') : false;
    }

    function isEnabled() {
        return $this->enabled;
    }

    function getHost() {
        return $this->host;
    }

    function getPort() {
        return $this->port;
    }

    function setHost($host) {
        $this->host = $host;
    }

    function setPort($port) {
        $this->port = $port;
    }

    /**
     * WIP
     * 
     * @param type $returnForm 
     * @param type $returnId
     * @param type $returnEvent
     */
    public function handshake($returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.handshake('$this->host:$this->port', '$returnForm',  '$returnId', '$returnEvent')");
    }

    /**
     * Effettua la firma usando Pknet
     * @param type $sourceDocument documento da firmare base64
     * @param type $params  array dei parameti da passare per la firma
     * @param type $returnForm
     * @param type $returnId
     * @param type $returnEvent
     * 
     */
    public function smartCardSign($sourceDocument, $params, $returnData) {
        if (!empty($params)) {
            $signMode = $params['signMode'];
            $encoding = $params['encoding'];
            $filterValidCred = $params['filterValidCred'];
            $multiple = $params['multiple'];
        }
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.smartCardSign('$this->host:$this->port','$sourceDocument','$signMode','$encoding', '$filterValidCred','$multiple','$returnForm', '$returnId', '$returnEvent')");
    }

    /**
     * Effettua la verifica della firma apposta sul documento  
     * @param type $signedDocument documento firmato in base64
     * @param type $sourceDocument documento originale in base64

     * @param type $returnForm
     * @param type $returnId
     * @param type $returnEvent
     */
    public function smartCardVerify($signedDocument, $sourceDocument, $returnData) {
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.smartCardVerify('$this->host:$this->port','$signedDocument','$sourceDocument','$returnForm',  '$returnId', '$returnEvent')");
    }

    /**
     * Effettua la verifica della firma apposta sul documento  
     * @param type $signedDocument documento firmato in base64
     * @param type $sourceDocument documento originale in base64

     * @param type $returnForm
     * @param type $returnId
     * @param type $returnEvent
     */
    public function smartCardSignersInfo($signedDocument, $sourceDocument, $returnData) {
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.smartCardSignersInfo('$this->host:$this->port','$signedDocument','$sourceDocument','$returnForm',  '$returnId', '$returnEvent')");
    }

    public function wiaScan($params, $returnData) {
        if (!empty($params)) {
            $forcePdf = $params['forcePdf'];
            $color = $params['color'];
            $quality = $params['quality'];
            $forceClose = $params['forceClose'];
        }
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.wiaScan('$this->host:$this->port', '$forcePdf','$color', '$quality','$forceClose','$returnForm', '$returnId', '$returnEvent')");
    }

    public function twainScan($params, $returnData) {
        if (!empty($params)) {
            $forcePdf = $params['forcePdf'];
            $color = $params['color'];
            $quality = $params['quality'];
            $forceClose = $params['forceClose'];
        }
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.twainScan('$this->host:$this->port', '$forcePdf','$color', '$quality','$forceClose','$returnForm','$returnId', '$returnEvent')");
    }

    public function isisScan($params, $returnData) {
        if (!empty($params)) {
            $forcePdf = $params['forcePdf'];
            $quality = $params['quality'];
            $color = $params['color'];
            $show_ui = $params['$show_ui'];
        }
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.isisScan('$this->host:$this->port', '$forcePdf','$show_ui','$color','$quality','$returnForm', '$returnId', '$returnEvent')");
    }

    public function namirialSignature($source, $params, $returnData) {
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        $device = $params['device'];
        $certificate = $params['certificate'];
        $biometricData = $params['biometricData'];
        $noPdfSignInfo = $params['noPdfSignInfo'];
        $makePdfOriginal = $params['makePdfOriginal'];
        $saveInSameFolder = $params['saveInSameFolder'];
        $forceOverwrite = $params['forceOverwrite'];
        Out::codice("Smartagent.namirialSignature('$this->host:$this->port', '$device', '$certificate','$source','$biometricData','$noPdfSignInfo','$makePdfOriginal','$saveInSameFolder','$forceOverwrite','$returnForm',  '$returnId', '$returnEvent')");
    }

    public function namirialVerifySignature($source, $returnData) {
        $returnForm = $returnData['returnForm'];
        $returnId = $returnData['returnId'];
        $returnEvent = $returnData['returnEvent'];
        Out::codice("Smartagent.namirialVerifySignature('$this->host:$this->port','$source','$returnForm',  '$returnId', '$returnEvent')");
    }

    /**
     * Eseguen un comando shell DOS
     * 
     * @param string $cmd
     * @param string $args
     * @param string $procName
     * @param strign $hidden apertura del pgm esterno 
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    public function shellExec($cmd, $args, $hidden, $procName, $returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.shellExec('$this->host:$this->port', '$cmd', '$args','$hidden','$procName', '$returnForm','$returnId', '$returnEvent')");
//        Out::shellExec("$this->host:$this->port", $cmd, $args, $procName, $returnForm, $returnId, $returnEvent);
    }

    /**
     * Apre una Remote App (Terminal Server)
     * 
     * @param string $cmd
     * @param string $args
     * @param string $procName
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     * 
     */
    public function remoteAppExec($cmd, $args, $procName, $returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.remoteAppExec('$this->host:$this->port', '$cmd', '$args', '$procName', '$returnForm', '$returnId', '$returnEvent')");
    }

    /**
     * Restituisce nome postazione
     * 
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    public function getMachineName($returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.getMachineName('$this->host:$this->port', '$returnForm','$returnId', '$returnEvent')");
    }

    /**
     * Imposta nome postazione
     * 
     * @param string $machineName Nome da impostare
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    public function setMachineName($machineName, $returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.setMachineName('$this->host:$this->port', '$machineName', '$returnForm', '$returnId', '$returnEvent')");
    }

    /**
     * Imposta nome postazione
     * 
     * @param string $fileName Nome file da scaricare
     * @param string $returnForm
      @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    public function downloadFile($fileName, $url, $returnForm, $returnId, $returnEvent) {
        Out::codice("Smartagent.downloadFile('$this->host:$this->port', '$fileName', '$url', '$returnForm', '$returnId', '$returnEvent')");
    }

}

?>
