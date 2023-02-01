<?php

use Monolog\Handler\AbstractProcessingHandler;

class EmailHandler extends AbstractProcessingHandler {

    private $toAddress;

    public function __construct($level = Logger::DEBUG, $bubble = true, $toAddress = '') {
        $this->toAddress = $toAddress;
        parent::__construct($level, $bubble);
    }

    protected function write(array $record) {
        require_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';

        $devLib = new devLib();

        /*
         * Account Mittente
         */
        $ItaEngine_mail_rec_account = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        if (!$ItaEngine_mail_rec_account) {
            return false;
        }

        $Account = $ItaEngine_mail_rec_account['CONFIG'];

        /*
         * Account destinatario
         */
        if (!$this->toAddress) {
            $ItaEngine_mail_rec_address = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ADDRESS', false);
            if (!$ItaEngine_mail_rec_address) {
                return false;
            }

            $Address = $ItaEngine_mail_rec_address['CONFIG'];
        } else {
            $Address = $this->toAddress;
        }

        require_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($Account);
        if (!$emlMailBox) {
            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            return;
        }

        $outgoingMessage->setSubject("Segnalazione itaEngine - {$record['channel']}.{$record['level_name']}");
        $outgoingMessage->setBody($record['formatted']);
        $outgoingMessage->setEmail($Address);
        $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);

        if ($mailSent) {
            return true;
        } else {
            return false;
        }
    }

}
