<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 2000 - Cancellazione
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
class itaANPRClient2000 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/2000cancellazione';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/2000cancellazione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_2001_decesso($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(2001); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'elencoSoggettiFamiglia' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta2001');
    }

    public function ws_2003_cancellazione_AltriMotivi($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(2003); //Determino i tag dal file XSD

        $exceptionKeys = array(
            'elencoSoggetti' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta2003');
    }

    public function ws_2009_Archiviazione_Scheda_Convivenza($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(2009); //Determino i tag dal file XSD

        $forceKeys = array(
            'datiControllo'  //Nel caso di sottolivello, indicare il percorso completo, es. residenzaConvivenza.
        );

        $exceptionKeys = array();

        $this->creaBody($bodyKeys, $exceptionKeys,$forceKeys);

        return $this->eseguiChiamata($wsse, 'Richiesta2009');
    }

    public function ws_2011_annullamento_Cancellazione($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(2011); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta2011');
    }

}
