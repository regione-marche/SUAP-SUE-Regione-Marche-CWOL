<?php

require_once(ITA_BASE_PATH . '/lib/Smarty/libs/Smarty.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaSmartyUtils.class.php';

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
        $this->Smarty();
        $this->template_dir = $template_dir;
        if (!@is_dir($this->template_dir)) {
            if (!@mkdir($this->template_dir, 0777, true)) {
                return false;
            }
        }
        $this->compile_dir = $compile_dir;
        if (!@is_dir($this->compile_dir)) {
            if (!@mkdir($this->compile_dir, 0777, true)) {
                return false;
            }
        }
        $this->config_dir = $config_dir;
        $this->cache_dir = $cache_dir;
        if (!@is_dir($this->cache_dir)) {
            if (!@mkdir($this->cache_dir, 0777, true)) {
                return false;
            }
        }
        $this->caching = (integer) $caching;
        $this->force_compile = true;
        $this->left_delimiter = $left_delimiter;
        $this->right_delimiter = $right_delimiter;
		
		$itaSmartyUtils = new itaSmartyUtils;
        $this->register_object('itaUtils', $itaSmartyUtils);
    }

    public function getTemplate_dir() {
        return $this->template_dir;
    }

    public function setLeft_delimiter($left_delimiter) {
        $this->left_delimiter = $left_delimiter;
    }

    public function setRight_delimiter($right_delimiter) {
        $this->right_delimiter = $right_delimiter;
    }

}

?>
