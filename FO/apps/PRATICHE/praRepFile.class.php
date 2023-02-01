<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praRepFile
 *
 * @author michele
 */
class praRepFile {

    private $sourceFile;
    private $errorMessage;
    private $destinationFile;
    private $repositoryRoot;

    function __construct($config) {
        if ($config) {
            $this->setRepositoryRoot($config['rootPath']);
        }
    }

    public function checkFile($sourceFile = '') {
        $this->setErrorMessage('');
        if ($sourceFile == "") {
            $this->setErrorMessage("File sorgente non specificato: " . $sourceFile);
            return false;
        }
//        $this->repositoryRoot = str_replace("\\", "/", $this->repositoryRoot);
//        $sourceFile = str_replace("\\", "/", $sourceFile);
        $this->repositoryRoot = (substr($this->repositoryRoot, strlen($this->repositoryRoot) - 1, 1) == "/") ? substr($this->repositoryRoot, 0, strlen($this->repositoryRoot) - 1) : $this->repositoryRoot;
        $sourceFile = (substr($sourceFile, 0, 1) == "/") ? substr($sourceFile, 1) : $sourceFile;
        if (file_exists($this->repositoryRoot . "/" . $sourceFile)) {
            return true;
        } else {
            return false;
        }
    }

    public function getFile($sourceFile = '', $destinationFile = '', $overWrite = false, $returnString = false) {
        $this->setErrorMessage('');
        if ($sourceFile == "") {
            $this->setErrorMessage("File sorgente non specificatoì: " . $sourceFile);
            return false;
        }

        if ($returnString === false) {
            if ($destinationFile == "") {
                $destinationFile = $this->getDestinationFile();
            }
            if ($destinationFile == "") {
                $this->setErrorMessage("File destino non specificato: " . $destinationFile);
                return false;
            }
        }

//        $this->repositoryRoot = str_replace("\\", "/", $this->repositoryRoot);
//        $sourceFile = str_replace("\\", "/", $sourceFile);
        $this->repositoryRoot = (substr($this->repositoryRoot, strlen($this->repositoryRoot) - 1, 1) == "/") ? substr($this->repositoryRoot, 0, strlen($this->repositoryRoot) - 1) : $this->repositoryRoot;
        $sourceFile = (substr($sourceFile, 0, 1) == "/") ? substr($sourceFile, 1) : $sourceFile;

        if ($returnString === false) {
            if (@file_exists($destinationFile)) {
                if ($overWrite == false) {
                    $this->setErrorMessage("File destino esistente e non riscrivibile: " . $sourceFile);
                    return false;
                }
            }

            if (!@copy($this->repositoryRoot . "/" . $sourceFile, $destinationFile)) {
                $this->setErrorMessage("Copia del file non riuscita: " . $this->repositoryRoot . "/" . $sourceFile);
                return false;
            }

            return true;
        } else {
            if (file_exists($sourceFile) && is_readable($sourceFile)) {
                return file_get_contents($sourceFile);
            }else{
                $this->setErrorMessage("File sorgente non trovato o non accessibile: " . $sourceFile);
                return false;
            }
        }
    }

    public function getRepositoryRoot() {
        return $this->repositoryRoot;
    }

    public function setRepositoryRoot($repositoryRoot) {
        $this->repositoryRoot = $repositoryRoot;
    }

    public function getSourceFile() {
        return $this->sourceFile;
    }

    public function setSourceFile($sourceFile) {
        $this->sourceFile = $sourceFile;
    }

    public function getErrorMessage() {
        return $this->errorMessage;
    }

    public function setErrorMessage($errorMessage) {
        $this->errorMessage = $errorMessage;
    }

    public function getDestinationFile() {
        return $this->destinationFile;
    }

    public function setDestinationFile($destinationFile) {
        $this->destinationFile = $destinationFile;
    }

}

?>
