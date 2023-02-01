<?php

abstract class praSchedaTemplate {

    public $praLib;
    public $config;
    public $PRAM_DB;

    public function __construct() {
        $this->praLib = new praLib;
        $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
    }

    public function getPraLib() {
        return $this->praLib;
    }

    public function setPraLib($praLib) {
        $this->praLib = $praLib;
    }

    public function getConfig() {
        return $this->config;
    }

    public function setConfig($config) {
        $this->config = $config;
    }

    public function getPRAM_DB() {
        return $this->PRAM_DB;
    }

    public function setPRAM_DB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

}
