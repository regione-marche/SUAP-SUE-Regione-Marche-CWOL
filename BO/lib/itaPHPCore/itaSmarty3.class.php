<?php

require_once(ITA_BASE_PATH . '/lib/Smarty3/libs/Smarty.class.php');
require_once ITA_LIB_PATH . '/itaPHPCore/itaSmartyUtils.class.php';

class itaSmarty extends Smarty {

    function __construct() {
        // Costruttore della Classe. Questi dati vengono automaticamente impostati
        // per ogni nuova istanza.

        parent::__construct();
        $this->setTemplateDir(App::getConf('itaSmarty.template_dir'));
        $this->setCompileDir(App::getConf('itaSmarty.compile_dir'));

        if (!@is_dir($this->compile_dir)) {
            @mkdir($this->compile_dir, 0777);
        }
        $this->setConfigDir(App::getConf('itaSmarty.config_dir'));
        $this->setCacheDir(App::getConf('itaSmarty.cache_dir'));

        if (!@is_dir($this->cache_dir)) {
            @mkdir($this->cache_dir, 0777);
        }

        $this->setCaching((integer) App::getConf('itaSmarty.caching'));
        $this->setForceCompile(true);
        $this->setLeft_delimiter(App::getConf('itaSmarty.left_delimiter'));
        $this->setRight_delimiter(App::getConf('itaSmarty.right_delimiter'));
        $this->assign('app_name', 'itaEngine');

        $itaSmartyUtils = new itaSmartyUtils;
        $this->registerObject('itaUtils', $itaSmartyUtils);
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

    public function fetch($resource_name, $cache_id = null, $compile_id = null, $display = false) {
        $ret = parent::fetch($resource_name, $cache_id, $compile_id, null);
        $this->clearCompiledTemplate($resource_name);
        return $ret;
    }

}

?>
