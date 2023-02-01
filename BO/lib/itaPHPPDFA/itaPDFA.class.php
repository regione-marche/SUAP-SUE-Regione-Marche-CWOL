<?php
/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /lib/itaPHPPDFA
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license 
 * @version    21.05.2012
 * @link
 * @see
 * @since
 * @deprecated
 **/
class itaPDFA {

    private $manager;
    private $managerObj;
    private $workDir = '';

    function __construct($manager = 'pdftron') {
        //inizializzazione
        $this->manager = $manager;
        $driver_manager = dirname(__FILE__) . "/manager.$this->manager.class.php";
        if (!file_exists($driver_manager)) {
            throw new Exception("Driver pdfa manager $driver_manager non trovato");
        }
        include_once($driver_manager);
        $classe = 'manager_' . $this->manager;
        $this->managerObj = new $classe();
    }

    public static function getManagerInstance($manager = 'pdftron') {
        try {
            return new itaPDFA($manager);
        } catch (Exception $e) {
            return null;
        }
    }

    function getManagerType() {
        return $this->managerObj->getManagerType();
    }

    function getManagerVersion() {
        return $this->managerObj->getManagerVersion();
    }

    function getLastExitCode() {
        return $this->managerObj->getlastExitCode();
    }

    function getLastMessage() {
        return $this->managerObj->getlastMessage();
    }

    function getLastOutput() {
        return $this->managerObj->getlastOutput();
    }

    public function verifyPDFSimple($fileName, $verbose = 0 , $level = "A") {
        return $this->managerObj->verifyPDFSimple($fileName, $verbose, $level);
    }

    public function convertPDF($fileName, $outputFile, $verbose = 0 ,$level = "A") {
        return $this->managerObj->convertPDF($fileName, $outputFile, $verbose, $level);
    }

}

?>
