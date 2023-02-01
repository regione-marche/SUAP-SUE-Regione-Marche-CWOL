<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaPDFA
 *
 * @author michele
 */
class manager {

    private $managerPath;
    public $lastExitCode;
    public $lastMessage;
    public $lastOutput;
    private $workDir = '';

    function __construct() {
        
    }

    function getWorkDir() {
        if ($this->workDir == '' || ($this->workDir && is_dir($this->workDir) === false )) {
            if (function_exists('sys_get_temp_dir')) {
                if (sys_get_temp_dir()) {
                    $workDir = sys_get_temp_dir();
                }
            }
            if ($workDir) {
                $workDir = $workDir . "/" . md5(uniqid(rand())) . "_pdfa";
            } else {
                return false;
            }
            if (!@mkdir($workDir)) {
                return false;
            }
            $this->workDir = $workDir;
        }
        return $this->workDir;
    }

    function deleteWorkDir() {
        if ($this->workDir && is_dir($this->workDir)) {
            $this->deleteDirRecursive($this->workDir);
        }
        return true;
    }

    function getLastExitCode() {
        return $this->lastExitCode;
    }

    function getLastMessage() {
        return $this->lastMessage;
    }

    function getLastOutput() {
        return $this->lastOutput;
    }

    function setLastMessage() {
        $this->lastMessage = print_r($this->getLastOutput(), true);
    }

    private function deleteDirRecursive($dir) {
        if ($dir == ".") {
            return true;
        }
        if ($dir == "..") {
            return true;
        }
        if ($dir == "/") {
            return false;
        }
        if ($dir == "") {
            return false;
        }
        if (strpos($dir, "*") !== false) {
            return false;
        }
        if (strpos($dir, "#") !== false) {
            return false;
        }
        if (strpos($dir, "@") !== false) {
            return false;
        }

        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        if ($this->deleteDirRecursive($dir . "/" . $object) == false) {
                            return false;
                        }
                    } else {
                        if (!@unlink($dir . "/" . $object)) {
                            return false;
                        }
                    }
                }
            }
            reset($objects);
            if (!@rmdir($dir)) {
                return false;
            }
        }
        return true;
    }
    
    
}

?>
