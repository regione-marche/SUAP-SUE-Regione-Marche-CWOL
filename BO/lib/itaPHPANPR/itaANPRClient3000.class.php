<?php

require_once(ITA_LIB_PATH . '/itaPHPANPR/itaANPRClientSuper.class.php');

/**
 *
 * WS ANPR 3000 - Consultazione
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
class itaANPRClient3000 extends itaANPRClientSuper {

    protected function init() {
        $this->namespace = 'http://sogei.it/ANPR/3000consultazione';
        $this->namespaces = array(
            'ns2' => 'http://sogei.it/ANPR/3000consultazione',
            'wsu' => 'http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd'
        );
    }

    public function ws_3002_interrogazioneCittadino_famiglia_convivenza($args, $wsse) {
        $this->inizializzaChiamata($args);

        $bodyKeys = $this->generaMappaChiaviWS(3002); //Determino i tag dal file XSD

//        $bodyKeys = array(
//            'criteriRicerca' => 'criteriRicerca',
//            'datiNascita' => 'datiNascita',
//            'datiRichiesta' => 'datiRichiesta'
//        );

        $exceptionKeys = array(
            'datiRichiesta.datiAnagraficiRichiesti' => self::NOT_CREATED_PARENT_NODE_ELEMENT
        );

        $this->creaBody($bodyKeys, $exceptionKeys);

//        $this->setDebugLevel(1);
        
        return $this->eseguiChiamata($wsse, 'Richiesta3002');
    }
    
    public function ws_3003_gestione_richieste($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(3003); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta3003');
    }
    
    public function ws_3004_consultazione_procedimento_amministrativo($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(3004); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta3004');
    }
    
    public function ws_3007_ricerca_identificativi_anpr($args, $wsse) {
        $this->inizializzaChiamata($args);
        $bodyKeys = $this->generaMappaChiaviWS(3007); //Determino i tag dal file XSD
        $this->creaBody($bodyKeys, $exceptionKeys);
        return $this->eseguiChiamata($wsse, 'Richiesta3007');
    }

}
