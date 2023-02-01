<?php

require_once(ITA_BASE_PATH . '/lib/Smarty/libs/Smarty.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaSmartyUtils.class.php';

class itaSmarty extends Smarty {

    function __construct() {
        // Costruttore della Classe. Questi dati vengono automaticamente impostati
        // per ogni nuova istanza.
        // 
        $this->Smarty();
        $this->template_dir = App::getConf('itaSmarty.template_dir');
        $this->compile_dir = App::getConf('itaSmarty.compile_dir');
        //App::log($this->compile_dir);
        if (!@is_dir($this->compile_dir)) {
            @mkdir($this->compile_dir, 0777);
        }
        $this->config_dir = App::getConf('itaSmarty.config_dir');
        $this->cache_dir = App::getConf('itaSmarty.cache_dir');
        //App::log($this->cache_dir);
        if (!@is_dir($this->cache_dir)) {
            @mkdir($this->cache_dir, 0777);
        }
        $this->caching = (integer) App::getConf('itaSmarty.caching');
        $this->force_compile = true;
        $this->left_delimiter = App::getConf('itaSmarty.left_delimiter');
        $this->right_delimiter = App::getConf('itaSmarty.right_delimiter');
        $this->assign('app_name', 'itaEngine');

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

    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
        $ret = parent::fetch($resource_name, $cache_id, $compile_id, $display);
        $this->clear_compiled_tpl($resource_name);
        return $ret;
    }

}

?>
