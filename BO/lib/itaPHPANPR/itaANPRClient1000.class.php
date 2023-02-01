<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 1000 - Iscrizione
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
class itaANPRClient1000 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/1000iscrizione';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/1000iscrizione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_1001_iscrizione_nascita($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(1001); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'residenza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'datiControllo.listaControlli.controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta1001');
    }

    public function ws_1002_iscrizione_altriMotivi($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(1002); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'cittadinanza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'residenza' => self::NOT_CREATED_PARENT_NODE_ELEMENT
            , 'datiControllo.listaControlli.controllo' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta1002');
    }

    public function ws_1010_iscrizione_schedaConvivenza($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(1010); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'altraLingua' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );
        
        $forceKeys = array(
            'datiControllo'  //Nel caso di sottolivello, indicare il percorso completo, es. residenzaConvivenza.
        );

        $this->creaBody($bodyKeys, $exceptionKeys,$forceKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta1010');
    }

    public function ws_1013_annullamento_iscrizione($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(1013); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta1013');
    }

    public function ws_1014_procedimento_amministrativo($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(1014); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'generalitaPerRicerca' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta1014');
    }

}
