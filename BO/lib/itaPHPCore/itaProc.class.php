<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of itaProc
 *
 * @author michele
 */
class itaProc {

    private $procToken;
    private $createLabel;
    private $completeLabel;
    private $progressLabel = '';
    private $progressVal = 0;
    private $progressMax = 100;
    private $responseTimeout = 30;
    private $refreshSrc = 'auto';
    private $refreshExternalPath;
    private $callBackFunc;
    private $model;
    private $refreshDelay = 5;

    public function __construct() {
        $newToken = md5(rand() * microtime());
        $this->setProcToken($newToken);
        $this->setRefreshExternalPath(itaLib::createAppsTempPath('modelprocess-' . $this->procToken) . "/externalProgress.log");
    }

    private function toArray() {
        $obj = get_object_vars($this);
        unset($obj['ITALWEB']);
        return $obj;
    }

    public function getITALWEB() {
        try {
            $ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            return false;
        }
        return $ITALWEB;
    }

    public function deleteProcessFromDB() {
        $Env_processi_rec = itaDB::DBSQLSelect($this->getITALWEB(), "SELECT ROWID FROM ENV_PROCESSI WHERE PROCTOKEN='{$this->getProcToken()}'", false);
        if ($Env_processi_rec) {
            try {
                itaDB::DBDelete($this->getITALWEB(), "ENV_PROCESSI", 'ROWID', $Env_processi_rec['ROWID']);
            } catch (Exception $exc) {
                return false;
            }
        } else {
            return false;
        }
        return true;
    }

    public function getEnvProcessi() {
        try {
            $Env_processi_rec = itaDB::DBSQLSelect($this->getITALWEB(), "SELECT * FROM ENV_PROCESSI WHERE PROCTOKEN='{$this->getProcToken()}'", false);
        } catch (Exception $e) {
            Out::msgStop("Gestione Processo. Errore grave.", $e->getMessage());
            $this->processEnd();
        }
        if ($Env_processi_rec) {
            return $Env_processi_rec;
        } else {
            return false;
        }
    }

    public function getProcessFromDB() {
        $Env_processi_rec = $this->getEnvProcessi();
        if ($Env_processi_rec) {
            $Proc_info = unserialize($Env_processi_rec['PROCINFO']);
            $this->progressLabel = $Proc_info['progressLabel'];
            $this->progressVal = $Proc_info['progressVal'];
            $this->progressMax = $Proc_info['progressMax'];
            $this->refreshSrc = $Proc_info['refreshSrc'];
            $this->refreshExternalPath = $Proc_info['refreshExternalPath'];
        }
    }

    public function setProcessToDB() {
        $Env_processi_rec = $this->getEnvProcessi();
        if (!$Env_processi_rec) {
            $Env_processi_rec = array();
            $Env_processi_rec['PROCTOKEN'] = $this->getProcToken();
            $Env_processi_rec['PROCDATE'] = "";
            $Env_processi_rec['PROCTIME'] = "";
            $Env_processi_rec['PROCUSER'] = "";
            $Env_processi_rec['PROCINFO'] = serialize($this->toArray());
            try {
                $nrow = itaDB::DBInsert($this->getITALWEB(), "ENV_PROCESSI", 'ROWID', $Env_processi_rec);
            } catch (Exception $exc) {
                return false;
            }
            $lastId = $this->getITALWEB()->getLastId();
            return true;
        } else {
            $Env_processi_rec['PROCINFO'] = serialize($this->toArray());
            try {
                $nrow = itaDB::DBUpdate($this->getITALWEB(), "ENV_PROCESSI", 'ROWID', $Env_processi_rec);
            } catch (Exception $exc) {

                return false;
            }
        }
        return true;
    }

    public function getRefreshSrc() {
        return $this->refreshSrc;
    }

    public function setRefreshSrc($refreshSrc) {
        $this->refreshSrc = $refreshSrc;
    }

    public function getRefreshExternalPath() {
        return $this->refreshExternalPath;
    }

    public function setRefreshExternalPath($refreshExternalPath) {
        $this->refreshExternalPath = $refreshExternalPath;
    }

    public function getProcToken() {
        return $this->procToken;
    }

    public function getProgressVal() {
        return $this->progressVal;
    }

    public function setProgressVal($progressVal) {
        $this->progressVal = $progressVal;
    }

    public function getProgressMax() {
        return $this->progressMax;
    }

    public function setProgressMax($progressMax) {
        $this->progressMax = $progressMax;
    }

    public function setProcToken($procToken) {
        $this->procToken = $procToken;
    }

    public function getResponseTimeout() {
        return $this->responseTimeout;
    }

    public function setResponseTimeout($responseTimeout) {
        $this->responseTimeout = $responseTimeout;
    }

    public function getCallBackFunc() {
        return $this->callBackFunc;
    }

    public function setCallBackFunc($callBackFunc) {
        $this->callBackFunc = $callBackFunc;
    }

    public function getStartCallBackFunc() {
        if (is_array($this->callBackFunc)) {
            if ($this->callBackFunc['start']) {
                return $this->callBackFunc['start'];
            }
        } else {
            return $this->callBackFunc;
        }
    }

    public function getRefreshCallBackFunc() {
        if (is_array($this->callBackFunc)) {
            if ($this->callBackFunc['refresh']) {
                return $this->callBackFunc['refresh'];
            }
        } else {
            return '';
        }
    }

    public function getModel() {
        return $this->model;
    }

    public function setModel($model) {
        $this->model = $model;
    }

    public function getRefreshDelay() {
        return $this->refreshDelay;
    }

    public function setRefreshDelay($refreshDelay) {
        $this->refreshDelay = $refreshDelay;
    }

    public function getCreateLabel() {
        return $this->createLabel;
    }

    public function setCreateLabel($createLabel) {
        $this->createLabel = $createLabel;
    }

    public function getCompleteLabel() {
        return $this->completeLabel;
    }

    public function setCompleteLabel($completeLabel) {
        $this->completeLabel = $completeLabel;
    }

    public function getProgressLabel() {
        return $this->progressLabel;
    }

    public function setProgressLabel($progressLabel) {
        $this->progressLabel = $progressLabel;
    }

    public function processStart($titolo, $height = 'auto', $width = 'auto', $closeButton = true, $header = '', $trailer = '') {
        Out::msgProgress($this, $titolo, '', $height, $width, $closeButton, $header, $trailer);
    }

    public function processStartApply() {
        $cbFunc = $this->getStartCallBackFunc();
        if (!$cbFunc) {
            return false;
        }
        App::session_write_close();
        return $cbFunc;
    }

    public function processEnd($nodedeletedb = false) {
        Out::closeDialog('msgProgress-' . $this->getProcToken());
        if ($nodedeletedb === false)
            $this->deleteProcessFromDB();
        itaLib::deleteAppsTempPath('modelprocess-' . $this->getProcToken());
        App::session_write_reopen();
    }

    public function refresh() {
        $this->getProcessFromDB();
        switch ($this->refreshSrc) {
            case 'external':
                $tailData = $this->tailRefreshExternalPath(1);
                if (!$tailData) {
                    break;
                }
                list($progressVal, $progressLabel) = explode("|", end($tailData));
                if (is_numeric($progressVal)) {
                    $this->setProgressVal($progressVal);
                    $progressLabel = str_replace("\n", '', $progressLabel);
                    if ($progressLabel) {
                        $this->setProgressLabel($progressLabel);
                    }
                }
                break;
        }
        $this->outProgressMax();
        $this->outProgressVal();
    }

    public function outProgressMax() {
        Out::codice("$('#msgProgress-progress-" . $this->getProcToken() . "').progressbar('option','max',{$this->getProgressMax()});");
    }

    public function outProgressVal() {
        Out::codice("$('#msgProgress-progress-" . $this->getProcToken() . "').progressbar('value',{$this->getProgressVal()});");
        if ($this->getProgressLabel()) {
            Out::codice("$('#msgProgress-progress-label-" . $this->getProcToken() . "').find('span').eq(0).html('{$this->getProgressLabel()}');");
        }
    }

    private function tailRefreshExternalPath($lines = 1) {
        $handle = fopen($this->refreshExternalPath, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();
        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning)
                break;
        }
        fclose($handle);
        return array_reverse($text);
    }

}

?>
