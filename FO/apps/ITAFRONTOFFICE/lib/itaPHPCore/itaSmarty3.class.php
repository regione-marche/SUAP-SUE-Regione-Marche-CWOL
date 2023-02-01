<?php

require_once(ITA_BASE_PATH . '/lib/Smarty3/libs/Smarty.class.php');

class itaSmarty extends Smarty {

    function __construct() {

        if (defined('ITA_FRONTOFFICE_TEMP') && ITA_FRONTOFFICE_TEMP != '') {
            $template_dir = ITA_FRONTOFFICE_TEMP . "/Smarty/templates";
            $compile_dir = ITA_FRONTOFFICE_TEMP . "/Smarty/smarty_template_c/";
            $config_dir = ITA_FRONTOFFICE_TEMP . "/Smarty/templates";
            $cache_dir = ITA_FRONTOFFICE_TEMP . "/Smarty/smarty_cache/";
        } else {
            $template_dir = ITA_BASE_PATH . "/lib/Smarty/templates";
            $compile_dir = ITA_BASE_PATH . "/lib/Smarty/smarty_template_c/";
            $config_dir = ITA_BASE_PATH . "/lib/Smarty/templates";
            $cache_dir = ITA_BASE_PATH . "/lib/Smarty/smarty_cache/";
        }

        $caching = 0;
        $left_delimiter = "@{";
        $right_delimiter = "}@";

        // Costruttore della Classe. Questi dati vengono automaticamente impostati
        // per ogni nuova istanza.
        parent::__construct();
        $this->setTemplateDir($template_dir);
        if (!@is_dir($this->getTemplate_dir())) {
            if (!@mkdir($this->getTemplate_dir(), 0777, true)) {
                return false;
            }
        }
        $this->setCompileDir($compile_dir);
        if (!@is_dir($this->compile_dir)) {
            if (!@mkdir($this->compile_dir, 0777, true)) {
                return false;
            }
        }
        $this->setConfigDir($config_dir);
        $this->setCacheDir($cache_dir);
        if (!@is_dir($this->cache_dir)) {
            if (!@mkdir($this->cache_dir, 0777, true)) {
                return false;
            }
        }
        $this->setCaching((integer) $caching);
        $this->setForceCompile(true);
        $this->setLeft_delimiter($left_delimiter);
        $this->setRight_delimiter($right_delimiter);
    }

    public function getTemplate_dir() {
        return $this->template_dir[0];
    }

    public function setLeft_delimiter($left_delimiter) {
        $this->setLeftDelimiter($left_delimiter);
    }

    public function setRight_delimiter($right_delimiter) {
        $this->setRightDelimiter($right_delimiter);
    }

}

?>