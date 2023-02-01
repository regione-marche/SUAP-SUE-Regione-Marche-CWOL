<?php

/**
 *
 * Interfaccia per le stampe in coda da usare con itaQueueWorkerPrinter
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
interface printableQueue {

    public function executePrint($args);

    public function getResult();

    public function getErrMessage();
}

?>
