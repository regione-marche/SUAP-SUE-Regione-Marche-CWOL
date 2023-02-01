<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR A000 - Servizi AIRE
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
class itaANPRClientA000 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/A000aire';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/A000aire',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_A001_AIRE_iscrizione_nascita($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS('A001'); //Determino i tag dal file XSD
        $exceptionKeys = array(
            'altraCittadinanza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'datiControllo.listaControlli.controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'RichiestaA001');
    }

    public function ws_A002_AIRE_iscrizioneAltriMotivi($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS('A002'); //Determino i tag dal file XSD
        $exceptionKeys = array(
            'altraCittadinanza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'datiControllo.listaControlli.controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'RichiestaA002');
    }

    public function ws_A006_AIRE_mutazione($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS('A006'); //Determino i tag dal file XSD
        $exceptionKeys = array(
            'controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'RichiestaA006');
    }

}
