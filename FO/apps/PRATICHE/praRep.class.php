<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praRep
 *
 * @author michele
 */
class praRep {
//parametri di configurazione
    const DEFAULT_MANAGER = 'file';
    const DEFAULT_USER = 'nobody';
    const DEFAULT_PASSWORD = 'none';
    const DEFAULT_HOST = 'localhost';
    const DEFAULT_ROOTPATH = '/tmp';
    const DEFAULT_CONFIG = ITA_PROC_REPOSITORY;

    public $connector;
    public $config;

    function __construct($config=false) {
        $this->config = $config;
        if (!$this->config) {
            $this->config = $this->parseDefaultConfig();
        } else {
            if (is_array($this->config)) {
                $this->config['manager'] = (isset($this->config['manager'])) ? $this->config['manager'] : self::DEFAULT_MANAGER;
                $this->config['user'] = (isset($this->config['user'])) ? $this->config['user'] : self::DEFAULT_USER;
                $this->config['host'] = (isset($this->config['host'])) ? $this->config['host'] : self::DEFAULT_HOST;
                $this->config['pwd'] = (isset($this->config['pwd'])) ? $this->config['pwd'] : self::DEFAULT_PASSWORD;
                $this->config['rootPath'] = (isset($this->config['rootPath'])) ? $this->config['rootPath'] : self::DEFAULT_ROOTPATH;
            } else {
                $this->config = $this->parseStringConfig($config);
            }
        }
        $manager_class = __CLASS__ . $this->config['manager'];
        $manager_file = dirname(__FILE__) . "/" . $manager_class . ".class.php";

        if (!file_exists($manager_file)) {
            return false;
        }

        include_once($manager_file);
        $this->connector = new $manager_class($this->config);
    }

    public function parseDefaultConfig() {
        $stringConfig = self::DEFAULT_CONFIG;
        return $this->parseStringConfig($stringConfig);
//        if (substr(self::DEFAULT_CONFIG, 0, strlen('file://')) === 'file://') {
//            $config = array(
//                'manager' => 'File',
//                'rootPath' => substr(self::DEFAULT_CONFIG, 7)
//            );
//            if ($config['rootPath'] == '' || $config['rootPath'] == '/') {
//                return false;
//            }
//        } elseif (substr(self::DEFAULT_CONFIG, 0, strlen('ftp://') === 'ftp://')) {
//            
//        } else {
//            return false;
//        }
//        return $config;
    }

    public function parseStringConfig($stringConfig) {
        if (substr($stringConfig, 0, strlen('file://')) === 'file://') {
            $config = array(
                'manager' => 'File',
                'rootPath' => substr($stringConfig, 7)
            );
            if ($config['rootPath'] == '' || $config['rootPath'] == '/') {
                return false;
            }
        } elseif (substr($stringConfig, 0, strlen('ftp://') === 'ftp://')) {
            
        } else {
            return false;
        }
        return $config;
    }
    
    public function checkFile($sourceFile='') {
         return $this->connector->checkFile($sourceFile);
    }

    public function getFile($sourceFile='', $destinationFile='', $overWrite=false, $returnString = false) {

         return $this->connector->getFile($sourceFile, $destinationFile, $overWrite, $returnString);
    }

    public function getErrorMessage() {
        return $this->connector->getErrorMessage();
    }

}

?>
