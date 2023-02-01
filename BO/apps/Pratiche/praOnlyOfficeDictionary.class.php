<?php

include_once ITA_LIB_PATH . '/itaOnlyOffice/itaOnlyOfficeDictionary.php';
itaModelHelper::requireAppFileByName('praLibVariabili.class');

/**
 * Dizionario Pratiche
 * @author andrea.bufarini
 */
class praOnlyOfficeDictionary implements itaOnlyOfficeDictionary {
    
    private $resolver;
    
    public function __construct() {
        $this->resolver = new praLibVariabili();
    }
    
    public function toArray() {
        return json_decode($this->resolver->getLegendaGenerico("json"), true);
    }

}


