<?php

use Monolog\Handler\AbstractProcessingHandler;

class ExceptionHandler extends AbstractProcessingHandler {

    protected function write(array $record) {
        throw new Exception("{$record['channel']}.{$record['level_name']} - {$record['message']}");
    }

}
