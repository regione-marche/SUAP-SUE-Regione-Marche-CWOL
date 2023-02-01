<?php

class itaSysExec {

    public $status;
    public $isTimedOut;
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
                $cmd, array(array('pipe', 'r'), array('pipe', 'w'), array('pipe', 'w')), $pipes
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
