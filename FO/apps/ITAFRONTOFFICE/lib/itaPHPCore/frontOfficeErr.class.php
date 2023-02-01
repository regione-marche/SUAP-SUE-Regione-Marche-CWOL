<?php

/**
 *
 * Controlle e gestione errori
 *
 *  * PHP Version 5
 *
 * @category   
 * @package    
 * @author     Andrea Boriani <andreaboriani1989@gmail.com>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    03.04.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
class frontOfficeErr {

    public $logDirectory;
    public $mailError;
    //
    // Amministratore Sito
    //
    public $from_err;
    public $name_err;
    //
    // Parametri SMTP ( wp=SMTP plug-in, si=tabella CB_CONFIG)
    //
    public $SMTP_host;
    public $SMTP_port;
    public $SMTP_username;
    public $SMTP_password;
    public $SMTP_secure;
    public $file;
    public $classe;
    public $codice;
    public $messaggio;
    public $htmlCms;
    public $bodyMail;
    public $user;
    public $siteName;
    public $logString;
    public $mailBody;

    function __construct() {
        //
        // PARAMETRI PER INVIO SMTP
        //
        $SMTPInfo = frontOfficeApp::$cmsHost->getSMTPInfo();
        $this->setFrom_err($SMTPInfo['from_err']);
        $this->setName_Err($SMTPInfo['name_err']);
        $this->setSMTPHost($SMTPInfo['SMTP_host']);
        $this->setSMTPPort($SMTPInfo['SMTP_port']);
        $this->setSMTPUsername($SMTPInfo['SMTP_username']);
        $this->setSMTPPassword($SMTPInfo['SMTP_password']);
        if ($SMTPInfo['SMTP_secure'] != '') {
            $this->setSMTPSecure($SMTPInfo['SMTP_secure']);
        }

        //
        // PATH FILE DI LOG
        //
        $this->setLogDirectory(ITA_FRONTOFFICE_LOG);

        //
        // DESTINATARIO MAIL ERRORE
        //
        $this->setMailError(frontOfficeApp::$cmsHost->getSiteAdminMailAddress());
    }

    function setUser() {
        $this->user = frontOfficeApp::$cmsHost->getUserName();
    }

    function setSiteName() {
        $this->siteName = frontOfficeApp::$cmsHost->getSiteName();
    }

    function setMailError($mailError) {
        $this->mailError = $mailError;
    }

    function getMailError() {
        return $this->mailError;
    }

    function setFrom_err($from_err) {
        $this->from_err = $from_err;
    }

    function getFrom_err() {
        return $this->from_err;
    }

    function setName_Err($name_err) {
        $this->name_err = $name_err;
    }

    function getName_Err() {
        return $this->name_err;
    }

    function setSMTPHost($host_err) {
        $this->host_err = $host_err;
    }

    function getSMTPHost() {
        return $this->host_err;
    }

    function setSMTPPort($port_err) {
        $this->port_err = $port_err;
    }

    function getSMTPPort() {
        return $this->port_err;
    }

    function setSMTPUsername($username_err) {
        $this->username_err = $username_err;
    }

    function getSMTPUsername() {
        return $this->username_err;
    }

    function setSMTPPassword($password_err) {
        $this->password_err = $password_err;
    }

    function getSMTPPassword() {
        return $this->password_err;
    }

    function setSMTPSecure($SMTP_secure) {
        $this->SMTP_secure = $SMTP_secure;
    }

    function getSMTPSecure() {
        return $this->SMTP_secure;
    }

    function setLogDirectory($logDirectory) {
        $this->logDirectory = $logDirectory;
    }

    function getLogDirectory() {
        return $this->logDirectory;
    }

    function setFile($file) {
        $this->file = $file;
    }

    function getFile() {
        return $this->file;
    }

    function setClasse($classe) {
        $this->classe = $classe;
    }

    function getClasse() {
        return $this->classe;
    }

    function setCodice($codice) {
        $this->codice = $codice;
    }

    function getCodice() {
        return $this->codice;
    }

    function setMessaggio($messaggio) {
        $this->messaggio = $messaggio;
    }

    function getMessaggio() {
        return $this->messaggio;
    }

    function getHtmlCms() {
        return $this->htmlCms;
    }

    /**
     * Elaborazione di un errore, salvataggio e invio
     * @param type $file
     * @param type $codice
     * @param type $messaggio
     * @param type $classe
     * @param type $messaggioCms
     * @return type
     */
    public function parseError($file, $codice, $messaggio, $classe = '', $messaggioCms = 'Servizio interrotto...', $visualizza = true) {
        $this->setUser();
        $this->setSiteName();
        $this->setFile($file);
        $this->setMessaggio($messaggio);
        $this->setCodice($codice);
        $this->setClasse($classe);
        $this->createLogString();
        if ($messaggioCms !== false && $visualizza) {
            $this->creaHtmlCms($messaggioCms);
        }
        $this->logErrore();
        $this->inviaMailErrore();
        return $this->getHtmlCms();
    }

    public function creaHtmlCms($errMessage) {
        $html = new html();

        $content = '<div class="italsoft-alert italsoft-alert--danger">
                    <h2>' . $errMessage . '</h2>
                    </div>';

        $vars = array(
            'URL_INCLUDE' => ItaUrlUtil::UrlInc(),
            'ERR_MESSAGE' => $errMessage
        );

//        $err_path = ITA_FRONTOFFICE_ROOT . '/itaFrontOffice_italsoft/resources/error.html';
//
//        if (file_exists($err_path)) {
//            $content = file_get_contents($err_path);
//            foreach ($vars as $placeholder => $value) {
//                $content = preg_replace('/@{\$' . $placeholder . '}@/', $value, $content);
//            }
//        }

        $html->appendHtml($content);

        $this->htmlCms = $html->getHtml();
    }

    public function inviaMailErrore() {
        if (!$this->mailError) {
            return;
        }

        $this->createMailBody();
        require_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');
        /*
         * PATCH Controllo eccezioni phpMailer
         * 
         */
        $itaMailer = new itaMailer(true);
        $itaMailer->Subject = "Segnalazione errore cms: $this->siteName";
        $itaMailer->IsHTML();
        $itaMailer->Body = $this->mailBody;

        $itaMailer->AddAddress($this->mailError);

        try {
            $itaMailer->Send(array(
                'FROM' => $this->from_err,
                'NAME' => $this->name_err,
                'HOST' => $this->SMTP_host,
                'PORT' => $this->SMTP_port,
                'SMTPSECURE' => $this->SMTP_secure,
                'USERNAME' => $this->SMTP_username,
                'PASSWORD' => $this->SMTP_password
            ));
        } catch (phpmailerException $e) {
            $this->setMessaggio('Errore invio mail: ' . $e->getMessage());
            $this->logErrore();
        }
        /*
         * Fine patch
         * 
         */
    }

    public function createMailBody() {
        $this->mailBody = "CMS        : $this->siteName<br>
                            DB         : " . ITA_DB_SUFFIX . "<br>
                            Utente     : $this->user<br>
                            Password   : " . frontOfficeApp::$cmsHost->getPassword() . "<br>
                            In data " . date('d/m/Y') . " alle ore " . date("H:i:s") . " si è verificato il seguente errore:<br>
                            Codice err.: " . $this->codice . "<br>
                            Messaggio  : <pre>" . $this->messaggio . "</pre>
                            File       : " . $this->file . "<br>
                            Classe     : " . $this->classe . "<br>
                            URL        : <pre>" . $_SERVER['REQUEST_URI'] . "</pre>";
    }

    public function createLogString() {
        $this->logString = "[$this->siteName][DB:" . ITA_DB_SUFFIX . "][$this->user][" . date('d/m/Y') . "][" . date("H:i:s") . "][$this->file][$this->classe][$this->codice][$this->messaggio]";
    }

    public function logErrore() {
        openlog("itaFrontOfficeLog", LOG_PID, LOG_USER);
        syslog(LOG_ERR, $this->logString);
        closelog();
    }

}
