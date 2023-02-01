<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 7001 - Scarico tabelle
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     Massimo Biagioli
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    28.05.2018
 * @link
 * @see
 * @since
 * */
class itaANPRClient7001 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/7001';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/7001scaricoTabelle',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_7001_scarico_tabelle($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(7001); //Determino i tag dal file XSD
        $exceptionKeys = array();
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta7001');
    }

    public function ws_7002_download_file($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(7002); //Determino i tag dal file XSD
        $exceptionKeys = array();
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta7002');
    }

}
