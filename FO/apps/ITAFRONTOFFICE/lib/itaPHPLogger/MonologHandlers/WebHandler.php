<?php

use Monolog\Handler\AbstractProcessingHandler;

class WebHandler extends AbstractProcessingHandler {

    protected function write(array $record) {
        if (class_exists('Out')) {
            Out::msgStop($record['channel'] . '.' . $record['level_name'], "<br /><b>{$record['message']}</b><br /><i>{$record['context']['file']}:{$record['context']['line']}</i>");

            if ($record['level'] >= itaPHPLogger::CRITICAL) {
                /*
                 * Se l'errore è bloccante, stampo l'XML
                 * ed esco. Imposto codice 200 perché altrimenti
                 * il JS non interpreta il responso.
                 */
                ob_clean();
                header('HTTP/1.1 200 OK');
                echo Out::get('xml');
                exit;
            }
        } else {
            echo $record['formatted'];
        }
    }

}
