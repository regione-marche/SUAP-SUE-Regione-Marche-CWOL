<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 6001 - Certificazione
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
class itaANPRClient6001 extends itaANPRClientSuper {

    protected function init() {

        $this->namespace = 'http://sogei.it/ANPR/6001certificazione';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/6001certificazione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_6001_emissioneCertificato($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(6001); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'listaTipiCertificati.tipoCertificato' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            ,'datiControllo.listaAnnotazioni.rigaAnnotazione' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys,$exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta6001');
    }

}
