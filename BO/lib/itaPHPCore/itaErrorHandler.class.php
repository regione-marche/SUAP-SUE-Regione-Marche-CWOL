<?php

class itaErrorHandler {

    private static $logger;

    public static function register() {
        require_once ITA_LIB_PATH . '/itaPHPCore/Config.class.php';

        Config::loadConfig();

        if (!($error_level = (int) Config::getConf('log.error_handler'))) {
            return;
        }

        require_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogger.php';

        $error_levels = array(
            1 => itaPHPLogger::ERROR,
            2 => itaPHPLogger::WARNING,
            3 => itaPHPLogger::NOTICE
        );

        self::$logger = new itaPHPLogger('itaEngine');

        switch (Config::getConf('log.error_handler_output')) {
            case 1:
                self::$logger->pushWeb($error_levels[$error_level]);
                break;

            case 2:
                self::$logger->pushSysLog($error_levels[$error_level]);
                break;

            case 3:
                self::$logger->pushFile(Config::getConf('log.error_handler_file'), $error_levels[$error_level]);
                break;
        }
    }

}
