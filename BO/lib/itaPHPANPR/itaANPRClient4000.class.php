<?php
require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 4000 - Estrazione
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
class itaANPRClient4000 extends itaANPRClientSuper {
    
    protected function init() {
         $this->namespace = 'http://sogei.it/ANPR/4000estrazione'; //https://ws.anpr.interno.it/ANPR4000ServiziEstrazione/AnprService4000
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/4000estrazione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }
    
    public function ws_4004_elenchi_supporto($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(4004); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta4004');
    }
    
    public function ws_4005_richiesta_asincrona($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(4005); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta4005');
    }

}
