<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 5000 - Mutazione
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
class itaANPRClient5000 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/5000mutazione';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/5000mutazione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_5001_mutazione_famiglia_residenza($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(5001); //Determino i tag dal file XSD
        $exceptionKeys = array();
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta5001');
    }

    public function ws_5005_mutazione_residenza($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(5005); //Determino i tag dal file XSD
        $exceptionKeys = array();
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta5005');
    }

    public function ws_5008_mutazione_scheda($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(5008); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'datiControllo.tipoMutazione' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'cittadinanza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

//        $this->setDebugLevel(1);
        
        return $this->eseguiChiamata($wsse, 'Richiesta5008');
    }

    public function ws_5012_annullamento_mutazione($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(5012); //Determino i tag dal file XSD

//        $exceptionKeys = array(
//            'datiControllo.listaControlli.controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
//        );
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta5012');
    }

    public function ws_5014_rettifica_dati($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(5014); //Determino i tag dal file XSD
        $exceptionKeys = array();
        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta5014');
    }

}
