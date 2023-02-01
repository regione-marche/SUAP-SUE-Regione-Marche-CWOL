<?php

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class EqAuditHandler extends AbstractProcessingHandler {

    private $model;
    private $eqAudit;

    public function __construct($model, $level = Logger::DEBUG, $bubble = true) {
        $this->model = $model;
        $this->eqAudit = new eqAudit();
        parent::__construct($level, $bubble);
    }

    protected function write(array $record) {
        switch ($record['level']) {
            case Monolog\Logger::EMERGENCY:
            case Monolog\Logger::ALERT:
            case Monolog\Logger::CRITICAL:
            case Monolog\Logger::ERROR:
                $operazione = eqAudit::OP_GENERIC_ERROR;
                break;

            case Monolog\Logger::WARNING:
                $operazione = eqAudit::OP_GENERIC_WARNING;
                break;

            default:
                $operazione = eqAudit::OP_MISC_AUDIT;
                break;
        }

        $this->eqAudit->logEqEvent($this->model, array(
            'Operazione' => $operazione,
            'Estremi' => $record['message']
        ));
    }

}
