<?php

class praCustomExplorerLocal {

    private $PRAM_DB;
    private $errMessage;
    private $errCode;

    const TYPE_FILE = 'F';
    const TYPE_DIRECTORY = 'D';
    const ERR_FILE_EXISTS = 10;

    public function getPRAM_DB() {
        if (!$this->PRAM_DB) {
            try {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage($e->getMessage());
            }
        }

        return $this->PRAM_DB;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    private function getCustomClassPath() {
        $sql = "SELECT * FROM ANAPAR WHERE PARKEY = 'CUSTOMCLASS_LOCAL_PATH'";
        $anapar_rec = ItaDB::DBSQLSelect($this->getPRAM_DB(), $sql, false);

        if (!$anapar_rec['PARVAL']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro 'CUSTOMCLASS_LOCAL_PATH' non configurato");
            return false;
        }

        return $anapar_rec['PARVAL'];
    }

    private function globUsortDirectoryFirst($a, $b) {
        $aIsDir = is_dir($a);
        $bIsDir = is_dir($b);

        if ($aIsDir === $bIsDir) {
            return strnatcasecmp($a, $b);
        } elseif ($aIsDir && !$bIsDir) {
            return -1;
        } elseif (!$aIsDir && $bIsDir) {
            return 1;
        }
    }

    private function humanFilesize($bytes, $decimals = 2) {
        $size = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$size[$factor];
    }

    public function getDirectory($path = '/') {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $result = array();
        $files = glob($basePath . $path . '/*');
        usort($files, array($this, 'globUsortDirectoryFirst'));

        foreach ($files as $file) {
            $is_dir = is_dir($file);

            $result[] = array(
                'filetype' => $is_dir ? self::TYPE_DIRECTORY : self::TYPE_FILE,
                'filename' => basename($file),
                'filecount' => $is_dir ? count(glob("$file/*")) : $this->humanFilesize(filesize($file))
            );
        }

        return $result;
    }

    public function getFile($path) {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $filePath = $basePath . $path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il file '$path' non è stato trovato.");
            return false;
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore durante la lettura del file.');
            return false;
        }

        return base64_encode($contents);
    }

    public function updateFile($path, $base64) {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $contents = base64_decode($base64);

        $filePath = $basePath . $path;

        if (!file_exists($filePath) || !is_file($filePath)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Il file '$path' non è stato trovato.");
            return false;
        }

        if (file_put_contents($filePath, $contents) === false) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore durante la scrittura del file.');
            return false;
        }

        return true;
    }

    public function deleteElement($path) {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $filePath = $basePath . $path;

        if (!file_exists($filePath)) {
            return true;
        }

        if (is_dir($filePath)) {
            if (!rmdir($filePath)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore durante l\'eliminazione della cartella.');
                return false;
            }
        } else {
            if (!unlink($filePath)) {
                $this->setErrCode(-1);
                $this->setErrMessage('Errore durante l\'eliminazione del file.');
                return false;
            }
        }

        return true;
    }

    public function insertFile($path, $base64 = false) {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $elementPath = $basePath . $path;

        if (file_exists($elementPath)) {
            $this->setErrCode(self::ERR_FILE_EXISTS);
            $this->setErrMessage('File già esistente.');
            return false;
        }

        if (!touch($elementPath)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore durante la creazione del file.');
            return false;
        }

        if ($base64) {
            if (!$this->updateFile($path, $base64)) {
                return false;
            }
        }

        return true;
    }

    public function insertDirectory($path) {
        $basePath = $this->getCustomClassPath();
        if (!$basePath) {
            return false;
        }

        $elementPath = $basePath . $path;

        if (!mkdir($elementPath)) {
            $this->setErrCode(-1);
            $this->setErrMessage('Errore durante la creazione della cartella.');
            return false;
        }

        return true;
    }

}
