<?php

class itaModelFO {

    /**
     * Configurazioni del model.
     * @var type array
     */
    protected $config;
    protected $request;
    protected $frontOfficeLib;

    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function __construct() {
        $this->request = frontOfficeApp::$cmsHost->getRequest();
        $this->frontOfficeLib = new frontOfficeLib;
    }

    public function parseEvent() {

    }

}
