<?php

/**
 *
 * REST Controller
 *
 *  * PHP Version 5
 *
 * @category   CORE
 * @package    itaPHPCore
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    17.02.2016
 * @link
 * @see
 * @since
 * */
abstract class RestController {

    const ERROR_PRECONDITION = -1;

    private $lastErrorCode;
    private $lastErrorDescription;
    private $lastAction;

    public function handleError($code, $description) {
        $this->setLastErrorCode($code);
        $this->setLastErrorDescription($description);
        error_log($this->getLastErrorCode() . " - " . $this->getLastErrorDescription());
    }

    public function resetLastError() {
        $this->setLastErrorCode(0);
        $this->setLastErrorDescription("");
    }

    public function getLastErrorCode() {
        return $this->lastErrorCode;
    }

    public function getLastErrorDescription() {
        return $this->lastErrorDescription;
    }

    public function setLastErrorCode($lastErrorCode) {
        $this->lastErrorCode = $lastErrorCode;
    }

    public function setLastErrorDescription($lastErrorDescription) {
        $this->lastErrorDescription = $lastErrorDescription;
    }

    public function getLastAction() {
        return $this->lastAction;
    }

    public function setLastAction($lastAction) {
        $this->lastAction = $lastAction;
    }

}

?>