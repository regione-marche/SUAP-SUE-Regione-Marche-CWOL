<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    22.01.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once(ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php');
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';

class emlWsManager {

    private $errCode;
    private $errMessage;
    private $tmpPath;
    private $envelopedId;
    public $ITALWEB;
    public $emlLib;

    function __construct() {
        try {
            $this->emlLib = new emlLib();
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    /**
     * 
     * Questo metodo mappa la risorsa REST sendMail
     * 
     * @param array $params
     * @return array()
     */
    public function sendMail($params) {

        if (!$this->createEnvelope($params['MESSAGEDATA'])) {
            $this->destroyEnvelope();
            return false;
        }

        $resultSendEnvelope = $this->sendEnvelope();
        if (!$resultSendEnvelope) {
            $this->destroyEnvelope();
            return false;
        }

        return $resultSendEnvelope;
    }

    public function checkSendMailPreconditions($params) {
        /*
         * Controllo pre-condizioni per elaborazione chiamata
         */
        if (!$params['MESSAGEDATA']) {
            $this->setErrMessage('Parametro MESSAGEDATA non presente');
            $this->errCode = -1;
            return false;
        }
        $xmlObj = new itaXML();
        $result = $xmlObj->setXmlFromString($params['MESSAGEDATA']);
        if (!$result) {
            $this->setErrMessage('Stringa XML del parametro MESSAGEDATA non valida');
            $this->errCode = -1;
            return false;
        }
        return true;
    }

    public function checkParamEnvelopeId($params) {
        if (!$params['ENVELOPEID']) {
            $this->setErrMessage('Parametro ENVELOPEID non presente');
            $this->errCode = -1;
            return false;
        }
    }

    /**
     * 
     * Test della cartella temporanea di lavore per l'evelope della mail
     *
     * @return boolean
     */
    private function testWorkDirectory($params) {
        $pathDest = itaLib::getAppsTempPath('work-mailer-evelope-' . $params['ENVELOPEID']);
        if (!is_dir($pathDest)) {
            $this->setErrMessage('Directory temporanea dell\'envelopeId non valida');
            $this->errCode = -1;
            return false;
        }
        return true;
    }

    public function createMailEnvelope($messageData) {
        if ($this->checkSendMailPreconditions($messageData) === false) {
            return false;
        }

        if (!$this->createEnvelope($messageData)) {
            $this->destroyEnvelope();
            return false;
        }

        return $this->envelopedId;
    }

    public function addBodyToEnvelope($messageData) {
        if ($this->checkSendMailPreconditions($messageData) === false) {
            return false;
        }
        if ($this->checkParamEnvelopeId($messageData) === false) {
            return false;
        }
        if ($this->testWorkDirectory($messageData) === false) {
            return false;
        }
        // leggere xml dal repositori e restituire array
        // aggiungere all'array il nuovo pezzo dell'xml
    }

    public function checkSendMailFromLocalDataPreconditions($params) {
        /*
         * Controllo pre-condizioni per elaborazione chiamata
         */
        if (!$params['IDFILE']) {
            $this->setErrMessage('Parametro IDFILE non presente');
            $this->errCode = -1;
            return false;
        }
        if (!$params['HASHFILE']) {
            $this->setErrMessage('Parametro HASHFILE non presente');
            $this->errCode = -1;
            return false;
        }

        return true;
    }

    public function sendMailFromLocalData($params) {
        if ($this->checkSendMailFromLocalDataPreconditions($params) === false) {
            return false;
        }
        $devlib = new devLib();
        $configLocalRepository = $devlib->getEnv_config('WEBMAILREST', 'codice', 'LOCALREPOSITORY', false);
        if (!$configLocalRepository || $configLocalRepository['CONFIG'] == '') {
            $this->setErrMessage('Manca configurazione del repository locale nella gestione parametri');
            $this->errCode = -1;
            return false;
        }

        $params['MESSAGEDATA'] = file_get_contents($configLocalRepository['CONFIG'] . '/' . $params['IDFILE'] . '.xml');
        if ($params['MESSAGEDATA'] == '') {
            $this->setErrMessage('L\'XML del repository locale è vuoto.');
            $this->errCode = -1;
            return false;
        }

        $hashFile = hash('sha256', $params['MESSAGEDATA']);
        if ($hashFile !== $params['HASHFILE']) {
            $this->setErrMessage('Valore del parametro HASHFILE non corrisponde con l\'hash di ' . $params['IDFILE'] . $hashFile);
            $this->errCode = -1;
            return false;
        }

        $retPrecodition = $this->checkSendMailPreconditions($params);
        if (!$retPrecodition) {
            $this->errCode = -1;
            return false;
        }

        return $this->sendMail($params);
    }

    public function sendMailFromEnvelope($evelopeId) {

        //TODo CHECK E STE ENVELOPE

        $resultSendEnvelope = $this->sendEnvelope();
        if (!$resultSendEnvelope) {
            $this->destroyEnvelope();
            return false;
        }

        return $resultSendEnvelope;
    }

    /*
     * Inizio Metodi privati
     */

    /**
     * 
     * Creazione id evelope
     * 
     * @return type
     */
    private function getEnvelopeId() {
        return hash('sha256', App::$utente->getKey('TOKEN') . uniqid());
    }

    /**
     * 
     * Creazione della cartella temporanea di lavore per l'evelope della mail
     *
     * @return boolean
     */
    private function createWorkDirectory() {
        $pathDest = itaLib::createAppsTempPath('work-mailer-evelope-' . $this->envelopedId);
        if (!$pathDest) {
            return false;
        }
        $this->tmpPath = $pathDest;
        return true;
    }

    /**
     * 
     * Trasmissione SMTP dei dati mail
     * 
     * @param type $paramSendMail
     * @return type
     */
    private function sendSMTPMessage($paramSendMail) {
        $emlMailBox = emlMailBox::getInstance($paramSendMail['fromAddress']);
        if (!$emlMailBox) {
            $toReturn = array(
                'status' => false,
                'return' => "Invio mail NON riuscito",
                'message' => 'Impossibile accedere alle funzioni dell\'account ' . $paramSendMail['fromAddress'],
                'idmail' => ''
            );
        } else {
            $outgoingMessage = array();
            $outgoingMessage['FromName'] = $emlMailBox->getAccount_name();
            $outgoingMessage['Subject'] = $paramSendMail['subject'];
            $outgoingMessage['From'] = $paramSendMail['fromAddress'];
            $outgoingMessage['Body'] = $paramSendMail['body'];
            $outgoingMessage['Email'] = $paramSendMail['toAddresses'];
            $outgoingMessage['CCAddresses'] = $paramSendMail['ccAdresses'];
            $outgoingMessage['BCCAddresses'] = $paramSendMail['ccAdresses'];
            $outgoingMessage['Attachments'] = $paramSendMail['attachments'];
            $mailArchivio_rec = $emlMailBox->sendMessage($outgoingMessage);

            if ($mailArchivio_rec) {
                $toReturn = array(
                    'status' => true,
                    'message' => "Invio mail riuscito",
                    'idmail' => $mailArchivio_rec['IDMAIL'],
                    'rowid' => $mailArchivio_rec['ROWID'],
                );
            } else {
                $toReturn = array(
                    'status' => false,
                    'message' => $emlMailBox->getLastMessage(),
                    'idmail' => '',
                    'rowid' => '',
                );
            }
        }
        return $toReturn;
    }

    /**
     * 
     * Crea l'ambiente di lavoro per l'invio della mail (detto anche busta)
     * 
     * @param array $messageData
     * @return boolean
     */
    private function createEnvelope($messageData = '') {
        $this->envelopedId = $this->getEnvelopeId();
        if (!$this->envelopedId) {
            $this->setErrMessage('errore nella richiesta dell\'envelopeId');
            $this->errCode = -1;
            return false;
        }

        $retCreateWorkDirectory = $this->createWorkDirectory($this->envelopedId);
        if (!$retCreateWorkDirectory) {
            $this->setErrMessage('errore nella creazione della cartella temporanea');
            $this->errCode = -1;
            return false;
        }

        if ($messageData) {
            if (file_put_contents($this->tmpPath . "/" . "MESSAGE_DATA.xml", $messageData) === false) {
                $this->errCode = -1;
                $this->setErrMessage('Errore di salvataggio dei dati messaggio nella busta di lavoro');
                return false;
            }
        }

        return true;
    }

    /**
     * 
     * Distrugge l'ambiente di lavoro per l'invio della mail (detto anche busta)
     * 
     */
    private function destroyEnvelope() {
        itaLib::deleteAppsTempPath('work-mailer-evelope-' . $this->envelopedId);
    }

    /**
     * 
     * Helper per la conversione del file xml MESSAGEDATA.xml della busta corrente
     * 
     * @return boolean|string
     */
    private function messageData2Array() {

        $messageDataArray = array();
        $xmlObj = new itaXML();
        $result = $xmlObj->setXmlFromFile("{$this->tmpPath}/MESSAGE_DATA.xml");
        if (!$result) {
            $this->setErrMessage($this->tmpPath . ' MESSAGE_DATA.xml non valido');
            $this->errCode = -1;
            return false;
        }

        $arrayXml = $xmlObj->getArray();
        $messageDataArray['fromAddress'] = $arrayXml['messageData'][0]['fromAddress'][0]['@textNode'];
        if (!$messageDataArray['fromAddress'] || $messageDataArray['fromAddress'] == '') {
            $this->setErrMessage('mittente non indicato');
            $this->errCode = -1;
            return false;
        }

        $messageDataArray['toAddresses'] = array();
        foreach ($arrayXml['messageData'][0]['toAddresses'][0]['toAddress'] as $toAddress) {
            $messageDataArray['toAddresses'][] = $toAddress['@textNode'];
        }
        if (!$messageDataArray['toAddresses']) {
            $this->setErrMessage('destinatario non indicato');
            $this->errCode = -1;
            return false;
        }

        foreach ($arrayXml['messageData'][0]['ccAddresses'][0]['ccAddress'] as $ccAddress) {
            if (trim($ccAddress) && $ccAddress) {
                $messageDataArray['ccdestinatari'] .= $ccAddress['@textNode'] . '; ';
            }
        }

        foreach ($arrayXml['messageData'][0]['bccAddresses'][0]['bccAddress'] as $bccAddress) {
            if (trim($bccAddress) && $bccAddress) {
                $messageDataArray['bccdestinatari'] .= $bccAddress['@textNode'] . '; ';
            }
        }

        $messageDataArray['subject'] = $arrayXml['messageData'][0]['subject'][0]['@textNode'];
        $messageDataArray['body'] = $arrayXml['messageData'][0]['body'][0]['@textNode'];

        $indice = 0;
        foreach ($arrayXml['messageData'][0]['attachments'][0]['attachment'] as $attachment) {
            if ($attachment['fileName'] != '' && $attachment['fileStream'] == '' && $attachment['filePath'] == '') {
                $this->setErrMessage('errore nei parametri degli allegati');
                $this->errCode = -1;
                return false;
            }
            $messageDataArray['allegati'][$indice]['fileName'] = $attachment['fileName'][0]['@textNode'];
            $messageDataArray['allegati'][$indice]['fileStream'] = $attachment['fileStream'][0]['@textNode'];
            $messageDataArray['allegati'][$indice]['filePath'] = $attachment['filePath'][0]['@textNode'];
            $indice ++;
        }

        $indice = 0;
        foreach ($arrayXml['messageData'][0]['extraParameters'][0]['parameter'] as $parameter) {
            $messageDataArray['extraParam'][$indice]['key'] = $parameter['key'][0]['@textNode'];
            $messageDataArray['extraParam'][$indice]['value'] = $parameter['value'][0]['@textNode'];
            $indice ++;
        }

        $messageDataArray['evelopeAllegati'] = array();
        foreach ($messageDataArray['allegati'] as $allegato) {
            $destFile = $this->tmpPath . "/" . $allegato['fileName'];
            if ($allegato['fileStream']) {
                if (!file_put_contents($destFile, base64_decode($allegato['fileStream']))) {
                    $this->setErrMessage('errore nella registrazione dell\'allegato ' . $allegato['fileName'] . ' in: ' . $destFile);
                    $this->errCode = -1;
                    return false;
                }
            } else {
                if ($allegato['filePath']) {
                    if (!copy($allegato['filePath'] . $allegato['fileName'], $destFile)) {
                        $this->setErrMessage('errore nella copia dell\'allegato ' . $allegato['fileName'] . ' da: ' . $allegato['filePath'] . $allegato['fileName'] . ' a: ' . $destFile);
                        $this->errCode = -1;
                        return false;
                    }
                }
            }
            $messageDataArray['evelopeAllegati'][] = array('FILEPATH' => $this->tmpPath . '/' . $allegato['fileName'], 'FILENAME' => $allegato['fileName'], 'FILEORIG' => $allegato['fileName']);
        }
        return $messageDataArray;
    }

    /**
     * 
     * Invio fisicamente la mail dal array dati normalizzato per la busta corrente
     * 
     * @param type $messageDataArray
     * @return type
     */
    private function sendFromMessageDataArray($messageDataArray) {
        $spedizioni = array();
        foreach ($messageDataArray['toAddresses'] as $key => $destinatario) {
            $paramSendMail = array(
                'fromAddress' => $messageDataArray['fromAddress'],
                'toAddresses' => $destinatario,
                'ccAdresses' => $$messageDataArray['ccdestinatari'],
                'bccAdresses' => $$messageDataArray['bccdestinatari'],
                'subject' => utf8_decode($messageDataArray['subject']),
                'body' => utf8_decode($messageDataArray['body']),
                'attachments' => $messageDataArray['evelopeAllegati'],
                'extraParameters' => $messageDataArray['extraParam']
            );
            $resultInvioMail = $this->sendSMTPMessage($paramSendMail);

            $spedizioni[$key]['status'] = $resultInvioMail['status'];
            $spedizioni[$key]['message'] = $resultInvioMail['message'];
            $spedizioni[$key]['recipient'] = $destinatario;
            $spedizioni[$key]['idmail'] = $resultInvioMail['idmail'];
            $spedizioni[$key]['rowid'] = $resultInvioMail['rowid'];
        }

        return $spedizioni;
    }

    /**
     * incia una mail dall'ambiente/busta di lavoro corrente
     * 
     * @return boolean
     */
    private function sendEnvelope() {

        $messageDataArray = $this->messageData2Array();

        if (!$messageDataArray) {
            $this->destroyEnvelope();
            return false;
        }

        $resultsInvioMail = $this->sendFromMessageDataArray($messageDataArray);
        if (!$resultsInvioMail) {
            $this->destroyEnvelope();
            return false;
        }

        /*
         * cancellazione cartella temporanea degli allegati
         */
        $this->destroyEnvelope();

        $resultGeneral = array();
        $resultGeneral['status'] = true;
        $resultGeneral['sentMessage'] = 'Invio concluso con successo';
        $resultGeneral['toSendCount'] = count($resultsInvioMail);

        $unsent = 0;
        foreach ($resultsInvioMail as $resultInvioMail) {
            if ($resultInvioMail['status'] === false) {
                $unsent ++;
            }
        }
        $resultGeneral['sentCount'] = count($resultsInvioMail) - $unsent;
        $resultGeneral['unsentCount'] = $unsent;
        $resultGeneral['detailedInfo'] = $resultsInvioMail;
        if ($unsent == count($resultsInvioMail)) {
            $resultGeneral['status'] = false;
            $resultGeneral['sentMessage'] = "Fallito l'invio a tutti i $unsent i destinatari.";
        } elseif ($unsent > 0) {
            $resultGeneral['status'] = false;
            $resultGeneral['sentMessage'] = "Fallito l'invio ad $unsent destinatari.";
        }
        return $resultGeneral;
    }

    public function statusMailEnvelope($params) {
        /*
         * Controllo pre-condizioni per elaborazione chiamata
         */
        if (!$params['ENVELOPEID'] && !$params['ROWID']) {
            $this->setErrMessage('Parametro ENVELOPEID/ROWID non presente o non valorizzato');
            $this->errCode = -1;
            return false;
        }
        if ($params['ENVELOPEID']) {
            $codice = $params['ENVELOPEID'];
            $tipo = 'id';
        } else {
            if ($params['ROWID']) {
                $codice = $params['ROWID'];
                $tipo = '';
            }
        }

        $currMailBox = new emlMailBox();
        $mailArchivio = $this->emlLib->getMailArchivio($codice, $tipo);
        if (!$mailArchivio) {
            $this->setErrMessage('Mail non trovata.');
            return false;
        }
        $currMessage = $currMailBox->getMessageFromDb($mailArchivio['ROWID']);
        $currMessage->parseEmlFileDeep();
        $retDecode = $currMessage->getStruct();
        return $this->analizzaMail($retDecode);
    }

    function analizzaMail($retDecode) {
        $elencoAllegati = array();
        $elementi = $retDecode['Attachments'];
        $AnomalieAllegati = false;
        if ($elementi) {
            $incr = 1;
            foreach ($elementi as $elemento) {
                if ($elemento['FileName'] === '') {
                    $AnomalieAllegati = true;
                    $elemento['FileName'] = md5(microtime());
                    usleep(10);
                }
                if ($elemento['FileName']) {
                    $vsign = "";
                    $icon = utiIcons::getExtensionIconClass($elemento['FileName'], 32);
                    $sizefile = $this->emlLib->formatFileSize(filesize($elemento['DataFile']));
                    $ext = pathinfo($elemento['FileName'], PATHINFO_EXTENSION);
                    if (strtolower($ext) == "p7m") {
                        $vsign = "<span class=\"ita-icon ita-icon-shield-blue-24x24\">Verifica Firma</span>";
                    }
                    $elencoAllegati[] = array(
                        'ROWID' => $incr,
                        'FileIcon' => "<span style = \"margin:2px;\" class=\"$icon\"></span>",
                        'DATAFILE' => $elemento['FileName'],
                        'FILE' => $elemento['DataFile'],
                        'FileSize' => $sizefile,
                        'VSIGN' => $vsign
                    );
                    $incr++;
                }
            }
        }
        $toReturn = array();
        $toReturn['Mittente'] = $retDecode['FromAddress'];
        $toReturn['Destinatario'] =  $retDecode['To'][0]['address'];
        $toReturn['Oggetto'] = $retDecode['Subject'];
        $toReturn['Data'] = date("Ymd", strtotime($retDecode['Date']));
        $toReturn['Ora'] = date("H:i:s", strtotime($retDecode['Date']));
        $toReturn['allegati'] = $elencoAllegati;
        return $toReturn;
    }

}
