<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of itaRepclass
 *
 * @author utente
 */
class itaPrnt {

    public $process;
    public $pipes;

    function __construct($prnt) {
        $prntSpec=parse_ini_file('./config/printers.ini',true);
        if ($prntSpec[$prnt]) {
            $descriptorspec = array(
                    0 => array($prntSpec[$prnt]['stdin'], "r"),  // stdin is a pipe that the child will read from
                    1 => array($prntSpec[$prnt]['stdout'], "w"),  // stdout is a pipe that the child will write to
                    2 => array("file", "/dev/null","a") // stderr is a file to write to
            );
            $env = array();
            $this->process = proc_open($prntSpec[$prnt]['cmd'], $descriptorspec, $this->pipes, $cwd, $env);
            if (is_resource($this->process)) {
                return true;
            }
        }
    }
    function __destruct() {
        $this->prntClose();
    }

    function prntOut($value,$crlf=true){
        $prntCRLF= ($crlf == true ) ? chr(13).chr(10) : '';
        fwrite($this->pipes[0],$value.$prntCRLF);
    }

    function prntClose(){
                fclose($this->pipes[0]);
                fclose($this->pipes[1]);
                return proc_close($this->process);
    }
}
?>
