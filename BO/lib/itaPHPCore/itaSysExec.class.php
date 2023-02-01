<?php

class itaSysExec {

    public $status;
    public $isTimedOut;
    private $stdinMode = 'pipe';
    private $stdoutMode = 'pipe';
    private $stderrMode = 'pipe';
    private $stdinTarget = 'r';
    private $stdoutTarget = 'w';
    private $stderrTarget = 'w';
    private $stdinAppend;
    private $stdoutAppend;
    private $stderrAppend;

    function getStdinMode() {
        return $this->stdinMode;
    }

    function getStdoutMode() {
        return $this->stdoutMode;
    }

    function getStderrMode() {
        return $this->stderrMode;
    }

    function getStdinTarget() {
        return $this->stdinTarget;
    }

    function getStdoutTarget() {
        return $this->stdoutTarget;
    }

    function getStderrTarget() {
        return $this->stderrTarget;
    }

    function getStdinAppend() {
        return $this->stdinAppend;
    }

    function getStdoutAppend() {
        return $this->stdoutAppend;
    }

    function getStderrAppend() {
        return $this->stderrAppend;
    }

    function setStdinMode($stdinMode) {
        $this->stdinMode = $stdinMode;
    }

    function setStdoutMode($stdoutMode) {
        $this->stdoutMode = $stdoutMode;
    }

    function setStderrMode($stderrMode) {
        $this->stderrMode = $stderrMode;
    }

    function setStdinTarget($stdinTarget) {
        $this->stdinTarget = $stdinTarget;
    }

    function setStdoutTarget($stdoutTarget) {
        $this->stdoutTarget = $stdoutTarget;
    }

    function setStderrTarget($stderrTarget) {
        $this->stderrTarget = $stderrTarget;
    }

    function setStdinAppend($stdinAppend) {
        $this->stdinAppend = $stdinAppend;
    }

    function setStdoutAppend($stdoutAppend) {
        $this->stdoutAppend = $stdoutAppend;
    }

    function setStderrAppend($stderrAppend) {
        $this->stderrAppend = $stderrAppend;
    }

    /**
     * 
     * @param type $cmd
     * @param type $stdin
     * @param string $stdout
     * @param string $stderr
     * @param type $timeout
     * @return boolean
     */
    public function execute($cmd, $stdin = null, &$stdout, &$stderr, $timeout = false) {
        $this->isTimedOut = false;
        $pipes = array();
        
        
        
        
        $process = proc_open(
                $cmd,
                array(
                    array($this->stdinMode, $this->stdinTarget),
                    array($this->stdoutMode, $this->stdoutTarget,  $this->stdoutAppend),
                    array($this->stderrMode,$this->stderrTarget)), $pipes
        );
        
        
        $start = time();
        $stdout = '';
        $stderr = '';

        if (is_resource($process)) {
            stream_set_blocking($pipes[0], 0);
            stream_set_blocking($pipes[1], 0);
            stream_set_blocking($pipes[2], 0);
            fwrite($pipes[0], $stdin);
            fclose($pipes[0]);
        }

        while (is_resource($process)) {
            $stdout .= stream_get_contents($pipes[1]);
            $stderr .= stream_get_contents($pipes[2]);

            if ($timeout !== false && time() - $start > $timeout) {
                proc_terminate($process, 9);
                $status = proc_get_status($process);
                $this->isTimedOut = true;
                $this->status = $status;
                return false;
            }

            $status = proc_get_status($process);
            if (!$status['running']) {
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                $this->status = $status;
                return true;
            }

            usleep(100000);
        }

        return false;
    }

}

?>
