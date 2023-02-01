<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibCalcoli.class.php';

/**
 *
 * Classe wrapper di un array posizionale con l'aggiunta della voce 'current' per gestire il nodo corrente in cui si 
 * è posizionati.
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    27.05.2016
 * @link
 * @see
 * 
 */
class linkedListDouble {

    private $currentKey; // la chiave corrente selezionata sulla $linkedList
    private $linkedList; // SplDoublyLinkedList

    public function __construct() {
        $this->currentKey = -1;
        $this->linkedList = array();
    }

    public function current() {
        return $this->linkedList[$this->currentKey];
    }

    public function isEmpty() {
        return empty($this->linkedList);
    }

    public function rewind() {
        $this->currentKey = 0;
    }

    public function push($value) {
        $this->linkedList[] = $value;
    }

    public function prev() {
        $this->currentKey--;
    }

    public function next() {
        $this->currentKey++;
    }

    public function key() {
        return $this->currentKey;
    }

    public function count() {
        return count($this->linkedList);
    }

    public function add($index, $newval) {
        $this->linkedList = cwbLibCalcoli::addInArray($this->linkedList, $index, $newval);
    }

    public function offsetUnset($indexToRemove) {
        unset($this->linkedList[$indexToRemove]);
        $this->linkedList = array_values($this->linkedList); // ricalcola le key per farle sequenziali e tappare il buco
    }

    public function currentKey() {
        return $this->currentKey;
    }

}

?>