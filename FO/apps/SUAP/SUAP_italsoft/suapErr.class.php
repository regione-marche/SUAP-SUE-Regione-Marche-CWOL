<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praErr
 *
 * @author Andrea
 */
require_once(ITA_LIB_PATH . '/itaPHPCore/itaMailer.class.php');

class suapErr extends frontOfficeErr {

    public $praLib;
    public $PRAM_DB;

    function __construct() {
        $this->praLib = new praLib();
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', ITA_DB_SUFFIX);
    }

    public function inviaMailErrore() {
        $this->createMailBody();

        $Anapar_tab_mail = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANAPAR", true);

        if ($Anapar_tab_mail) {
            foreach ($Anapar_tab_mail as $key => $Anapar_rec) {
                if ($Anapar_rec['PARKEY'] == 'ITA_MAIL_ACCOUNT')
                    $from = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_NAME_ACCOUNT')
                    $name = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_HOST')
                    $host = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_PORT')
                    $port = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_SECURE')
                    $smtpSecure = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_USER')
                    $username = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_PASSWORD')
                    $password = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_MAIL_ERROR')
                    $mailError = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_MAIL_ERROR_ACCOUNT')
                    $from_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_NAME_ERROR_ACCOUNT')
                    $name_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_ERROR_HOST')
                    $host_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_PORT_ERROR')
                    $port_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_ERROR_SECURE')
                    $smtpSecure_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_ERROR_USER')
                    $username_err = $Anapar_rec['PARVAL'];
                if ($Anapar_rec['PARKEY'] == 'ITA_SMTP_ERROR_PASSWORD')
                    $password_err = $Anapar_rec['PARVAL'];
            }

            if ($from_err == "") {
                $from_err = $from;
                $name_err = $name;
                $host_err = $host;
                $port_err = $port;
                $smtpSecure_err = $smtpSecure;
                $username_err = $username;
                $password_err = $password;
                $mailError_err = $mailError;
            }
        } else {
            $from_err = ITA_MAIL_ACCOUNT;
            $name_err = ITA_NAME_ACCOUNT;
            $host_err = ITA_SMTP_HOST;
            $port_err = defined('ITA_PORT') ? ITA_PORT : null;
            $smtpSecure_err = defined('ITA_SMTP_SECURE') ? ITA_SMTP_SECURE : null;
            $username_err = ITA_SMTP_USER;
            $password_err = ITA_SMTP_PASSWORD;
        }

        $itaMailer = new itaMailer(true);
        $itaMailer->Subject = "Segnalazione errore cms: $this->siteName";
        $itaMailer->IsHTML();
        $itaMailer->Body = $this->mailBody;

        $itaMailer->AddAddress($mailError);

        try {
            $itaMailer->Send(array(
                'FROM' => $from_err,
                'NAME' => $name_err,
                'HOST' => $host_err,
                'PORT' => $port_err,
                'SMTPSECURE' => $smtpSecure_err,
                'USERNAME' => $username_err,
                'PASSWORD' => $password_err
            ));
        } catch (phpmailerException $e) {
            $this->setMessaggio('Errore invio mail: ' . $e->getMessage());
            $this->logErrore();
        }
    }

    public function logErrore() {
        $logDirectory = $this->praLib->getCartellaLog();
        $errorLog = fopen($logDirectory . 'errorLog.txt', 'a+');
        fwrite($errorLog, $this->logString . chr(13) . chr(10));
        fclose($errorLog);
    }

}
