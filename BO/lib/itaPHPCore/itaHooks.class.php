<?php

/**
 * Hooks Manager
 *
 * @author m.biagioli
 */
class itaHooks {

    private static $HOOKS = array();

    public static function scan() {
        $root = ITA_BASE_PATH . '/hooks';
        if (!file_exists($root . '/hooks.ini')) {
            return true;
        }

        if (!is_readable($root . '/hooks.ini')) {
            return true;
        }
        $hooksIni = parse_ini_file($root . '/hooks.ini', true);
        if (!$hooksIni) {
            return false;
        }
        foreach (scandir($root) as $filename) {
            if (isSet($hooksIni[$filename]) && $hooksIni[$filename]['active'] == 1) {
                if (pathinfo($root . '/' . $filename, PATHINFO_EXTENSION) == 'php') {
                    require_once $root . '/' . $filename;
                }
            }
        }
        return true;
    }

    public static function init() {
        self::$HOOKS = array();
    }

    public static function register($cutpoint, $fn) {
        self::$HOOKS[$cutpoint][] = array(
            'function' => $fn
        );
    }

    public static function execute($cutpoint, $args = array()) {
        if (isset(self::$HOOKS[$cutpoint])) {
            foreach (self::$HOOKS[$cutpoint] as $hook) {
                $hook['function']($args);
            }
        }
    }
    
    public static function isActive($config) {
        /*
         * Verifico che la configurazione sia accessibile.
         */

        if (!self::scan()) {
            return false;
        }

        $root = ITA_BASE_PATH . '/hooks';
        $hooksIni = parse_ini_file($root . '/hooks.ini', true);

        if (!$hooksIni) {
            return false;
        }

        if (isset($hooksIni[$config]) && $hooksIni[$config]['active'] == 1) {
            return true;
        }

        return false;
    }
    
}